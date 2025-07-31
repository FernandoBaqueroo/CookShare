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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->integer('tiempo_preparacion'); // en minutos
            $table->integer('tiempo_coccion')->default(0); // en minutos
            $table->integer('porciones');
            $table->enum('dificultad', ['Fácil', 'Intermedio', 'Difícil']);
            $table->longText('foto_principal')->nullable();
            $table->longText('instrucciones');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index('activa');
            $table->index('fecha_creacion');
            $table->index('dificultad');
            $table->index(['usuario_id', 'activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
