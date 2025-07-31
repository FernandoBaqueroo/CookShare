<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecetasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ejemplo de recetas con URLs de imágenes
        $recetas = [
            [
                'titulo' => 'Pasta Carbonara Clásica',
                'descripcion' => 'Una receta tradicional italiana con huevos, queso parmesano y panceta',
                'tiempo_preparacion' => 15,
                'tiempo_coccion' => 20,
                'porciones' => 4,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=800&h=600&fit=crop',
                'instrucciones' => '1. Cocinar la pasta al dente\n2. Preparar la salsa con huevos y queso\n3. Mezclar todo y servir caliente',
                'usuario_id' => 1,
                'categoria_id' => 1,
                'fecha_creacion' => now(),
                'fecha_actualizacion' => now(),
                'activa' => true
            ],
            [
                'titulo' => 'Ensalada César',
                'descripcion' => 'Ensalada fresca con aderezo casero y crutones',
                'tiempo_preparacion' => 20,
                'tiempo_coccion' => 0,
                'porciones' => 2,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=800&h=600&fit=crop',
                'instrucciones' => '1. Lavar la lechuga\n2. Preparar el aderezo\n3. Mezclar todos los ingredientes',
                'usuario_id' => 1,
                'categoria_id' => 2,
                'fecha_creacion' => now(),
                'fecha_actualizacion' => now(),
                'activa' => true
            ],
            [
                'titulo' => 'Tiramisú Casero',
                'descripcion' => 'Postre italiano clásico con café y mascarpone',
                'tiempo_preparacion' => 30,
                'tiempo_coccion' => 0,
                'porciones' => 6,
                'dificultad' => 'Intermedio',
                'foto_principal' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800&h=600&fit=crop',
                'instrucciones' => '1. Preparar el café\n2. Batir el mascarpone con huevos\n3. Montar las capas y refrigerar',
                'usuario_id' => 1,
                'categoria_id' => 3,
                'fecha_creacion' => now(),
                'fecha_actualizacion' => now(),
                'activa' => true
            ]
        ];

        foreach ($recetas as $receta) {
            $recetaId = DB::table('recetas')->insertGetId($receta);
            
            // Procesar la imagen si es una URL
            if (filter_var($receta['foto_principal'], FILTER_VALIDATE_URL)) {
                try {
                    // Incluir las funciones de procesamiento de imágenes
                    require_once __DIR__ . '/../../functions/api.php';
                    
                    $resultadoImagen = procesarImagenReceta($receta['foto_principal'], $recetaId);
                    
                    if ($resultadoImagen['success']) {
                        // Actualizar la ruta de la imagen en la base de datos
                        DB::table('recetas')
                            ->where('id', $recetaId)
                            ->update(['foto_principal' => $resultadoImagen['filename']]);
                    }
                } catch (Exception $e) {
                    // Si falla el procesamiento, mantener la URL original
                    error_log("Error procesando imagen para receta {$recetaId}: " . $e->getMessage());
                }
            }
        }
    }
}
