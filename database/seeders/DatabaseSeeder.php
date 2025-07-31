<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            CategoriasSeeder::class,
            UsuariosSeeder::class,
            IngredientesSeeder::class,
            EtiquetasSeeder::class,
            DatosCompletosSeeder::class, // Este incluye recetas, ingredientes, valoraciones, comentarios y favoritos
        ]);
    }
}
