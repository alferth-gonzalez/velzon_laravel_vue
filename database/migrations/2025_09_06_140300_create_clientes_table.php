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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('identificacion')->unique();
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->foreignId('id_tipo_identificacion')
                ->nullable()
                ->constrained('tipo_identificaciones')
                ->nullOnDelete();
            $table->foreignId('id_ciudad')
                ->nullable()
                ->constrained('municipios', 'id_municipio')
                ->nullOnDelete();
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
            $table->softDeletes();
            $table->timestamps();

            $table->index(
                ['id_usuario_crea', 'id_usuario_modifica', 'id_usuario_elimina'],
                'idx_clientes_usuarios'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
