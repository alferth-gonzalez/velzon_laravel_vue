<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            // Información tributaria
            $table->enum('tax_regime', ['simplified', 'common', 'special', 'no_responsible', 'great_contributor']);
            $table->json('tax_responsibilities')->nullable(); // Array de responsabilidades tributarias
            $table->json('activity_codes')->nullable(); // Array de códigos de actividad económica
            $table->text('tax_address')->nullable();
            $table->boolean('is_retention_agent')->default(false);
            $table->boolean('is_self_retainer')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->unique('customer_id');
            $table->index('tax_regime');
            $table->index('is_retention_agent');
            $table->index('is_self_retainer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tax_profiles');
    }
};
