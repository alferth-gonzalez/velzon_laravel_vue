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
        Schema::create('municipios', function (Blueprint $table) {
            $table->id('id_municipio');
            $table->string('codigo_dian');
            $table->string('municipio');
            $table->integer('estado');
            $table->foreignId('id_departamento')
                ->constrained('departamentos', 'id_departamento')
                ->nullable();
            $table->text('latitud');
            $table->text('longitud');
            $table->foreignId('id_usuario_crea')
                ->nullable()
                ->constrained('users')
                ->nullable();
            $table->foreignId('id_usuario_modifica')
                ->nullable()
                ->constrained('users')
                ->nullable();
            $table->foreignId('id_usuario_elimina')
                ->nullable()
                ->constrained('users')
                ->nullable();
            $table->softDeletes(); // crea automaticamente las columnas deleted_at
            $table->timestamps(); // crea automaticamente las columnas created_at y updated_at
        });

        // Crear índices para los campos de usuario
        Schema::table('municipios', function (Blueprint $table) {
            $table->index(['id_usuario_crea', 'id_usuario_modifica', 'id_usuario_elimina'], 'idx_municipios_usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
