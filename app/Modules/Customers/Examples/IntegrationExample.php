<?php

declare(strict_types=1);

namespace App\Modules\Customers\Examples;

use App\Modules\Customers\Application\Contracts\CustomerReadModelInterface;
use App\Modules\Customers\Application\Contracts\CustomerExportPortInterface;
use App\Modules\Customers\Domain\Events\CustomerCreated;
use Illuminate\Support\Facades\Event;

/**
 * Ejemplos de cómo otros módulos pueden integrar con el módulo de Clientes
 */
class IntegrationExample
{
    public function __construct(
        private CustomerReadModelInterface $customerReadModel,
        private CustomerExportPortInterface $customerExport
    ) {}

    /**
     * Ejemplo: Obtener información básica de un cliente para mostrar en facturas
     */
    public function getCustomerForInvoice(int $customerId): ?array
    {
        $customer = $this->customerReadModel->getCustomerBasicInfo($customerId);
        
        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer['id'],
            'name' => $customer['business_name'],
            'status' => $customer['status'],
            'is_active' => $customer['status'] === 'active'
        ];
    }

    /**
     * Ejemplo: Validar que un cliente esté activo antes de crear una venta
     */
    public function validateCustomerForSale(int $customerId): bool
    {
        return $this->customerReadModel->isCustomerActive($customerId);
    }

    /**
     * Ejemplo: Obtener información tributaria para facturación electrónica
     */
    public function getCustomerTaxDataForBilling(int $customerId): ?array
    {
        $taxInfo = $this->customerReadModel->getCustomerTaxInfo($customerId);
        $contactInfo = $this->customerReadModel->getCustomerContactInfo($customerId);
        
        if (!$taxInfo || !$contactInfo) {
            return null;
        }

        return [
            'tax_regime' => $taxInfo['tax_regime'],
            'tax_responsibilities' => $taxInfo['tax_responsibilities'],
            'is_retention_agent' => $taxInfo['is_retention_agent'],
            'email' => $contactInfo['email'],
            'addresses' => $contactInfo['addresses']
        ];
    }

    /**
     * Ejemplo: Buscar clientes para autocompletado en formularios
     */
    public function searchCustomersForAutocomplete(string $query): array
    {
        $customers = $this->customerReadModel->searchCustomers($query, null, 5);
        
        return array_map(function ($customer) {
            return [
                'value' => $customer['id'],
                'label' => $customer['business_name'],
                'subtitle' => $customer['status']
            ];
        }, $customers);
    }

    /**
     * Ejemplo: Exportar clientes para reportes de ventas
     */
    public function exportCustomersForSalesReport(array $customerIds): string
    {
        $fields = ['business_name', 'email', 'phone', 'status', 'segment'];
        return $this->customerExport->exportToCSV($customerIds, $fields);
    }

    /**
     * Ejemplo: Listener para cuando se crea un cliente
     */
    public function handleCustomerCreated(): void
    {
        Event::listen(CustomerCreated::class, function (CustomerCreated $event) {
            $customer = $event->customer;
            
            // Ejemplo: Crear perfil de CRM automáticamente
            $this->createCRMProfile($customer->getId());
            
            // Ejemplo: Enviar email de bienvenida
            $this->sendWelcomeEmail($customer->getEmail());
            
            // Ejemplo: Crear cuenta contable
            $this->createAccountingAccount($customer->getId(), $customer->getType());
        });
    }

    private function createCRMProfile(int $customerId): void
    {
        // Simular creación de perfil en módulo CRM
        logger('Creating CRM profile for customer', ['customer_id' => $customerId]);
    }

    private function sendWelcomeEmail(?object $email): void
    {
        if ($email) {
            // Simular envío de email de bienvenida
            logger('Sending welcome email', ['email' => $email->value]);
        }
    }

    private function createAccountingAccount(int $customerId, object $customerType): void
    {
        // Simular creación de cuenta contable
        logger('Creating accounting account', [
            'customer_id' => $customerId,
            'account_type' => $customerType->value
        ]);
    }
}

/**
 * Ejemplo de cómo registrar listeners en EventServiceProvider
 */
class EventListenerExample
{
    /**
     * En app/Providers/EventServiceProvider.php
     */
    public function getEventListeners(): array
    {
        return [
            \App\Modules\Customers\Domain\Events\CustomerCreated::class => [
                \App\Modules\CRM\Listeners\CreateCRMProfile::class,
                \App\Modules\Accounting\Listeners\CreateCustomerAccount::class,
                \App\Modules\Marketing\Listeners\AddToWelcomeCampaign::class,
            ],
            
            \App\Modules\Customers\Domain\Events\CustomerUpdated::class => [
                \App\Modules\CRM\Listeners\UpdateCRMProfile::class,
                \App\Modules\Accounting\Listeners\UpdateCustomerAccount::class,
            ],
            
            \App\Modules\Customers\Domain\Events\CustomerMerged::class => [
                \App\Modules\CRM\Listeners\MergeCRMProfiles::class,
                \App\Modules\Sales\Listeners\UpdateSalesHistory::class,
                \App\Modules\Accounting\Listeners\MergeAccountingAccounts::class,
            ],
            
            \App\Modules\Customers\Domain\Events\CustomerBlacklisted::class => [
                \App\Modules\Sales\Listeners\CancelPendingOrders::class,
                \App\Modules\CRM\Listeners\FlagAsBlacklisted::class,
                \App\Modules\Security\Listeners\LogSecurityEvent::class,
            ],
        ];
    }
}

/**
 * Ejemplo de Policy para controlar acceso desde otros módulos
 */
class CustomerAccessExample
{
    /**
     * Verificar si un usuario puede acceder a los datos de un cliente
     */
    public function canAccessCustomer(int $userId, int $customerId): bool
    {
        // Ejemplo: Solo usuarios del mismo tenant pueden acceder
        $userTenant = $this->getUserTenant($userId);
        $customer = $this->customerReadModel->getCustomerById($customerId);
        
        return $customer && $customer['tenant_id'] === $userTenant;
    }

    /**
     * Verificar si un usuario puede exportar clientes
     */
    public function canExportCustomers(int $userId, array $customerIds): bool
    {
        return $this->customerExport->validateExportPermissions($customerIds, $userId);
    }

    private function getUserTenant(int $userId): ?string
    {
        // Simular obtención del tenant del usuario
        return 'tenant_' . ($userId % 3);
    }
}
