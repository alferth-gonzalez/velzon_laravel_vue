<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Queries;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Services\DedupService;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Domain\ValueObjects\Email;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SearchCustomersQuery
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private DedupService $dedupService
    ) {}

    public function execute(
        ?string $tenantId,
        string $query,
        array $filters = [],
        int $limit = 20
    ): Collection {
        Log::withContext([
            'module' => 'customers',
            'query' => 'search_customers',
            'search_term' => $query,
            'tenant_id' => $tenantId,
            'filters' => $filters,
            'limit' => $limit
        ]);

        try {
            $results = new Collection();

            // Búsqueda general
            $generalResults = $this->customerRepository->search($tenantId, $query, $filters, $limit);
            $results = $results->merge($generalResults);

            // Intentar búsqueda específica por documento
            if ($this->looksLikeDocument($query)) {
                $documentResults = $this->searchByDocument($tenantId, $query);
                $results = $results->merge($documentResults);
            }

            // Intentar búsqueda específica por email
            if ($this->looksLikeEmail($query)) {
                $emailResults = $this->searchByEmail($tenantId, $query);
                $results = $results->merge($emailResults);
            }

            // Intentar búsqueda específica por teléfono
            if ($this->looksLikePhone($query)) {
                $phoneResults = $this->searchByPhone($tenantId, $query);
                $results = $results->merge($phoneResults);
            }

            // Remover duplicados y limitar resultados
            $results = $results->unique('id')->take($limit);

            Log::info('Búsqueda de clientes completada', [
                'results_count' => $results->count(),
                'search_term' => $query
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de clientes', [
                'search_term' => $query,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function searchByDocument(?string $tenantId, string $query): Collection
    {
        try {
            // Intentar diferentes formatos de documento
            $patterns = $this->extractDocumentPatterns($query);
            $results = new Collection();

            foreach ($patterns as $pattern) {
                if (isset($pattern['type']) && isset($pattern['number'])) {
                    $documentId = new DocumentId($pattern['type'], $pattern['number']);
                    $documentResults = $this->dedupService->findByDocument($tenantId, $documentId);
                    $results = $results->merge($documentResults);
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::warning('Error en búsqueda por documento', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    private function searchByEmail(?string $tenantId, string $query): Collection
    {
        try {
            $email = new Email($query);
            return $this->dedupService->findByEmail($tenantId, $email);
        } catch (\Exception $e) {
            Log::warning('Error en búsqueda por email', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    private function searchByPhone(?string $tenantId, string $query): Collection
    {
        try {
            return $this->dedupService->findByPhone($tenantId, $query);
        } catch (\Exception $e) {
            Log::warning('Error en búsqueda por teléfono', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    private function looksLikeDocument(string $query): bool
    {
        // Verificar si parece un documento (números con posibles separadores)
        return preg_match('/^[A-Z]{2,3}[:\-\s]*[0-9\.\-]+$/', strtoupper(trim($query))) ||
               preg_match('/^[0-9\.\-]{5,}$/', trim($query));
    }

    private function looksLikeEmail(string $query): bool
    {
        return filter_var($query, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function looksLikePhone(string $query): bool
    {
        $numbersOnly = preg_replace('/[^0-9]/', '', $query);
        return strlen($numbersOnly) >= 7 && strlen($numbersOnly) <= 15;
    }

    private function extractDocumentPatterns(string $query): array
    {
        $patterns = [];
        $cleanQuery = strtoupper(trim($query));

        // Patrón: CC:12345678 o CC-12345678 o CC 12345678
        if (preg_match('/^([A-Z]{2,3})[\:\-\s]+([0-9\.\-]+)$/', $cleanQuery, $matches)) {
            $patterns[] = [
                'type' => $matches[1],
                'number' => preg_replace('/[^0-9]/', '', $matches[2])
            ];
        }

        // Si es solo números, asumir CC
        if (preg_match('/^[0-9\.\-]{8,}$/', $cleanQuery)) {
            $patterns[] = [
                'type' => 'CC',
                'number' => preg_replace('/[^0-9]/', '', $cleanQuery)
            ];
        }

        return $patterns;
    }
}

