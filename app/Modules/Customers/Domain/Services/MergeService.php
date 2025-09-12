<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Services;

use App\Modules\Customers\Domain\Entities\Customer;
use App\Modules\Customers\Domain\Events\CustomerMerged;
use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Application\Contracts\IdempotencyRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MergeService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private IdempotencyRepositoryInterface $idempotencyRepository
    ) {}

    /**
     * Combina dos clientes de forma idempotente
     */
    public function mergeCustomers(
        int $sourceCustomerId,
        int $destinationCustomerId,
        string $idempotencyKey,
        ?int $actorId = null,
        ?string $reason = null
    ): Customer {
        Log::withContext([
            'module' => 'customers',
            'action' => 'merge_customers',
            'source_id' => $sourceCustomerId,
            'destination_id' => $destinationCustomerId,
            'idempotency_key' => $idempotencyKey,
            'actor_id' => $actorId
        ]);

        // Verificar idempotencia
        if ($this->idempotencyRepository->exists($idempotencyKey)) {
            Log::info('Merge ya procesado, retornando resultado existente');
            return $this->customerRepository->findById($destinationCustomerId);
        }

        // Validaciones
        if ($sourceCustomerId === $destinationCustomerId) {
            throw new \DomainException('No se puede combinar un cliente consigo mismo');
        }

        $sourceCustomer = $this->customerRepository->findById($sourceCustomerId);
        $destinationCustomer = $this->customerRepository->findById($destinationCustomerId);

        if (!$sourceCustomer || !$destinationCustomer) {
            throw new \DomainException('Uno o ambos clientes no existen');
        }

        // Validar que pertenezcan al mismo tenant
        if ($sourceCustomer->getTenantId() !== $destinationCustomer->getTenantId()) {
            throw new \DomainException('Solo se pueden combinar clientes del mismo tenant');
        }

        // Validar que el cliente fuente no esté en lista negra
        if (!$sourceCustomer->getStatus()->canBeUpdated()) {
            throw new \DomainException('No se puede combinar un cliente en lista negra');
        }

        try {
            DB::beginTransaction();

            // Registrar idempotencia
            $this->idempotencyRepository->store($idempotencyKey, [
                'source_id' => $sourceCustomerId,
                'destination_id' => $destinationCustomerId,
                'actor_id' => $actorId,
                'reason' => $reason
            ]);

            // Realizar el merge
            $mergedCustomer = $this->performMerge($sourceCustomer, $destinationCustomer, $reason);

            // Registrar evento
            $mergeId = $this->generateMergeId($sourceCustomerId, $destinationCustomerId);
            $event = new CustomerMerged($sourceCustomer, $mergedCustomer, $mergeId);
            $mergedCustomer->recordEvent($event);

            // Soft delete del cliente fuente
            $sourceCustomer->softDelete("Combinado con cliente #{$destinationCustomerId}");
            $this->customerRepository->save($sourceCustomer);

            // Guardar cliente destino actualizado
            $this->customerRepository->save($mergedCustomer);

            DB::commit();

            Log::info('Merge completado exitosamente', [
                'merge_id' => $mergeId,
                'merged_customer_id' => $mergedCustomer->getId()
            ]);

            return $mergedCustomer;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error durante el merge', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \DomainException('Error al combinar clientes: ' . $e->getMessage());
        }
    }

    private function performMerge(Customer $source, Customer $destination, ?string $reason = null): Customer
    {
        // Estrategia de merge: conservar datos más completos del destino,
        // pero tomar datos faltantes del origen

        $updates = [];

        // Actualizar información básica si está vacía en destino
        if (empty($destination->getFirstName()) && !empty($source->getFirstName())) {
            $updates['firstName'] = $source->getFirstName();
        }

        if (empty($destination->getLastName()) && !empty($source->getLastName())) {
            $updates['lastName'] = $source->getLastName();
        }

        if (empty($destination->getEmail()) && $source->getEmail()) {
            $updates['email'] = $source->getEmail();
        }

        if (empty($destination->getPhone()) && !empty($source->getPhone())) {
            $updates['phone'] = $source->getPhone();
        }

        if (empty($destination->getSegment()) && !empty($source->getSegment())) {
            $updates['segment'] = $source->getSegment();
        }

        // Combinar notas
        $combinedNotes = $this->combineNotes(
            $destination->getNotes(),
            $source->getNotes(),
            $reason
        );
        if ($combinedNotes !== $destination->getNotes()) {
            $updates['notes'] = $combinedNotes;
        }

        // Aplicar actualizaciones si las hay
        if (!empty($updates)) {
            $destination->update(
                businessName: $destination->getBusinessName(),
                firstName: $updates['firstName'] ?? $destination->getFirstName(),
                lastName: $updates['lastName'] ?? $destination->getLastName(),
                email: $updates['email'] ?? $destination->getEmail(),
                phone: $updates['phone'] ?? $destination->getPhone(),
                segment: $updates['segment'] ?? $destination->getSegment(),
                notes: $updates['notes'] ?? $destination->getNotes()
            );
        }

        // Transferir contactos únicos
        $this->transferContacts($source, $destination);

        // Transferir direcciones únicas
        $this->transferAddresses($source, $destination);

        // Transferir perfil tributario si el destino no lo tiene
        if (!$destination->getTaxProfile() && $source->getTaxProfile()) {
            $destination->setTaxProfile($source->getTaxProfile());
        }

        return $destination;
    }

    private function combineNotes(?string $destinationNotes, ?string $sourceNotes, ?string $reason): string
    {
        $parts = [];

        if (!empty($destinationNotes)) {
            $parts[] = $destinationNotes;
        }

        if (!empty($sourceNotes)) {
            $parts[] = "Notas del cliente combinado: " . $sourceNotes;
        }

        if (!empty($reason)) {
            $parts[] = "Razón del merge: " . $reason;
        }

        $parts[] = "Combinado el " . now()->format('Y-m-d H:i:s');

        return implode("\n\n", $parts);
    }

    private function transferContacts(Customer $source, Customer $destination): void
    {
        $destinationEmails = $destination->getContacts()
            ->filter(fn($contact) => $contact->getEmail())
            ->map(fn($contact) => $contact->getEmail()->normalized())
            ->toArray();

        $destinationPhones = $destination->getContacts()
            ->filter(fn($contact) => $contact->getPhone())
            ->map(fn($contact) => $contact->getPhone()->normalized())
            ->toArray();

        foreach ($source->getContacts() as $contact) {
            $isDuplicate = false;

            // Verificar duplicado por email
            if ($contact->getEmail()) {
                $normalizedEmail = $contact->getEmail()->normalized();
                if (in_array($normalizedEmail, $destinationEmails, true)) {
                    $isDuplicate = true;
                }
            }

            // Verificar duplicado por teléfono
            if (!$isDuplicate && $contact->getPhone()) {
                $normalizedPhone = $contact->getPhone()->normalized();
                if (in_array($normalizedPhone, $destinationPhones, true)) {
                    $isDuplicate = true;
                }
            }

            // Solo transferir si no es duplicado
            if (!$isDuplicate) {
                $destination->addContact($contact);
            }
        }
    }

    private function transferAddresses(Customer $source, Customer $destination): void
    {
        $destinationAddresses = $destination->getAddresses()
            ->map(fn($address) => $address->getFullAddress())
            ->toArray();

        foreach ($source->getAddresses() as $address) {
            $fullAddress = $address->getFullAddress();
            
            // Solo transferir si no existe una dirección similar
            if (!in_array($fullAddress, $destinationAddresses, true)) {
                $destination->addAddress($address);
            }
        }
    }

    private function generateMergeId(int $sourceId, int $destinationId): string
    {
        return 'merge_' . $sourceId . '_to_' . $destinationId . '_' . time();
    }

    /**
     * Valida que el merge sea posible
     */
    public function validateMerge(Customer $source, Customer $destination): array
    {
        $violations = [];

        if ($source->getId() === $destination->getId()) {
            $violations[] = 'No se puede combinar un cliente consigo mismo';
        }

        if ($source->getTenantId() !== $destination->getTenantId()) {
            $violations[] = 'Solo se pueden combinar clientes del mismo tenant';
        }

        if (!$source->getStatus()->canBeUpdated()) {
            $violations[] = 'El cliente origen está en lista negra y no puede ser combinado';
        }

        if (!$destination->getStatus()->canBeUpdated()) {
            $violations[] = 'El cliente destino está en lista negra y no puede recibir el merge';
        }

        if ($source->getType() !== $destination->getType()) {
            $violations[] = 'Solo se pueden combinar clientes del mismo tipo (natural/jurídica)';
        }

        return $violations;
    }

    /**
     * Genera una vista previa del merge sin ejecutarlo
     */
    public function previewMerge(Customer $source, Customer $destination): array
    {
        return [
            'source' => $source->toArray(),
            'destination' => $destination->toArray(),
            'preview_result' => $this->generateMergePreview($source, $destination),
            'validation_errors' => $this->validateMerge($source, $destination)
        ];
    }

    private function generateMergePreview(Customer $source, Customer $destination): array
    {
        $preview = $destination->toArray();
        
        // Mostrar qué campos se actualizarían
        $changes = [];

        if (empty($destination->getFirstName()) && !empty($source->getFirstName())) {
            $changes['first_name'] = $source->getFirstName();
        }

        if (empty($destination->getLastName()) && !empty($source->getLastName())) {
            $changes['last_name'] = $source->getLastName();
        }

        if (empty($destination->getEmail()) && $source->getEmail()) {
            $changes['email'] = $source->getEmail()->value;
        }

        if (empty($destination->getPhone()) && !empty($source->getPhone())) {
            $changes['phone'] = $source->getPhone();
        }

        if (empty($destination->getSegment()) && !empty($source->getSegment())) {
            $changes['segment'] = $source->getSegment();
        }

        return [
            'final_data' => array_merge($preview, $changes),
            'changes_applied' => $changes,
            'contacts_added' => $source->getContacts()->count(),
            'addresses_added' => $source->getAddresses()->count(),
            'tax_profile_updated' => !$destination->getTaxProfile() && $source->getTaxProfile()
        ];
    }
}

