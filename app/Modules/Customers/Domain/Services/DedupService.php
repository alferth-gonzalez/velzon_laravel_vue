<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Services;

use App\Modules\Customers\Domain\Entities\Customer;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DedupService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    /**
     * Encuentra duplicados potenciales basado en diferentes criterios
     */
    public function findPotentialDuplicates(?string $tenantId, Customer $customer): Collection
    {
        Log::withContext([
            'module' => 'customers',
            'action' => 'find_duplicates',
            'customer_id' => $customer->getId(),
            'tenant_id' => $tenantId
        ]);

        $duplicates = new Collection();

        // Buscar por documento (exacto)
        $documentDuplicates = $this->findByDocument($tenantId, $customer->getDocumentId());
        $duplicates = $duplicates->merge($documentDuplicates);

        // Buscar por email normalizado
        if ($customer->getEmail()) {
            $emailDuplicates = $this->findByEmail($tenantId, $customer->getEmail());
            $duplicates = $duplicates->merge($emailDuplicates);
        }

        // Buscar por teléfono normalizado
        if ($customer->getPhone()) {
            $phoneDuplicates = $this->findByPhone($tenantId, $customer->getPhone());
            $duplicates = $duplicates->merge($phoneDuplicates);
        }

        // Buscar por nombres similares (fuzzy matching)
        $nameDuplicates = $this->findBySimilarNames($tenantId, $customer);
        $duplicates = $duplicates->merge($nameDuplicates);

        // Remover el cliente actual de los resultados
        $duplicates = $duplicates->filter(function ($duplicate) use ($customer) {
            return $duplicate->getId() !== $customer->getId();
        });

        // Remover duplicados de la colección
        $duplicates = $duplicates->unique('id');

        Log::info('Duplicados encontrados', [
            'duplicates_count' => $duplicates->count(),
            'duplicate_ids' => $duplicates->pluck('id')->toArray()
        ]);

        return $duplicates;
    }

    public function findByDocument(?string $tenantId, DocumentId $documentId): Collection
    {
        return $this->customerRepository->findByDocumentId($tenantId, $documentId);
    }

    public function findByEmail(?string $tenantId, Email $email): Collection
    {
        return $this->customerRepository->findByEmail($tenantId, $email->normalized());
    }

    public function findByPhone(?string $tenantId, string $phone): Collection
    {
        // Normalizar teléfono para búsqueda
        $normalizedPhone = $this->normalizePhoneForSearch($phone);
        return $this->customerRepository->findByPhone($tenantId, $normalizedPhone);
    }

    public function findBySimilarNames(?string $tenantId, Customer $customer): Collection
    {
        $searchTerms = [];

        if ($customer->getType()->isNatural()) {
            // Para personas naturales, buscar por nombres
            $firstName = $customer->getFirstName();
            $lastName = $customer->getLastName();
            
            if ($firstName) {
                $searchTerms[] = trim($firstName);
            }
            if ($lastName) {
                $searchTerms[] = trim($lastName);
            }
        } else {
            // Para personas jurídicas, buscar por razón social
            $businessName = $customer->getBusinessName();
            if ($businessName) {
                $searchTerms[] = trim($businessName);
            }
        }

        if (empty($searchTerms)) {
            return new Collection();
        }

        return $this->customerRepository->findBySimilarNames($tenantId, $searchTerms);
    }

    /**
     * Calcula un score de similitud entre dos clientes
     */
    public function calculateSimilarityScore(Customer $customer1, Customer $customer2): float
    {
        $score = 0.0;
        $maxScore = 0.0;

        // Documento exacto = 100% match
        if ($customer1->getDocumentId()->equals($customer2->getDocumentId())) {
            return 1.0;
        }

        // Email exacto = 80% match
        $maxScore += 0.8;
        if ($customer1->getEmail() && $customer2->getEmail() && 
            $customer1->getEmail()->equals($customer2->getEmail())) {
            $score += 0.8;
        }

        // Teléfono exacto = 60% match
        $maxScore += 0.6;
        if ($customer1->getPhone() && $customer2->getPhone() && 
            $this->normalizePhoneForSearch($customer1->getPhone()) === 
            $this->normalizePhoneForSearch($customer2->getPhone())) {
            $score += 0.6;
        }

        // Nombre similar = hasta 40% match
        $maxScore += 0.4;
        $nameScore = $this->calculateNameSimilarity($customer1, $customer2);
        $score += $nameScore * 0.4;

        return $maxScore > 0 ? $score / $maxScore : 0.0;
    }

    private function calculateNameSimilarity(Customer $customer1, Customer $customer2): float
    {
        if ($customer1->getType() !== $customer2->getType()) {
            return 0.0;
        }

        if ($customer1->getType()->isNatural()) {
            $name1 = trim(($customer1->getFirstName() ?? '') . ' ' . ($customer1->getLastName() ?? ''));
            $name2 = trim(($customer2->getFirstName() ?? '') . ' ' . ($customer2->getLastName() ?? ''));
        } else {
            $name1 = $customer1->getBusinessName();
            $name2 = $customer2->getBusinessName();
        }

        return $this->stringSimilarity($name1, $name2);
    }

    private function stringSimilarity(string $str1, string $str2): float
    {
        $str1 = $this->normalizeStringForComparison($str1);
        $str2 = $this->normalizeStringForComparison($str2);

        if ($str1 === $str2) {
            return 1.0;
        }

        similar_text($str1, $str2, $percent);
        return $percent / 100.0;
    }

    private function normalizeStringForComparison(string $str): string
    {
        // Convertir a minúsculas, remover acentos, espacios múltiples
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^\w\s]/', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    private function normalizePhoneForSearch(string $phone): string
    {
        // Remover todo excepto dígitos
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Determina si dos clientes son duplicados probables
     */
    public function areLikelyDuplicates(Customer $customer1, Customer $customer2): bool
    {
        $score = $this->calculateSimilarityScore($customer1, $customer2);
        
        // Umbral de 70% para considerar duplicados probables
        return $score >= 0.7;
    }

    /**
     * Genera un reporte de duplicados con scores
     */
    public function generateDuplicateReport(?string $tenantId, Customer $customer): array
    {
        $duplicates = $this->findPotentialDuplicates($tenantId, $customer);
        
        $report = [];
        foreach ($duplicates as $duplicate) {
            $score = $this->calculateSimilarityScore($customer, $duplicate);
            $report[] = [
                'customer' => $duplicate,
                'similarity_score' => $score,
                'is_likely_duplicate' => $score >= 0.7,
                'match_reasons' => $this->getMatchReasons($customer, $duplicate)
            ];
        }

        // Ordenar por score descendente
        usort($report, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return $report;
    }

    private function getMatchReasons(Customer $customer1, Customer $customer2): array
    {
        $reasons = [];

        if ($customer1->getDocumentId()->equals($customer2->getDocumentId())) {
            $reasons[] = 'Mismo documento de identidad';
        }

        if ($customer1->getEmail() && $customer2->getEmail() && 
            $customer1->getEmail()->equals($customer2->getEmail())) {
            $reasons[] = 'Mismo email';
        }

        if ($customer1->getPhone() && $customer2->getPhone() && 
            $this->normalizePhoneForSearch($customer1->getPhone()) === 
            $this->normalizePhoneForSearch($customer2->getPhone())) {
            $reasons[] = 'Mismo teléfono';
        }

        $nameScore = $this->calculateNameSimilarity($customer1, $customer2);
        if ($nameScore >= 0.8) {
            $reasons[] = 'Nombres muy similares';
        } elseif ($nameScore >= 0.6) {
            $reasons[] = 'Nombres similares';
        }

        return $reasons;
    }
}

