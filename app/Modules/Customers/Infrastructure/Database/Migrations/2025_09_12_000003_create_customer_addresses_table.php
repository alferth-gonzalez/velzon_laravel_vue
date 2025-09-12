<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            // Información de la dirección
            $table->enum('type', ['billing', 'shipping', 'legal', 'office', 'home'])->index();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 20);
            $table->char('country_code', 2)->default('CO');
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['customer_id', 'type']);
            $table->index(['customer_id', 'is_default']);
            $table->index(['country_code', 'state', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
