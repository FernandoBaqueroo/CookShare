<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosCompletosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar recetas
        $recetas = [
            [
                'titulo' => 'Tortilla Española Clásica',
                'descripcion' => 'La auténtica tortilla de patatas española, cremosa por dentro y dorada por fuera',
                'tiempo_preparacion' => 15,
                'tiempo_coccion' => 20,
                'porciones' => 4,
                'dificultad' => 'Intermedio',
                'foto_principal' => 'https://images.pexels.com/photos/7625439/pexels-photo-7625439.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Pelar y cortar las patatas en láminas finas. 2. Freír las patatas en aceite abundante. 3. Batir los huevos y mezclar con las patatas. 4. Cuajar en la sartén por ambos lados.',
                'usuario_id' => 2,
                'categoria_id' => 2,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Pancakes Americanos',
                'descripcion' => 'Esponjosos pancakes perfectos para el desayuno',
                'tiempo_preparacion' => 10,
                'tiempo_coccion' => 15,
                'porciones' => 3,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.pexels.com/photos/376464/pexels-photo-376464.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Mezclar ingredientes secos. 2. Batir huevos con leche. 3. Combinar ambas mezclas. 4. Cocinar en sartén caliente hasta que estén dorados.',
                'usuario_id' => 5,
                'categoria_id' => 1,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Tarta de Chocolate',
                'descripcion' => 'Rica tarta de chocolate con ganache cremoso',
                'tiempo_preparacion' => 30,
                'tiempo_coccion' => 45,
                'porciones' => 8,
                'dificultad' => 'Difícil',
                'foto_principal' => 'https://images.pexels.com/photos/291528/pexels-photo-291528.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Preparar la masa quebrada. 2. Hacer el relleno de chocolate. 3. Hornear la base. 4. Añadir el relleno y decorar con ganache.',
                'usuario_id' => 4,
                'categoria_id' => 3,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Ensalada César',
                'descripcion' => 'Fresca ensalada con pollo, crutones y aderezo césar',
                'tiempo_preparacion' => 20,
                'tiempo_coccion' => 0,
                'porciones' => 2,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.pexels.com/photos/2097090/pexels-photo-2097090.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Lavar y cortar la lechuga. 2. Preparar los crutones. 3. Cocinar el pollo a la plancha. 4. Mezclar con el aderezo césar.',
                'usuario_id' => 3,
                'categoria_id' => 7,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Paella Valenciana',
                'descripcion' => 'La tradicional paella española con pollo y verduras',
                'tiempo_preparacion' => 20,
                'tiempo_coccion' => 40,
                'porciones' => 6,
                'dificultad' => 'Difícil',
                'foto_principal' => 'https://images.pexels.com/photos/16743489/pexels-photo-16743489.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Sofreír el pollo. 2. Añadir las verduras. 3. Incorporar el arroz y el caldo. 4. Cocinar sin remover hasta que esté listo.',
                'usuario_id' => 1,
                'categoria_id' => 2,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Gazpacho Andaluz',
                'descripcion' => 'Sopa fría perfecta para el verano',
                'tiempo_preparacion' => 15,
                'tiempo_coccion' => 0,
                'porciones' => 4,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.pexels.com/photos/5737241/pexels-photo-5737241.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Triturar todos los vegetales. 2. Añadir aceite y vinagre. 3. Salpimentar al gusto. 4. Refrigerar antes de servir.',
                'usuario_id' => 2,
                'categoria_id' => 6,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Pasta Carbonara',
                'descripcion' => 'Cremosa pasta italiana con bacon y huevo',
                'tiempo_preparacion' => 10,
                'tiempo_coccion' => 15,
                'porciones' => 4,
                'dificultad' => 'Intermedio',
                'foto_principal' => 'https://images.pexels.com/photos/4518843/pexels-photo-4518843.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Cocinar la pasta al dente. 2. Freír el bacon. 3. Batir huevos con queso. 4. Mezclar todo fuera del fuego.',
                'usuario_id' => 6,
                'categoria_id' => 8,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Brownie de Chocolate',
                'descripcion' => 'Intenso brownie con nueces',
                'tiempo_preparacion' => 15,
                'tiempo_coccion' => 30,
                'porciones' => 9,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.pexels.com/photos/887853/pexels-photo-887853.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Derretir chocolate con mantequilla. 2. Batir huevos con azúcar. 3. Mezclar con harina. 4. Hornear hasta que esté firme.',
                'usuario_id' => 4,
                'categoria_id' => 3,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Croquetas de Jamón',
                'descripcion' => 'Cremosas croquetas caseras',
                'tiempo_preparacion' => 45,
                'tiempo_coccion' => 10,
                'porciones' => 20,
                'dificultad' => 'Intermedio',
                'foto_principal' => 'https://images.pexels.com/photos/12737543/pexels-photo-12737543.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Hacer la bechamel. 2. Añadir jamón picado. 3. Enfriar y formar las croquetas. 4. Rebozar y freír.',
                'usuario_id' => 1,
                'categoria_id' => 4,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Smoothie Verde',
                'descripcion' => 'Batido saludable con espinacas y frutas',
                'tiempo_preparacion' => 5,
                'tiempo_coccion' => 0,
                'porciones' => 2,
                'dificultad' => 'Fácil',
                'foto_principal' => 'https://images.pexels.com/photos/1092730/pexels-photo-1092730.jpeg?auto=compress&cs=tinysrgb&w=1000&q=80',
                'instrucciones' => '1. Lavar las espinacas. 2. Pelar y trocear las frutas. 3. Batir todo con agua o leche vegetal. 4. Servir inmediatamente.',
                'usuario_id' => 3,
                'categoria_id' => 5,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('recetas')->insert($recetas);

        // Insertar ingredientes para las recetas
        $recetaIngredientes = [
            // Tortilla Española
            ['receta_id' => 1, 'ingrediente_id' => 1, 'cantidad' => 6, 'notas' => 'huevos grandes'],
            ['receta_id' => 1, 'ingrediente_id' => 7, 'cantidad' => 2, 'notas' => 'cebollas medianas'],
            ['receta_id' => 1, 'ingrediente_id' => 9, 'cantidad' => 150, 'notas' => 'aceite de oliva virgen'],
            ['receta_id' => 1, 'ingrediente_id' => 10, 'cantidad' => 5, 'notas' => 'sal al gusto'],
            
            // Pancakes
            ['receta_id' => 2, 'ingrediente_id' => 1, 'cantidad' => 2, 'notas' => 'huevos batidos'],
            ['receta_id' => 2, 'ingrediente_id' => 2, 'cantidad' => 200, 'notas' => 'harina común'],
            ['receta_id' => 2, 'ingrediente_id' => 4, 'cantidad' => 250, 'notas' => 'leche entera'],
            ['receta_id' => 2, 'ingrediente_id' => 3, 'cantidad' => 30, 'notas' => 'azúcar'],
            ['receta_id' => 2, 'ingrediente_id' => 5, 'cantidad' => 50, 'notas' => 'mantequilla derretida'],
            
            // Tarta de Chocolate
            ['receta_id' => 3, 'ingrediente_id' => 16, 'cantidad' => 200, 'notas' => 'chocolate negro'],
            ['receta_id' => 3, 'ingrediente_id' => 1, 'cantidad' => 4, 'notas' => 'huevos'],
            ['receta_id' => 3, 'ingrediente_id' => 3, 'cantidad' => 150, 'notas' => 'azúcar'],
            ['receta_id' => 3, 'ingrediente_id' => 5, 'cantidad' => 100, 'notas' => 'mantequilla'],
            ['receta_id' => 3, 'ingrediente_id' => 2, 'cantidad' => 100, 'notas' => 'harina'],
            
            // Ensalada César
            ['receta_id' => 4, 'ingrediente_id' => 13, 'cantidad' => 300, 'notas' => 'pechuga de pollo'],
            ['receta_id' => 4, 'ingrediente_id' => 12, 'cantidad' => 100, 'notas' => 'queso parmesano'],
            ['receta_id' => 4, 'ingrediente_id' => 9, 'cantidad' => 50, 'notas' => 'aceite de oliva'],
            
            // Paella Valenciana
            ['receta_id' => 5, 'ingrediente_id' => 14, 'cantidad' => 400, 'notas' => 'arroz bomba'],
            ['receta_id' => 5, 'ingrediente_id' => 13, 'cantidad' => 500, 'notas' => 'pollo troceado'],
            ['receta_id' => 5, 'ingrediente_id' => 6, 'cantidad' => 3, 'notas' => 'tomates maduros'],
            ['receta_id' => 5, 'ingrediente_id' => 9, 'cantidad' => 100, 'notas' => 'aceite de oliva'],
            
            // Gazpacho
            ['receta_id' => 6, 'ingrediente_id' => 6, 'cantidad' => 6, 'notas' => 'tomates maduros'],
            ['receta_id' => 6, 'ingrediente_id' => 7, 'cantidad' => 1, 'notas' => 'cebolla pequeña'],
            ['receta_id' => 6, 'ingrediente_id' => 8, 'cantidad' => 2, 'notas' => 'dientes de ajo'],
            ['receta_id' => 6, 'ingrediente_id' => 9, 'cantidad' => 80, 'notas' => 'aceite de oliva virgen'],
            
            // Pasta Carbonara
            ['receta_id' => 7, 'ingrediente_id' => 15, 'cantidad' => 400, 'notas' => 'espaguetis'],
            ['receta_id' => 7, 'ingrediente_id' => 1, 'cantidad' => 4, 'notas' => 'huevos'],
            ['receta_id' => 7, 'ingrediente_id' => 12, 'cantidad' => 100, 'notas' => 'queso pecorino'],
            ['receta_id' => 7, 'ingrediente_id' => 20, 'cantidad' => 150, 'notas' => 'panceta o bacon'],
            
            // Brownie
            ['receta_id' => 8, 'ingrediente_id' => 16, 'cantidad' => 150, 'notas' => 'chocolate negro'],
            ['receta_id' => 8, 'ingrediente_id' => 1, 'cantidad' => 3, 'notas' => 'huevos'],
            ['receta_id' => 8, 'ingrediente_id' => 3, 'cantidad' => 120, 'notas' => 'azúcar moreno'],
            ['receta_id' => 8, 'ingrediente_id' => 2, 'cantidad' => 80, 'notas' => 'harina'],
            ['receta_id' => 8, 'ingrediente_id' => 5, 'cantidad' => 80, 'notas' => 'mantequilla'],
            
            // Croquetas de Jamón
            ['receta_id' => 9, 'ingrediente_id' => 20, 'cantidad' => 200, 'notas' => 'jamón serrano picado'],
            ['receta_id' => 9, 'ingrediente_id' => 4, 'cantidad' => 500, 'notas' => 'leche entera'],
            ['receta_id' => 9, 'ingrediente_id' => 2, 'cantidad' => 80, 'notas' => 'harina'],
            ['receta_id' => 9, 'ingrediente_id' => 5, 'cantidad' => 80, 'notas' => 'mantequilla'],
            ['receta_id' => 9, 'ingrediente_id' => 1, 'cantidad' => 2, 'notas' => 'huevos para rebozar'],
            
            // Smoothie Verde
            ['receta_id' => 10, 'ingrediente_id' => 4, 'cantidad' => 200, 'notas' => 'leche de almendras'],
            ['receta_id' => 10, 'ingrediente_id' => 18, 'cantidad' => 1, 'notas' => 'limón exprimido'],
        ];

        foreach ($recetaIngredientes as $ingrediente) {
            $ingrediente['created_at'] = now();
            $ingrediente['updated_at'] = now();
            DB::table('receta_ingredientes')->insert($ingrediente);
        }

        // Insertar etiquetas para las recetas
        $recetaEtiquetas = [
            [1, 5], [1, 6], // Tortilla: Tradicional, Fácil
            [2, 1], [2, 6], // Pancakes: Rápido, Fácil
            [3, 7], [3, 3], // Tarta: Gourmet, Sin Gluten
            [4, 1], [4, 4], // César: Rápido, Bajo en Calorías
            [5, 5], [5, 7], // Paella: Tradicional, Gourmet
            [6, 2], [6, 4], [6, 1], // Gazpacho: Vegetariano, Bajo en Calorías, Rápido
            [7, 1], [7, 5], // Carbonara: Rápido, Tradicional
            [8, 6], [8, 8], // Brownie: Fácil, Económico
            [9, 5], [9, 7], // Croquetas: Tradicional, Gourmet
            [10, 2], [10, 4], [10, 1], // Smoothie: Vegetariano, Bajo en Calorías, Rápido
        ];

        foreach ($recetaEtiquetas as $etiqueta) {
            DB::table('receta_etiquetas')->insert([
                'receta_id' => $etiqueta[0],
                'etiqueta_id' => $etiqueta[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar valoraciones
        $valoraciones = [
            [1, 1, 5], [1, 3, 4], [1, 4, 5],
            [2, 2, 5], [2, 4, 4], [2, 6, 5],
            [3, 1, 5], [3, 2, 4], [3, 5, 5],
            [4, 2, 4], [4, 5, 3], [4, 6, 4],
            [5, 3, 5], [5, 4, 5], [5, 6, 4],
            [6, 1, 4], [6, 4, 5], [6, 5, 4],
            [7, 2, 5], [7, 3, 4], [7, 5, 5],
            [8, 1, 5], [8, 3, 5], [8, 6, 4],
            [9, 2, 4], [9, 5, 5], [9, 6, 4],
            [10, 1, 4], [10, 2, 3], [10, 4, 5],
        ];

        foreach ($valoraciones as $valoracion) {
            DB::table('valoraciones')->insert([
                'receta_id' => $valoracion[0],
                'usuario_id' => $valoracion[1],
                'puntuacion' => $valoracion[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar comentarios
        $comentarios = [
            [1, 1, '¡Excelente receta! La tortilla quedó perfecta, cremosa por dentro.'],
            [1, 3, 'Muy buena explicación paso a paso. La repetiré seguro.'],
            [2, 4, 'Mis hijos los adoran. Perfectos para el desayuno del domingo.'],
            [3, 2, 'Un poco complicada pero el resultado vale la pena. ¡Espectacular!'],
            [5, 6, 'La mejor paella que he probado. Gracias por compartir la receta.'],
            [6, 4, 'Perfecta para el verano. Muy refrescante y fácil de hacer.'],
            [7, 3, 'Quedó deliciosa, aunque tuve que practicar varias veces la técnica.'],
            [8, 6, 'Brownies increíbles, muy chocolateados. Los niños están encantados.'],
            [9, 5, 'Las croquetas quedaron perfectas. El truco está en la bechamel.'],
            [10, 2, 'Excelente manera de incluir verduras en la dieta. Muy sabroso.'],
        ];

        foreach ($comentarios as $comentario) {
            DB::table('comentarios')->insert([
                'receta_id' => $comentario[0],
                'usuario_id' => $comentario[1],
                'comentario' => $comentario[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar favoritos
        $favoritos = [
            [1, 3], [1, 5], [1, 7],
            [2, 1], [2, 6], [2, 8],
            [3, 2], [3, 4], [3, 10],
            [4, 1], [4, 3], [4, 9],
            [5, 2], [5, 6], [5, 7],
            [6, 1], [6, 4], [6, 8],
        ];

        foreach ($favoritos as $favorito) {
            DB::table('favoritos')->insert([
                'usuario_id' => $favorito[0],
                'receta_id' => $favorito[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
