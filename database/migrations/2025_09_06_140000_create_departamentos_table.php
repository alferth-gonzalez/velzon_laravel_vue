<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('departamento');
            $table->foreignId('id_usuario_crea')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('id_usuario_modifica')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('id_usuario_elimina')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->softDeletes(); // crea automaticamente las columnas deleted_at
            $table->timestamps(); // crea automaticamente las columnas created_at y updated_at
            
            $table->index(['id_usuario_crea', 'id_usuario_modifica', 'id_usuario_elimina'], 'idx_departamentos_usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
