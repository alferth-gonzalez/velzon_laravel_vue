<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_processed_events', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->string('event_type'); // merge, blacklist, etc.
            $table->json('payload'); // Datos del evento procesado
            $table->json('result')->nullable(); // Resultado del procesamiento
            $table->timestamp('processed_at');
            $table->timestamp('expires_at')->nullable(); // Para limpeza automática
            
            // Índices
            $table->index('event_type');
            $table->index('processed_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_processed_events');
    }
};

