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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_usuario', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('token', 255)->nullable()->unique();
            $table->string('nombre_completo', 100);
            $table->text('bio')->nullable();
            $table->string('foto_perfil', 255)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index('activo');
            $table->index('nombre_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
