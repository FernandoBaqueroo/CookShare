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
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->integer('puntuacion');
            $table->timestamp('fecha_valoracion')->useCurrent();
            $table->timestamps();

            // Restricción para puntuación entre 1 y 5
            $table->check('puntuacion >= 1 AND puntuacion <= 5');
            
            // Índice único para evitar valoraciones duplicadas
            $table->unique(['usuario_id', 'receta_id'], 'unique_usuario_receta');
            
            // Índices para mejorar rendimiento
            $table->index('puntuacion');
            $table->index(['receta_id', 'puntuacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
