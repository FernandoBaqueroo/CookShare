<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredientes = [
            ['nombre' => 'Huevos', 'unidad_medida' => 'unidades', 'activo' => true],
            ['nombre' => 'Harina', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Azúcar', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Leche', 'unidad_medida' => 'mililitros', 'activo' => true],
            ['nombre' => 'Mantequilla', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Tomate', 'unidad_medida' => 'unidades', 'activo' => true],
            ['nombre' => 'Cebolla', 'unidad_medida' => 'unidades', 'activo' => true],
            ['nombre' => 'Ajo', 'unidad_medida' => 'dientes', 'activo' => true],
            ['nombre' => 'Aceite de oliva', 'unidad_medida' => 'mililitros', 'activo' => true],
            ['nombre' => 'Sal', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Pimienta negra', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Queso', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Pollo', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Arroz', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Pasta', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Chocolate', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Vainilla', 'unidad_medida' => 'mililitros', 'activo' => true],
            ['nombre' => 'Limón', 'unidad_medida' => 'unidades', 'activo' => true],
            ['nombre' => 'Perejil', 'unidad_medida' => 'gramos', 'activo' => true],
            ['nombre' => 'Jamón', 'unidad_medida' => 'gramos', 'activo' => true],
        ];

        foreach ($ingredientes as $ingrediente) {
            $ingrediente['created_at'] = now();
            $ingrediente['updated_at'] = now();
            DB::table('ingredientes')->insert($ingrediente);
        }
    }
}
