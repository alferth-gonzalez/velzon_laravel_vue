<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            
            // Información del cliente
            $table->enum('type', ['natural', 'juridical'])->index();
            $table->enum('document_type', ['CC', 'NIT', 'CE', 'PA', 'TI', 'RC'])->index();
            $table->string('document_number', 20)->index();
            $table->string('business_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            
            // Contacto
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            
            // Estado y segmentación
            $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted', 'prospect'])
                  ->default('prospect')
                  ->index();
            $table->string('segment', 50)->nullable()->index();
            
            // Notas y auditoría
            $table->text('notes')->nullable();
            $table->string('blacklist_reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Índices únicos por tenant
            $table->unique(['tenant_id', 'document_type', 'document_number'], 'customers_tenant_document_unique');
            $table->unique(['tenant_id', 'email'], 'customers_tenant_email_unique');
            
            // Índices compuestos para consultas comunes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'segment']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
