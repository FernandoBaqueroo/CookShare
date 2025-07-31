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
        Schema::create('receta_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->onDelete('cascade');
            $table->foreignId('ingrediente_id')->constrained('ingredientes');
            $table->decimal('cantidad', 8, 2);
            $table->string('notas', 100)->nullable(); // opcional, ej: "picado finamente"
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['receta_id', 'ingrediente_id'], 'unique_receta_ingrediente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_ingredientes');
    }
};
