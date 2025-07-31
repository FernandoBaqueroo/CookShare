<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Desayunos',
                'descripcion' => 'Recetas para comenzar el día',
                'icono' => '🍳',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Platos Principales',
                'descripcion' => 'Comidas principales del día',
                'icono' => '🍽️',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Postres',
                'descripcion' => 'Dulces y postres deliciosos',
                'icono' => '🍰',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Aperitivos',
                'descripcion' => 'Entrantes y bocadillos',
                'icono' => '🥗',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Bebidas',
                'descripcion' => 'Bebidas y cócteles',
                'icono' => '🥤',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Sopas',
                'descripcion' => 'Sopas y caldos',
                'icono' => '🍲',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ensaladas',
                'descripcion' => 'Ensaladas frescas y saludables',
                'icono' => '🥙',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pasta',
                'descripcion' => 'Platos de pasta italiana',
                'icono' => '🍝',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categorias')->insert($categorias);
    }
}
