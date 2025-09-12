<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Customers\Infrastructure\Models\CustomerModel;
use App\Modules\Customers\Infrastructure\Models\CustomerContactModel;
use App\Modules\Customers\Infrastructure\Models\CustomerAddressModel;
use App\Modules\Customers\Infrastructure\Models\CustomerTaxProfileModel;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        // Crear clientes de ejemplo
        $customers = [
            [
                'tenant_id' => null,
                'type' => 'natural',
                'document_type' => 'CC',
                'document_number' => '12345678',
                'business_name' => 'Juan Pérez',
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'email' => 'juan.perez@example.com',
                'phone' => '+57 300 123 4567',
                'status' => 'active',
                'segment' => 'VIP',
                'notes' => 'Cliente frecuente, excelente historial de pagos.',
            ],
            [
                'tenant_id' => null,
                'type' => 'juridical',
                'document_type' => 'NIT',
                'document_number' => '9001234567',
                'business_name' => 'Tecnología Avanzada S.A.S.',
                'first_name' => null,
                'last_name' => null,
                'email' => 'contacto@tecavanzada.com',
                'phone' => '+57 1 234 5678',
                'status' => 'active',
                'segment' => 'Premium',
                'notes' => 'Empresa de tecnología, contratos anuales.',
            ],
            [
                'tenant_id' => null,
                'type' => 'natural',
                'document_type' => 'CC',
                'document_number' => '87654321',
                'business_name' => 'María González',
                'first_name' => 'María',
                'last_name' => 'González',
                'email' => 'maria.gonzalez@example.com',
                'phone' => '+57 300 987 6543',
                'status' => 'prospect',
                'segment' => 'Estándar',
                'notes' => 'Prospecto interesado en servicios premium.',
            ],
            [
                'tenant_id' => null,
                'type' => 'juridical',
                'document_type' => 'NIT',
                'document_number' => '8001234567',
                'business_name' => 'Comercializadora del Norte Ltda.',
                'first_name' => null,
                'last_name' => null,
                'email' => 'ventas@comercializadoranorte.com',
                'phone' => '+57 5 345 6789',
                'status' => 'inactive',
                'segment' => 'Estándar',
                'notes' => 'Cliente inactivo desde el último trimestre.',
            ],
            [
                'tenant_id' => null,
                'type' => 'natural',
                'document_type' => 'CE',
                'document_number' => '123456789',
                'business_name' => 'Carlos Rodriguez',
                'first_name' => 'Carlos',
                'last_name' => 'Rodriguez',
                'email' => 'carlos.rodriguez@example.com',
                'phone' => '+57 300 555 0123',
                'status' => 'suspended',
                'segment' => null,
                'notes' => 'Cliente suspendido por pagos atrasados.',
            ]
        ];

        foreach ($customers as $customerData) {
            $customer = CustomerModel::create($customerData);

            // Crear contactos para cada cliente
            $this->createContactsForCustomer($customer);

            // Crear direcciones para cada cliente
            $this->createAddressesForCustomer($customer);

            // Crear perfil tributario para personas jurídicas
            if ($customer->type === 'juridical') {
                $this->createTaxProfileForCustomer($customer);
            }
        }
    }

    private function createContactsForCustomer(CustomerModel $customer): void
    {
        if ($customer->type === 'juridical') {
            // Para personas jurídicas, crear contactos adicionales
            CustomerContactModel::create([
                'customer_id' => $customer->id,
                'role' => 'Gerente General',
                'name' => 'Ana Martínez',
                'email' => 'gerencia@' . explode('@', $customer->email)[1],
                'phone' => '+57 300 111 2222',
                'is_primary' => true,
                'notes' => 'Contacto principal para decisiones estratégicas.',
            ]);

            CustomerContactModel::create([
                'customer_id' => $customer->id,
                'role' => 'Contador',
                'name' => 'Pedro Sánchez',
                'email' => 'contabilidad@' . explode('@', $customer->email)[1],
                'phone' => '+57 300 333 4444',
                'is_primary' => false,
                'notes' => 'Contacto para temas financieros y contables.',
            ]);
        } else {
            // Para personas naturales, crear un contacto alternativo
            CustomerContactModel::create([
                'customer_id' => $customer->id,
                'role' => 'Personal',
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'is_primary' => true,
                'notes' => 'Contacto principal del cliente.',
            ]);
        }
    }

    private function createAddressesForCustomer(CustomerModel $customer): void
    {
        // Dirección principal
        CustomerAddressModel::create([
            'customer_id' => $customer->id,
            'type' => 'billing',
            'address_line_1' => 'Calle ' . rand(10, 99) . ' # ' . rand(10, 99) . ' - ' . rand(10, 99),
            'address_line_2' => 'Apto ' . rand(100, 999),
            'city' => 'Bogotá',
            'state' => 'Cundinamarca',
            'postal_code' => '110111',
            'country_code' => 'CO',
            'is_default' => true,
            'notes' => 'Dirección principal de facturación.',
        ]);

        // Si es persona jurídica, agregar dirección de oficina
        if ($customer->type === 'juridical') {
            CustomerAddressModel::create([
                'customer_id' => $customer->id,
                'type' => 'office',
                'address_line_1' => 'Carrera ' . rand(10, 99) . ' # ' . rand(10, 99) . ' - ' . rand(10, 99),
                'address_line_2' => 'Oficina ' . rand(100, 999),
                'city' => 'Medellín',
                'state' => 'Antioquia',
                'postal_code' => '050001',
                'country_code' => 'CO',
                'is_default' => false,
                'notes' => 'Oficina principal de operaciones.',
            ]);
        }
    }

    private function createTaxProfileForCustomer(CustomerModel $customer): void
    {
        CustomerTaxProfileModel::create([
            'customer_id' => $customer->id,
            'tax_regime' => $customer->id % 2 === 0 ? 'common' : 'simplified',
            'tax_responsibilities' => [
                'R-99-PN', // Responsable del régimen común
                'ZZ', // No responsable
            ],
            'activity_codes' => [
                '6201', // Actividades de programación informática
                '6202', // Actividades de consultoría informática
            ],
            'tax_address' => 'Calle ' . rand(10, 99) . ' # ' . rand(10, 99) . ' - ' . rand(10, 99) . ', Bogotá',
            'is_retention_agent' => $customer->id % 3 === 0,
            'is_self_retainer' => $customer->id % 4 === 0,
            'notes' => 'Perfil tributario estándar para empresas de tecnología.',
        ]);
    }
}

