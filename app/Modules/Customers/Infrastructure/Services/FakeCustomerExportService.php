<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Services;

use App\Modules\Customers\Application\Contracts\CustomerExportPortInterface;
use Illuminate\Support\Facades\Log;

class FakeCustomerExportService implements CustomerExportPortInterface
{
    public function exportToCSV(array $customerIds, array $fields = []): string
    {
        Log::info('Exportación CSV solicitada', [
            'customer_ids' => $customerIds,
            'fields' => $fields
        ]);

        // Simulación de exportación CSV
        return '/tmp/customers_export_' . time() . '.csv';
    }

    public function exportToExcel(array $customerIds, array $fields = []): string
    {
        Log::info('Exportación Excel solicitada', [
            'customer_ids' => $customerIds,
            'fields' => $fields
        ]);

        // Simulación de exportación Excel
        return '/tmp/customers_export_' . time() . '.xlsx';
    }

    public function getAvailableFields(): array
    {
        return [
            'id' => 'ID',
            'business_name' => 'Razón Social',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'document_type' => 'Tipo Documento',
            'document_number' => 'Número Documento',
            'email' => 'Email',
            'phone' => 'Teléfono',
            'status' => 'Estado',
            'type' => 'Tipo Cliente',
            'segment' => 'Segmento',
            'created_at' => 'Fecha Creación',
            'updated_at' => 'Fecha Actualización',
        ];
    }

    public function validateExportPermissions(array $customerIds, int $userId): bool
    {
        Log::info('Validación de permisos de exportación', [
            'user_id' => $userId,
            'customer_count' => count($customerIds)
        ]);

        // En una implementación real, verificaría permisos por tenant, rol, etc.
        return true;
    }

    public function getExportableData(array $customerIds, array $fields = []): array
    {
        Log::info('Obtención de datos para exportación', [
            'customer_ids' => $customerIds,
            'fields' => $fields
        ]);

        // En una implementación real, obtendría los datos reales de la base de datos
        return [
            'data' => [],
            'total_records' => count($customerIds),
            'exported_at' => now()->toISOString(),
        ];
    }
}
