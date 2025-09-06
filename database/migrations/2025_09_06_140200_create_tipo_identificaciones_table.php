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
        Schema::create('tipo_identificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
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

            // Se define un nombre de índice más corto para evitar el error de longitud
            $table->index(
                ['id_usuario_crea', 'id_usuario_modifica', 'id_usuario_elimina'],
                'idx_tipo_identif_usuarios'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_identificaciones');
    }
};
