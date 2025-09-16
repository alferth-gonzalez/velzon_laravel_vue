<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('emp_employees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // Multitenencia opcional
            $table->ulid('tenant_id')->nullable()->index();

            $table->string('first_name', 80);
            $table->string('last_name', 80)->nullable();
            $table->string('document_type', 10);            // CC, NIT, CE...
            $table->string('document_number', 32);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();

            $table->date('hire_date')->nullable();
            $table->string('status', 20)->default('active'); // active|inactive

            // Auditoría básica
            $table->ulid('created_by')->nullable()->index();
            $table->ulid('updated_by')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Unicidad por tenant + documento
            $table->unique(['tenant_id','document_type','document_number'], 'emp_employees_tenant_doc_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('emp_employees');
    }
};