<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EtiquetasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etiquetas = [
            ['nombre' => 'Rápido', 'color' => '#28a745', 'activa' => true],
            ['nombre' => 'Vegetariano', 'color' => '#17a2b8', 'activa' => true],
            ['nombre' => 'Sin Gluten', 'color' => '#ffc107', 'activa' => true],
            ['nombre' => 'Bajo en Calorías', 'color' => '#6f42c1', 'activa' => true],
            ['nombre' => 'Tradicional', 'color' => '#dc3545', 'activa' => true],
            ['nombre' => 'Fácil', 'color' => '#20c997', 'activa' => true],
            ['nombre' => 'Gourmet', 'color' => '#fd7e14', 'activa' => true],
            ['nombre' => 'Económico', 'color' => '#6c757d', 'activa' => true],
        ];

        foreach ($etiquetas as $etiqueta) {
            $etiqueta['created_at'] = now();
            $etiqueta['updated_at'] = now();
            DB::table('etiquetas')->insert($etiqueta);
        }
    }
}
