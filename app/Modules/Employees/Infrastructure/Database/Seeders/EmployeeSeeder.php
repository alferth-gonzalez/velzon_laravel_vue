<?php

namespace App\Modules\Employees\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Employees\Infrastructure\Database\Models\EmployeeModel;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        EmployeeModel::updateOrCreate(
            ['document_number' => '1234567890'],
            [
                'id' => (string) Str::ulid(),
                'tenant_id' => '1', // Asume un tenant_id por defecto
                'first_name' => 'Alferth',
                'last_name' => 'Gonzalez',
                'document_type' => 'CC',
                'document_number' => '1234567890',
                'email' => 'alferth.gonzalez@example.com',
                'phone' => '3001234567',
                'hire_date' => '2023-01-15',
                'status' => 'active',
            ]
        );

        EmployeeModel::updateOrCreate(
            ['document_number' => '987654321'],
            [
                'id' => (string) Str::ulid(),
                'tenant_id' => '1',
                'first_name' => 'Maria',
                'last_name' => 'Perez',
                'document_type' => 'CE',
                'document_number' => '987654321',
                'email' => 'maria.perez@example.com',
                'phone' => '3109876543',
                'hire_date' => '2022-05-20',
                'status' => 'inactive',
            ]
        );

        EmployeeModel::updateOrCreate(
            ['document_number' => '111111111'],
            [
                'id' => (string) Str::ulid(),
                'tenant_id' => '1',
                'first_name' => 'Carlos',
                'last_name' => 'Rodriguez',
                'document_type' => 'CC',
                'document_number' => '111111111',
                'email' => 'carlos.rodriguez@example.com',
                'phone' => '3111111111',
                'hire_date' => '2024-01-01',
                'status' => 'active',
            ]
        );
    }
}
