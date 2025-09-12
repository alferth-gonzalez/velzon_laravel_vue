<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            // Información de auditoría
            $table->string('action'); // create, update, delete, merge, blacklist
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('reason')->nullable();
            
            // Cambios
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable(); // Información adicional del contexto
            
            $table->timestamp('created_at');
            
            // Índices
            $table->index(['customer_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_audit_logs');
    }
};

