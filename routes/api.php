<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Importar funciones helper
require_once __DIR__ . '/../functions/api.php';

/**
 * @api {get} /feed Feed de recetas
 * @apiSuccess {Object[]} data
 */
Route::middleware(['auth:sanctum', 'check.token.expiration'])->group(function () {
    Route::get('/feed', function (Request $request) {
        $usuarioId = $request->input('usuario_id');
        
        if (!$usuarioId) {
            abort(400, 'El usuario_id es requerido');
        }
        
        // Verificar que el usuario existe
        $usuarioExiste = DB::table('usuarios')->where('id', $usuarioId)->exists();
        if (!$usuarioExiste) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        }
        
        $recetas = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.tiempo_preparacion',
                'recetas.tiempo_coccion',
                'recetas.porciones',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.fecha_creacion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil',
                DB::raw('(SELECT COUNT(*) FROM favoritos WHERE favoritos.receta_id = recetas.id) as total_favoritos'),
                DB::raw('(SELECT AVG(valoraciones.puntuacion) FROM valoraciones WHERE valoraciones.receta_id = recetas.id) as promedio_valoraciones')
            ])
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->where('recetas.activa', true)
            ->where('recetas.usuario_id', '!=', $usuarioId) // Excluir recetas del usuario autenticado
            ->orderBy('recetas.fecha_creacion', 'desc')
            ->get()
            ->map(function ($receta) {
                // Construir URLs completas para las imágenes
                if ($receta->foto_principal) {
                    $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
                }
                if ($receta->foto_perfil) {
                    $receta->foto_perfil = construirUrlImagen($receta->foto_perfil, 'profiles');
                }
                
                // Obtener etiquetas de la receta
                $etiquetas = DB::table('receta_etiquetas')
                    ->select(['etiquetas.nombre', 'etiquetas.color'])
                    ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
                    ->where('receta_etiquetas.receta_id', $receta->id)
                    ->get();
                
                $receta->etiquetas = $etiquetas;
                return $receta;
            });

        return response()->json([
            'data' => $recetas
        ], 200);
    });

    /**
     * @api {get} /receta/:id Obtener receta
     * @apiParam {Number} id
     * @apiSuccess {Object} data
     */
    Route::get('/receta/{id}', function(Request $request, $id) {
        $receta = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.tiempo_preparacion',
                'recetas.tiempo_coccion',
                'recetas.porciones',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.instrucciones',
                'recetas.fecha_creacion',
                'recetas.fecha_actualizacion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil',
                'categorias.nombre as categoria_nombre'
            ])
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->join('categorias', 'recetas.categoria_id', '=', 'categorias.id')
            ->where('recetas.id', $id)
            ->where('recetas.activa', true)
            ->first();

        if (!$receta) {
            abort(560, 'No existe una receta con el ID proporcionado');
        }

        // Obtener ingredientes de la receta
        $ingredientes = DB::table('receta_ingredientes')
            ->select([
                'ingredientes.nombre',
                DB::raw("CONCAT(receta_ingredientes.cantidad, ' ', ingredientes.unidad_medida) as cantidad"),
                'receta_ingredientes.notas'
            ])
            ->join('ingredientes', 'receta_ingredientes.ingrediente_id', '=', 'ingredientes.id')
            ->where('receta_ingredientes.receta_id', $id)
            ->get();

        // Obtener etiquetas de la receta
        $etiquetas = DB::table('receta_etiquetas')
            ->select([
                'etiquetas.nombre',
                'etiquetas.color'
            ])
            ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
            ->where('receta_etiquetas.receta_id', $id)
            ->get();

        // Construir URLs completas para las imágenes
        if ($receta->foto_principal) {
            $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
        }
        if ($receta->foto_perfil) {
            $receta->foto_perfil = construirUrlImagen($receta->foto_perfil, 'profiles');
        }

        // Obtener contadores de favoritos y valoraciones
        $totalFavoritos = DB::table('favoritos')->where('receta_id', $id)->count();
        $promedioValoraciones = DB::table('valoraciones')->where('receta_id', $id)->avg('puntuacion');

        // Estructurar la respuesta
        $recetaCompleta = [
            'id' => $receta->id,
            'titulo' => $receta->titulo,
            'descripcion' => $receta->descripcion,
            'tiempo_preparacion' => $receta->tiempo_preparacion,
            'tiempo_coccion' => $receta->tiempo_coccion,
            'porciones' => $receta->porciones,
            'dificultad' => $receta->dificultad,
            'foto_principal' => $receta->foto_principal,
            'instrucciones' => $receta->instrucciones,
            'fecha_creacion' => $receta->fecha_creacion,
            'fecha_actualizacion' => $receta->fecha_actualizacion,
            'total_favoritos' => $totalFavoritos,
            'promedio_valoraciones' => round($promedioValoraciones, 1),
            'usuario' => [
                'nombre_usuario' => $receta->nombre_usuario,
                'foto_perfil' => $receta->foto_perfil
            ],
            'categoria' => [
                'nombre' => $receta->categoria_nombre
            ],
            'ingredientes' => $ingredientes,
            'etiquetas' => $etiquetas
        ];

        return response()->json([
            'data' => $recetaCompleta
        ], 200);
    });

    /**
     * @api {get} /ingredientes Ingredientes de receta
     * @apiParam {Number} receta_id
     * @apiSuccess {Object[]} data
     */
    Route::get('/ingredientes', function(Request $request) {
        $recetaId = $request->input('receta_id');
        if (!$recetaId) {
            abort(400, 'El receta_id es requerido');
        }

        $recetaExiste = DB::table('recetas')
            ->where('id', $recetaId)
            ->exists();

        if (!$recetaExiste) {
            abort(560, 'No existe una receta con el ID proporcionado');
        }

        $ingredientes = DB::table('receta_ingredientes')
            ->select([
                'ingredientes.nombre',
                DB::raw("CONCAT(receta_ingredientes.cantidad, ' ', ingredientes.unidad_medida) as cantidad"),
                'receta_ingredientes.notas'
            ])
            ->join('ingredientes', 'receta_ingredientes.ingrediente_id', '=', 'ingredientes.id')
            ->where('receta_ingredientes.receta_id', $recetaId)
            ->get();

        if ($ingredientes->isEmpty()) {
            abort(561, 'La receta no tiene ingredientes registrados');
        }

        return response()->json([
            'data' => $ingredientes
        ], 200);
    });

    /**
     * @api {get} /favoritos Favoritos de usuario
     * @apiParam {Number} usuario_id
     * @apiSuccess {Object[]} data
     */
    Route::get('/favoritos', function(Request $request) {
        $usuarioId = $request->input('usuario_id');
        if (!$usuarioId) {
            abort(400, 'El usuario_id es requerido');
        }

        $usuarioExiste = DB::table('usuarios')
            ->where('id', $usuarioId)
            ->exists();

        if (!$usuarioExiste) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        }

        $favoritos = DB::table('favoritos')
            ->select([
                'favoritos.id',
                'favoritos.fecha_favorito',
                'recetas.id as receta_id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.dificultad',
                'recetas.foto_principal',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('recetas', 'favoritos.receta_id', '=', 'recetas.id')
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->where('favoritos.usuario_id', $usuarioId)
            ->where('recetas.activa', true)
            ->orderBy('favoritos.fecha_favorito', 'desc')
            ->get()
            ->map(function ($item) {
                // Obtener etiquetas de la receta
                $etiquetas = DB::table('receta_etiquetas')
                    ->select(['etiquetas.nombre', 'etiquetas.color'])
                    ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
                    ->where('receta_etiquetas.receta_id', $item->receta_id)
                    ->get();
                
                return [
                    'id' => $item->id,
                    'fecha_favorito' => $item->fecha_favorito,
                    'receta' => [
                        'id' => $item->receta_id,
                        'titulo' => $item->titulo,
                        'descripcion' => $item->descripcion,
                        'dificultad' => $item->dificultad,
                        'foto_principal' => construirUrlImagen($item->foto_principal, 'posts'),
                        'nombre_usuario' => $item->nombre_usuario,
                        'foto_perfil' => construirUrlImagen($item->foto_perfil, 'profiles'),
                        'etiquetas' => $etiquetas
                    ]
                ];
            });

        if ($favoritos->isEmpty()) {
            abort(562, 'El usuario no tiene recetas favoritas');
        }

        return response()->json([
            'data' => $favoritos
        ], 200);
    });

    /**
     * @api {get} /comentarios Comentarios de receta
     * @apiParam {Number} receta_id
     * @apiSuccess {Object[]} data
     */
    Route::get('/comentarios', function(Request $request) {
        $recetaId = $request->input('receta_id');
        if (!$recetaId) {
            abort(400, 'El receta_id es requerido');
        }

        $recetaExiste = DB::table('recetas')
            ->where('id', $recetaId)
            ->exists();

        if (!$recetaExiste) {
            abort(560, 'No existe una receta con el ID proporcionado');
        }

        $comentarios = DB::table('comentarios')
            ->select([
                'comentarios.comentario',
                'comentarios.fecha_comentario',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('usuarios', 'comentarios.usuario_id', '=', 'usuarios.id')
            ->where('comentarios.receta_id', $recetaId)
            ->orderBy('comentarios.fecha_comentario', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'comentario' => $item->comentario,
                    'fecha_comentario' => $item->fecha_comentario,
                    'usuario' => [
                        'nombre_usuario' => $item->nombre_usuario,
                        'foto_perfil' => construirUrlImagen($item->foto_perfil, 'profiles')
                    ]
                ];
            });

        return response()->json([
            'data' => $comentarios
        ], 200);
    });

    /**
     * @api {get} /valoraciones Valoraciones de receta
     * @apiParam {Number} receta_id
     * @apiSuccess {Object[]} data
     */
    Route::get('/valoraciones', function(Request $request) {
        $recetaId = $request->input('receta_id');
        if (!$recetaId) {
            abort(400, 'El receta_id es requerido');
        }

        $recetaExiste = DB::table('recetas')
            ->where('id', $recetaId)
            ->exists();

        if (!$recetaExiste) {
            abort(560, 'No existe una receta con el ID proporcionado');
        }

        $valoraciones = DB::table('valoraciones')
            ->select([
                'valoraciones.id',
                'recetas.titulo',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil',
                'valoraciones.puntuacion',
                'valoraciones.fecha_valoracion'
            ])
            ->join('recetas', 'valoraciones.receta_id', '=', 'recetas.id')
            ->join('usuarios', 'valoraciones.usuario_id', '=', 'usuarios.id')
            ->where('valoraciones.receta_id', $recetaId)
            ->orderBy('valoraciones.fecha_valoracion', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'titulo' => $item->titulo,
                    'usuario' => [
                        'nombre_usuario' => $item->nombre_usuario,
                        'foto_perfil' => construirUrlImagen($item->foto_perfil, 'profiles')
                    ],
                    'valoracion' => [
                        'id' => $item->id,
                        'puntuacion' => $item->puntuacion,
                        'fecha_valoracion' => $item->fecha_valoracion
                    ]
                ];
            });

        return response()->json([
            'data' => $valoraciones
        ], 200);
    });

    //! Ruta para crear una receta
    Route::post('/post', function (Request $request) {
        $camposRequeridos = [
            'titulo' => 'El título es requerido',
            'descripcion' => 'La descripción es requerida',
            'tiempo_preparacion' => 'El tiempo de preparación es requerido',
            'tiempo_coccion' => 'El tiempo de cocción es requerido',
            'porciones' => 'El número de porciones es requerido',
            'dificultad' => 'La dificultad es requerida',
            'foto_principal' => 'La foto principal es requerida',
            'instrucciones' => 'Las instrucciones son requeridas',
            'usuario_id' => 'El usuario_id es requerido',
            'categoria_id' => 'El categoria_id es requerido',
            'ingredientes' => 'Los ingredientes son requeridos',
            'etiquetas' => 'Las etiquetas son requeridas'
        ];

        foreach ($camposRequeridos as $campo => $mensaje) {
            if (!$request->input($campo)) {
                abort(400, $mensaje);
            }
        }

        $usuario_id = $request->input('usuario_id');
        $categoria_id = $request->input('categoria_id');
        $ingredientes = $request->input('ingredientes');
        $etiquetas = $request->input('etiquetas');

        // Verificar que el usuario y categoría existen
        if (!DB::table('usuarios')->where('id', $usuario_id)->exists()) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        }

        if (!DB::table('categorias')->where('id', $categoria_id)->exists()) {
            abort(566, 'No existe una categoría con el ID proporcionado');
        }

        // Validar ingredientes usando función helper
        $validacionIngredientes = validarIngredientes($ingredientes);
        if (!$validacionIngredientes['success']) {
            abort(400, $validacionIngredientes['error']);
        }

        // Validar etiquetas usando función helper
        $validacionEtiquetas = validarEtiquetas($etiquetas);
        if (!$validacionEtiquetas['success']) {
            abort(400, $validacionEtiquetas['error']);
        }

        // Comprobar si ha publicado en las últimas 24 horas
        $fechaLimite = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $haPublicado = DB::table('recetas')
            ->where('usuario_id', $usuario_id)
            ->where('fecha_creacion', '>=', $fechaLimite)
            ->exists();

        /*if ($haPublicado) {
            abort(563, 'Ya has publicado una receta en las últimas 24 horas');
        } else {*/

        // Insertar la receta
        $recetaId = DB::table('recetas')->insertGetId([
            'titulo' => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'tiempo_preparacion' => $request->input('tiempo_preparacion'),
            'tiempo_coccion' => $request->input('tiempo_coccion'),
            'porciones' => $request->input('porciones'),
            'dificultad' => $request->input('dificultad'),
            'foto_principal' => $request->input('foto_principal'),
            'instrucciones' => $request->input('instrucciones'),
            'usuario_id' => $usuario_id,
            'categoria_id' => $categoria_id,
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
            'activa' => true
        ]);

        // Procesar y guardar imagen usando la función helper
        $foto_principal = $request->input('foto_principal');
        
        if ($foto_principal) {
            $resultadoImagen = procesarImagenReceta($foto_principal, $recetaId);
            
            if (!$resultadoImagen['success']) {
                // Si falla el procesamiento de imagen, eliminar la receta creada
                DB::table('recetas')->where('id', $recetaId)->delete();
                abort(400, $resultadoImagen['error']);
            }
            
            // Actualizar la ruta de la imagen en la base de datos
            DB::table('recetas')
                ->where('id', $recetaId)
                ->update(['foto_principal' => $resultadoImagen['filename']]);
        }

        // Insertar ingredientes de la receta
        foreach ($ingredientes as $ingrediente) {
            DB::table('receta_ingredientes')->insert([
                'receta_id' => $recetaId,
                'ingrediente_id' => $ingrediente['ingrediente_id'],
                'cantidad' => $ingrediente['cantidad'],
                'notas' => $ingrediente['notas'] ?? null
            ]);
        }

        // Insertar etiquetas de la receta
        foreach ($etiquetas as $etiqueta_id) {
            DB::table('receta_etiquetas')->insert([
                'receta_id' => $recetaId,
                'etiqueta_id' => $etiqueta_id
            ]);
        }

        return response()->json([
            'message' => 'Receta creada exitosamente',
            'data' => [
                'id' => $recetaId,
                'titulo' => $request->input('titulo'),
                'ingredientes_count' => count($ingredientes),
                'etiquetas_count' => count($etiquetas)
            ]
        ], 201);
        //}
    });

    //! Ruta subida imagen de perfil
    Route::post('/profile-image', function(Request $request) {
        $usuario_id = $request->input('usuario_id');
        $foto_perfil = $request->input('foto_perfil');



        if (!$usuario_id || !$foto_perfil) {
            abort(400, 'El usuario_id y la foto_perfil son requeridos');
        }
        
        if (!DB::table('usuarios')->where('id', $usuario_id)->exists()) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        }

        // Procesar y guardar imagen de perfil.
        $resultadoImagen = procesarImagenPerfil($foto_perfil, $usuario_id);
        

        
        if (!$resultadoImagen['success']) {
            abort(400, $resultadoImagen['error']);
        }

        // Actualizar la ruta de la imagen en la base de datos
        DB::table('usuarios')
            ->where('id', $usuario_id)
            ->update(['foto_perfil' => $resultadoImagen['filename']]);

        return response()->json([
            'message' => 'Imagen de perfil actualizada exitosamente',
            'data' => [
                'usuario_id' => $usuario_id,
                'foto_url' => $resultadoImagen['url']
            ]
        ], 200);
    });



    //! Ruta para subir una valoracion
    Route::post('/valoracion', function(Request $request) {
        $receta_id = $request->input('receta_id');
        $usuario_id = $request->input('usuario_id');
        $puntuacion = $request->input('puntuacion');

        // Validaciones encadenadas con else if
        if (!$receta_id || !$usuario_id || !$puntuacion) {
            abort(400, 'El receta_id, usuario_id y puntuación son requeridos');
        } else if (!validarRecetaExiste($receta_id)) {
            abort(560, 'No existe una receta con el ID proporcionado');
        } else if (!validarUsuarioExiste($usuario_id)) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        } else if ($puntuacion < 1 || $puntuacion > 5) {
            abort(400, 'La puntuación debe estar entre 1 y 5');
        } else {
            $validacion = validarNoValorarPropiaReceta($receta_id, $usuario_id);
            if (!$validacion['success']) {
                abort(400, $validacion['error']);
            }
            
            $validacion = validarNoValoracionPrevia($receta_id, $usuario_id);
            if (!$validacion['success']) {
                abort(400, $validacion['error']);
            }
        }

        // Insertar la valoración en BBDD
        $valoracionId = DB::table('valoraciones')->insertGetId([
            'receta_id' => $receta_id,
            'usuario_id' => $usuario_id,
            'puntuacion' => $puntuacion,
            'fecha_valoracion' => now()
        ]);

        return response()->json([
            'message' => 'Valoración creada exitosamente',
            'data' => [
                'valoracion_id' => $valoracionId,
            ]
        ], 201);
    });

    //! Ruta para crear un comentario independiente
    Route::post('/comentario', function(Request $request) {
        $receta_id = $request->input('receta_id');
        $usuario_id = $request->input('usuario_id');
        $comentario = $request->input('comentario');

        // Validaciones
        if (!$receta_id || !$usuario_id || !$comentario) {
            abort(400, 'El receta_id, usuario_id y comentario son requeridos');
        } else if (!validarRecetaExiste($receta_id)) {
            abort(560, 'No existe una receta con el ID proporcionado');
        } else if (!validarUsuarioExiste($usuario_id)) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        } else if (strlen(trim($comentario)) == 0) {
            abort(400, 'El comentario no puede estar vacío');
        } else if (strlen($comentario) > 500) {
            abort(400, 'El comentario no puede tener más de 500 caracteres');
        }

        // Insertar el comentario
        $comentarioId = DB::table('comentarios')->insertGetId([
            'receta_id' => $receta_id,
            'usuario_id' => $usuario_id,
            'comentario' => $comentario,
            'fecha_comentario' => now(),
            'activo' => true
        ]);

        return response()->json([
            'message' => 'Comentario creado exitosamente',
            'data' => [
                'comentario_id' => $comentarioId
            ]
        ], 201);
    });

    //! Ruta para agregar una receta a favoritos
    Route::post('/favorito', function(Request $request) {
        $receta_id = $request->input('receta_id');
        $usuario_id = $request->input('usuario_id');

        // Validaciones
        if (!$receta_id || !$usuario_id) {
            abort(400, 'El receta_id y usuario_id son requeridos');
        } else if (!validarRecetaExiste($receta_id)) {
            abort(560, 'No existe una receta con el ID proporcionado');
        } else if (!validarUsuarioExiste($usuario_id)) {
            abort(565, 'No existe un usuario con el ID proporcionado');
        } else {
            $validacion = validarNoFavoritoPrevio($receta_id, $usuario_id);
            if (!$validacion['success']) {
                abort(400, $validacion['error']);
            }
        }

        // Insertar el favorito en la base de datos
        $favoritoId = DB::table('favoritos')->insertGetId([
            'receta_id' => $receta_id,
            'usuario_id' => $usuario_id,
            'fecha_favorito' => now()
        ]);

        return response()->json([
            'message' => 'Receta añadida a favoritos exitosamente',
            'data' => [
                'favorito_id' => $favoritoId
            ]
        ], 201);
    });

    //! Ruta para editar una valoración
    Route::put('/valoracion/{id}', function(Request $request, $id) {
        $puntuacion = $request->input('puntuacion');

        if (!$puntuacion) {
            abort(400, 'La puntuación es requerida');
        }

        if ($puntuacion < 1 || $puntuacion > 5) {
            abort(400, 'La puntuación debe estar entre 1 y 5');
        }

        // Verificar que la valoración existe
        $valoracion = DB::table('valoraciones')->where('id', $id)->first();
        if (!$valoracion) {
            abort(404, 'Valoración no encontrada');
        }

        // Verificar que el usuario no está valorando su propia receta
        $autorReceta = DB::table('recetas')->where('id', $valoracion->receta_id)->value('usuario_id');
        if ($autorReceta == $valoracion->usuario_id) {
            abort(400, 'No puedes valorar tu propia receta');
        }

        // Actualizar la valoración
        DB::table('valoraciones')
            ->where('id', $id)
            ->update([
                'puntuacion' => $puntuacion,
                'fecha_valoracion' => now()
            ]);

        return response()->json([
            'message' => 'Valoración actualizada exitosamente',
            'data' => [
                'valoracion_id' => $id,
                'puntuacion' => $puntuacion
            ]
        ], 200);
    });

    //! Ruta para editar una receta
    Route::put('/receta/{id}', function(Request $request, $id) {
        $camposEditables = [
            'titulo' => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'tiempo_preparacion' => $request->input('tiempo_preparacion'),
            'tiempo_coccion' => $request->input('tiempo_coccion'),
            'porciones' => $request->input('porciones'),
            'dificultad' => $request->input('dificultad'),
            'instrucciones' => $request->input('instrucciones'),
            'foto_principal' => $request->input('foto_principal'),
            'categoria_id' => $request->input('categoria_id')
        ];

        // Verificar que la receta existe
        $receta = DB::table('recetas')->where('id', $id)->first();
        if (!$receta) {
            abort(404, 'Receta no encontrada');
        }

        // Verificar que al menos un campo se está actualizando
        $camposActualizados = array_filter($camposEditables, function($valor) {
            return $valor !== null;
        });

        if (empty($camposActualizados)) {
            abort(400, 'Debes proporcionar al menos un campo para actualizar');
        }

        // Validar campos obligatorios si se proporcionan
        if (isset($camposEditables['titulo']) && empty(trim($camposEditables['titulo']))) {
            abort(400, 'El título no puede estar vacío');
        }

        if (isset($camposEditables['descripcion']) && empty(trim($camposEditables['descripcion']))) {
            abort(400, 'La descripción no puede estar vacía');
        }

        if (isset($camposEditables['tiempo_preparacion']) && $camposEditables['tiempo_preparacion'] <= 0) {
            abort(400, 'El tiempo de preparación debe ser mayor a 0');
        }

        if (isset($camposEditables['tiempo_coccion']) && $camposEditables['tiempo_coccion'] <= 0) {
            abort(400, 'El tiempo de cocción debe ser mayor a 0');
        }

        if (isset($camposEditables['porciones']) && $camposEditables['porciones'] <= 0) {
            abort(400, 'El número de porciones debe ser mayor a 0');
        }

        if (isset($camposEditables['dificultad']) && !in_array($camposEditables['dificultad'], ['Fácil', 'Intermedio', 'Difícil'])) {
            abort(400, 'La dificultad debe ser: Fácil, Intermedio o Difícil');
        }

        // Validar categoría si se proporciona
        if (isset($camposEditables['categoria_id'])) {
            if (!DB::table('categorias')->where('id', $camposEditables['categoria_id'])->exists()) {
                abort(566, 'No existe una categoría con el ID proporcionado');
            }
        }

        // Procesar imagen si se proporciona
        if (isset($camposEditables['foto_principal']) && $camposEditables['foto_principal']) {
            $resultadoImagen = procesarImagenReceta($camposEditables['foto_principal'], $id);
            
            if (!$resultadoImagen['success']) {
                abort(400, $resultadoImagen['error']);
            }
            
            $camposActualizados['foto_principal'] = $resultadoImagen['filename'];
        }

        // Añadir fecha de actualización
        $camposActualizados['fecha_actualizacion'] = now();

        // Actualizar la receta usando solo los campos actualizados
        DB::table('recetas')
            ->where('id', $id)
            ->update($camposActualizados);

        // Procesar ingredientes si se proporcionan
        $ingredientes = $request->input('ingredientes');
        if (is_array($ingredientes)) {
            // Validar ingredientes usando función helper
            $validacionIngredientes = validarIngredientes($ingredientes);
            if (!$validacionIngredientes['success']) {
                abort(400, $validacionIngredientes['error']);
            }
            
            // Eliminar ingredientes existentes
            DB::table('receta_ingredientes')->where('receta_id', $id)->delete();
            
            // Insertar nuevos ingredientes
            foreach ($ingredientes as $ingrediente) {
                DB::table('receta_ingredientes')->insert([
                    'receta_id' => $id,
                    'ingrediente_id' => $ingrediente['ingrediente_id'],
                    'cantidad' => $ingrediente['cantidad'],
                    'notas' => $ingrediente['notas'] ?? null
                ]);
            }
        }

        // Procesar etiquetas si se proporcionan
        $etiquetas = $request->input('etiquetas');
        if (is_array($etiquetas)) {
            // Validar etiquetas usando función helper
            $validacionEtiquetas = validarEtiquetas($etiquetas);
            if (!$validacionEtiquetas['success']) {
                abort(400, $validacionEtiquetas['error']);
            }
            
            // Eliminar etiquetas existentes
            DB::table('receta_etiquetas')->where('receta_id', $id)->delete();
            
            // Insertar nuevas etiquetas
            foreach ($etiquetas as $etiqueta_id) {
                DB::table('receta_etiquetas')->insert([
                    'receta_id' => $id,
                    'etiqueta_id' => $etiqueta_id
                ]);
            }
        }

        return response()->json([
            'message' => 'Receta actualizada exitosamente',
            'data' => [
                'receta_id' => $id,
                'campos_actualizados' => array_keys($camposActualizados),
                'ingredientes_actualizados' => is_array($ingredientes),
                'etiquetas_actualizadas' => is_array($etiquetas)
            ]
        ], 200);
    });

    //! Ruta para editar perfil de usuario
    Route::put('/usuario/{id}', function(Request $request, $id) {
        $camposEditables = [
            'nombre_usuario' => $request->input('nombre_usuario'),
            'email' => $request->input('email'),
            'foto_perfil' => $request->input('foto_perfil'),
            'bio' => $request->input('bio')
        ];

        // Verificar que el usuario existe
        if (!validarUsuarioExiste($id)) {
            abort(404, 'Usuario no encontrado');
        }

        // Verificar que al menos un campo se está actualizando
        $camposActualizados = array_filter($camposEditables, function($valor) {
            return $valor !== null;
        });

        if (empty($camposActualizados)) {
            abort(400, 'Debes proporcionar al menos un campo para actualizar');
        }

        // Validar nombre de usuario si se proporciona
        if (isset($camposEditables['nombre_usuario'])) {
            if (empty(trim($camposEditables['nombre_usuario']))) {
                abort(400, 'El nombre de usuario no puede estar vacío');
            }

            $validacion = validarNombreUsuarioUnico($camposEditables['nombre_usuario'], $id);
            if (!$validacion['success']) {
                abort(400, $validacion['error']);
            }
        }

        // Validar email si se proporciona
        if (isset($camposEditables['email'])) {
            if (empty(trim($camposEditables['email']))) {
                abort(400, 'El email no puede estar vacío');
            }

            if (!filter_var($camposEditables['email'], FILTER_VALIDATE_EMAIL)) {
                abort(400, 'El formato del email no es válido');
            }

            $validacion = validarEmailUnico($camposEditables['email'], $id);
            if (!$validacion['success']) {
                abort(400, $validacion['error']);
            }
        }

        // Procesar imagen de perfil si se proporciona
        if (isset($camposEditables['foto_perfil']) && $camposEditables['foto_perfil']) {
            $resultadoImagen = procesarImagenPerfil($camposEditables['foto_perfil'], $id);
            
            if (!$resultadoImagen['success']) {
                abort(400, $resultadoImagen['error']);
            }
            
            $camposActualizados['foto_perfil'] = $resultadoImagen['filename'];
        }

        // Actualizar el usuario
        DB::table('usuarios')
            ->where('id', $id)
            ->update($camposActualizados);

        return response()->json([
            'message' => 'Perfil de usuario actualizado exitosamente',
            'data' => [
                'usuario_id' => $id,
                'campos_actualizados' => array_keys($camposActualizados)
            ]
        ], 200);
    });

    //! Ruta para servir imágenes de perfil
    Route::get('/images/profiles/{filename}', function($filename) {
        $baseImagePath = env('APP_IMAGES_PATH');
        $imagePath = $baseImagePath . '/profiles/' . $filename;
        
        if (!file_exists($imagePath)) {
            abort(404, 'Imagen de perfil no encontrada');
        }
        
        return response()->file($imagePath);
    });

    /**
     * @api {get} /ingredientes/lista Obtener lista de ingredientes
     * @apiName GetIngredientesLista
     * @apiGroup Ingredientes
     * @apiParam {String} [busqueda] Término de búsqueda para filtrar ingredientes
     * @apiSuccess {Object[]} data Lista de ingredientes
     * @apiSuccess {Number} data.id ID del ingrediente
     * @apiSuccess {String} data.nombre Nombre del ingrediente
     * @apiSuccess {String} data.unidad_medida Unidad de medida del ingrediente
     */
    Route::get('/ingredientes/lista', function(Request $request) {
        $busqueda = $request->input('busqueda');
        
        $query = DB::table('ingredientes')->select(['id', 'nombre', 'unidad_medida']);
        
        if ($busqueda) {
            $query->where('nombre', 'like', '%' . $busqueda . '%');
        }
        
        $ingredientes = $query->orderBy('nombre')->get();
        
        return response()->json([
            'data' => $ingredientes
        ], 200);
    })->middleware('auth:sanctum');

    /**
     * @api {post} /ingredientes/crear Crear un nuevo ingrediente
     * @apiName CreateIngrediente
     * @apiGroup Ingredientes
     * @apiParam {String} nombre Nombre del ingrediente
     * @apiParam {String} unidad_medida Unidad de medida del ingrediente
     * @apiSuccess {Object} data Datos del ingrediente creado
     * @apiSuccess {Number} data.id ID del ingrediente creado
     * @apiSuccess {String} data.nombre Nombre del ingrediente
     * @apiSuccess {String} data.unidad_medida Unidad de medida
     */
    Route::post('/ingredientes/crear', function(Request $request) {
        $nombre = $request->input('nombre');
        $unidad_medida = $request->input('unidad_medida');
        
        if (!$nombre || !$unidad_medida) {
            abort(400, 'El nombre y la unidad de medida son requeridos');
        }
        
        if (empty(trim($nombre))) {
            abort(400, 'El nombre del ingrediente no puede estar vacío');
        }
        
        // Verificar que no existe un ingrediente con el mismo nombre
        if (DB::table('ingredientes')->where('nombre', trim($nombre))->exists()) {
            abort(400, 'Ya existe un ingrediente con ese nombre');
        }
        
        $ingredienteId = DB::table('ingredientes')->insertGetId([
            'nombre' => trim($nombre),
            'unidad_medida' => $unidad_medida
        ]);
        
        return response()->json([
            'message' => 'Ingrediente creado exitosamente',
            'data' => [
                'id' => $ingredienteId,
                'nombre' => trim($nombre),
                'unidad_medida' => $unidad_medida
            ]
        ], 201);
    })->middleware('auth:sanctum');

    /**
     * @api {get} /etiquetas/lista Obtener lista de etiquetas
     * @apiName GetEtiquetasLista
     * @apiGroup Etiquetas
     * @apiParam {String} [busqueda] Término de búsqueda para filtrar etiquetas
     * @apiSuccess {Object[]} data Lista de etiquetas
     * @apiSuccess {Number} data.id ID de la etiqueta
     * @apiSuccess {String} data.nombre Nombre de la etiqueta
     * @apiSuccess {String} data.color Color de la etiqueta (hexadecimal)
     */
    Route::get('/etiquetas/lista', function(Request $request) {
        $busqueda = $request->input('busqueda');
        
        $query = DB::table('etiquetas')->select(['id', 'nombre', 'color']);
        
        if ($busqueda) {
            $query->where('nombre', 'like', '%' . $busqueda . '%');
        }
        
        $etiquetas = $query->orderBy('nombre')->get();
        
        return response()->json([
            'data' => $etiquetas
        ], 200);
    });

    /**
     * @api {get} /categorias/lista Obtener lista de categorías
     * @apiName GetCategoriasLista
     * @apiGroup Categorias
     * @apiSuccess {Object[]} data Lista de categorías
     * @apiSuccess {Number} data.id ID de la categoría
     * @apiSuccess {String} data.nombre Nombre de la categoría
     */
    Route::get('/categorias/lista', function(Request $request) {
        $categorias = DB::table('categorias')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'data' => $categorias
        ], 200);
    });

    /**
     * @api {delete} /receta/:id Eliminar una receta (marcar como inactiva)
     * @apiName DeleteReceta
     * @apiGroup Recetas
     * @apiParam {Number} id ID de la receta
     * @apiSuccess {String} message Mensaje de confirmación
     * @apiSuccess {Object} data Datos de la operación
     * @apiSuccess {Number} data.receta_id ID de la receta eliminada
     */
    Route::delete('/receta/{id}', function(Request $request, $id) {
        // Verificar que la receta existe
        $receta = DB::table('recetas')->where('id', $id)->first();
        if (!$receta) {
            abort(404, 'Receta no encontrada');
        }

        // Marcar la receta como inactiva en lugar de eliminarla
        DB::table('recetas')
            ->where('id', $id)
            ->update([
                'activa' => false,
                'fecha_actualizacion' => now()
            ]);

        return response()->json([
            'message' => 'Receta eliminada exitosamente',
            'data' => [
                'receta_id' => $id
            ]
        ], 200);
    });

    /**
     * @api {get} /recetas/buscar Buscar recetas por etiquetas
     * @apiName BuscarRecetas
     * @apiGroup Recetas
     * @apiParam {Number[]} etiquetas_ids Array de IDs de etiquetas para filtrar
     * @apiParam {Number} [usuario_id] ID del usuario que realiza la búsqueda (opcional)
     * @apiSuccess {Object[]} data Lista de recetas que coinciden con las etiquetas
     * @apiSuccess {Number} data.id ID de la receta
     * @apiSuccess {String} data.titulo Título de la receta
     * @apiSuccess {String} data.descripcion Descripción de la receta
     * @apiSuccess {String} data.dificultad Nivel de dificultad
     * @apiSuccess {String} data.foto_principal URL de la foto principal
     * @apiSuccess {String} data.fecha_creacion Fecha de creación
     * @apiSuccess {Object} data.usuario Datos del usuario que creó la receta
     * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
     * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
     * @apiSuccess {Object[]} data.etiquetas Lista de etiquetas de la receta
     */
    Route::get('/recetas/buscar', function(Request $request) {
        $etiquetasIds = $request->input('etiquetas_ids');
        $usuarioId = $request->input('usuario_id');
        
        if (!$etiquetasIds || !is_array($etiquetasIds) || empty($etiquetasIds)) {
            abort(400, 'Debes proporcionar al menos una etiqueta para buscar');
        }
        
        // Verificar que todas las etiquetas existen
        foreach ($etiquetasIds as $etiquetaId) {
            if (!DB::table('etiquetas')->where('id', $etiquetaId)->exists()) {
                abort(568, 'No existe una etiqueta con el ID proporcionado: ' . $etiquetaId);
            }
        }
        
        // Buscar recetas que tengan al menos una de las etiquetas especificadas
        $recetas = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.fecha_creacion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->join('receta_etiquetas', 'recetas.id', '=', 'receta_etiquetas.receta_id')
            ->whereIn('receta_etiquetas.etiqueta_id', $etiquetasIds)
            ->where('recetas.activa', true);
        
        // Excluir recetas del usuario si se proporciona usuario_id
        if ($usuarioId) {
            $recetas->where('recetas.usuario_id', '!=', $usuarioId);
        }
        
        $recetas = $recetas->distinct()
            ->orderBy('recetas.fecha_creacion', 'desc')
            ->get()
            ->map(function ($receta) {
                // Construir URLs completas para las imágenes
                if ($receta->foto_principal) {
                    $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
                }
                if ($receta->foto_perfil) {
                    $receta->foto_perfil = construirUrlImagen($receta->foto_perfil, 'profiles');
                }
                
                // Obtener etiquetas de la receta
                $etiquetas = DB::table('receta_etiquetas')
                    ->select(['etiquetas.nombre', 'etiquetas.color'])
                    ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
                    ->where('receta_etiquetas.receta_id', $receta->id)
                    ->get();
                
                $receta->etiquetas = $etiquetas;
                return $receta;
            });
        
        if ($recetas->isEmpty()) {
            abort(569, 'No se encontraron recetas con las etiquetas especificadas');
        }
        
        return response()->json([
            'data' => $recetas
        ], 200);
    });

    // --- ENDPOINTS ADICIONALES ---

    /**
     * @api {post} /favorito Añadir favorito
     * @apiName AddFavorito
     * @apiGroup Favoritos
     * @apiParam {Number} receta_id ID de la receta
     * @apiParam {Number} usuario_id ID del usuario
     * @apiSuccess {String} message Mensaje de confirmación
     * @apiSuccess {Object} data Datos del favorito creado
     * @apiSuccess {Number} data.favorito_id ID del favorito creado
     */
    Route::post('/favorito', function(Request $request) {
        $validated = $request->validate([
            'receta_id' => 'required|integer|exists:recetas,id',
            'usuario_id' => 'required|integer|exists:usuarios,id'
        ]);

        // Verificar que la receta existe y está activa
        $receta = DB::table('recetas')
            ->where('id', $validated['receta_id'])
            ->where('activa', true)
            ->first();
        
        if (!$receta) {
            abort(404, 'Receta no encontrada o no está activa');
        }

        // Verificar que el usuario existe
        $usuario = DB::table('usuarios')
            ->where('id', $validated['usuario_id'])
            ->first();
        
        if (!$usuario) {
            abort(404, 'Usuario no encontrado');
        }

        // Verificar que no existe ya un favorito para esta receta y usuario
        $favoritoExistente = DB::table('favoritos')
            ->where('receta_id', $validated['receta_id'])
            ->where('usuario_id', $validated['usuario_id'])
            ->first();
        
        if ($favoritoExistente) {
            abort(400, 'Esta receta ya está en tus favoritos');
        }

        // Crear el favorito
        $favoritoId = DB::table('favoritos')->insertGetId([
            'receta_id' => $validated['receta_id'],
            'usuario_id' => $validated['usuario_id'],
            'fecha_favorito' => now()
        ]);

        return response()->json([
            'message' => 'Receta añadida a favoritos exitosamente',
            'data' => [
                'favorito_id' => $favoritoId,
                'receta_id' => $validated['receta_id'],
                'usuario_id' => $validated['usuario_id']
            ]
        ], 201);
    });

    /**
     * @api {get} /favoritos Obtener favoritos de un usuario
     * @apiName GetFavoritos
     * @apiGroup Favoritos
     * @apiParam {Number} usuario_id ID del usuario
     * @apiSuccess {Object[]} data Lista de favoritos del usuario
     */
    Route::get('/favoritos', function(Request $request) {
        $usuarioId = $request->input('usuario_id');
        
        if (!$usuarioId) {
            abort(400, 'El usuario_id es requerido');
        }

        // Verificar que el usuario existe
        $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
        if (!$usuario) {
            abort(404, 'Usuario no encontrado');
        }

        $favoritos = DB::table('favoritos')
            ->select([
                'favoritos.id',
                'favoritos.fecha_favorito',
                'recetas.id as receta_id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.fecha_creacion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('recetas', 'favoritos.receta_id', '=', 'recetas.id')
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->where('favoritos.usuario_id', $usuarioId)
            ->where('recetas.activa', true)
            ->orderBy('favoritos.fecha_favorito', 'desc')
            ->get()
            ->map(function ($favorito) {
                // Construir URLs completas para las imágenes
                if ($favorito->foto_principal) {
                    $favorito->foto_principal = construirUrlImagen($favorito->foto_principal, 'posts');
                }
                if ($favorito->foto_perfil) {
                    $favorito->foto_perfil = construirUrlImagen($favorito->foto_perfil, 'profiles');
                }
                
                // Obtener etiquetas de la receta
                $etiquetas = DB::table('receta_etiquetas')
                    ->select(['etiquetas.nombre', 'etiquetas.color'])
                    ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
                    ->where('receta_etiquetas.receta_id', $favorito->receta_id)
                    ->get();
                
                // Estructurar la respuesta
                return [
                    'id' => $favorito->id,
                    'fecha_favorito' => $favorito->fecha_favorito,
                    'receta' => [
                        'id' => $favorito->receta_id,
                        'titulo' => $favorito->titulo,
                        'descripcion' => $favorito->descripcion,
                        'dificultad' => $favorito->dificultad,
                        'foto_principal' => $favorito->foto_principal,
                        'fecha_creacion' => $favorito->fecha_creacion,
                        'nombre_usuario' => $favorito->nombre_usuario,
                        'foto_perfil' => $favorito->foto_perfil,
                        'etiquetas' => $etiquetas
                    ]
                ];
            });

        return response()->json([
            'data' => $favoritos
        ], 200);
    });

    /**
     * @api {delete} /favorito/{id} Eliminar favorito
     * @apiName DeleteFavorito
     * @apiGroup Favoritos
     * @apiParam {Number} id ID del favorito
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::delete('/favorito/{id}', function($id) {
        $favorito = DB::table('favoritos')->where('id', $id)->first();
        if (!$favorito) {
            abort(404, 'Favorito no encontrado');
        }
        DB::table('favoritos')->where('id', $id)->delete();
        return response()->json([
            'message' => 'Favorito eliminado exitosamente',
            'data' => [ 'favorito_id' => $id ]
        ], 200);
    });

    /**
     * @api {delete} /comentario/{id} Eliminar comentario
     * @apiName DeleteComentario
     * @apiGroup Comentarios
     * @apiParam {Number} id ID del comentario
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::delete('/comentario/{id}', function($id) {
        $comentario = DB::table('comentarios')->where('id', $id)->first();
        if (!$comentario) {
            abort(404, 'Comentario no encontrado');
        }

        DB::table('comentarios')->where('id', $id)->update(['activo' => false]);
        return response()->json([
            'message' => 'Comentario eliminado exitosamente',
            'data' => [ 'comentario_id' => $id ]
        ], 200);
    });

    /**
     * @api {delete} /receta/{receta_id}/ingrediente/{ingrediente_id} Eliminar ingrediente de una receta
     * @apiName DeleteIngredienteReceta
     * @apiGroup Recetas
     * @apiParam {Number} receta_id ID de la receta
     * @apiParam {Number} ingrediente_id ID del ingrediente
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::delete('/receta/{receta_id}/ingrediente/{ingrediente_id}', function($receta_id, $ingrediente_id) {
        $existe = DB::table('receta_ingredientes')
            ->where('receta_id', $receta_id)
            ->where('ingrediente_id', $ingrediente_id)
            ->exists();
        if (!$existe) {
            abort(404, 'No existe ese ingrediente en la receta');
        }
        DB::table('receta_ingredientes')
            ->where('receta_id', $receta_id)
            ->where('ingrediente_id', $ingrediente_id)
            ->delete();
        return response()->json([
            'message' => 'Ingrediente eliminado de la receta exitosamente',
            'data' => [ 'receta_id' => $receta_id, 'ingrediente_id' => $ingrediente_id ]
        ], 200);
    });

    /**
     * @api {delete} /receta/{receta_id}/etiqueta/{etiqueta_id} Eliminar etiqueta de una receta
     * @apiName DeleteEtiquetaReceta
     * @apiGroup Recetas
     * @apiParam {Number} receta_id ID de la receta
     * @apiParam {Number} etiqueta_id ID de la etiqueta
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::delete('/receta/{receta_id}/etiqueta/{etiqueta_id}', function($receta_id, $etiqueta_id) {
        $existe = DB::table('receta_etiquetas')
            ->where('receta_id', $receta_id)
            ->where('etiqueta_id', $etiqueta_id)
            ->exists();
        if (!$existe) {
            abort(404, 'No existe esa etiqueta en la receta');
        }
        DB::table('receta_etiquetas')
            ->where('receta_id', $receta_id)
            ->where('etiqueta_id', $etiqueta_id)
            ->delete();
        return response()->json([
            'message' => 'Etiqueta eliminada de la receta exitosamente',
            'data' => [ 'receta_id' => $receta_id, 'etiqueta_id' => $etiqueta_id ]
        ], 200);
    });

    /**
     * @api {put} /receta/{receta_id}/ingrediente/{ingrediente_id} Editar ingrediente de una receta
     * @apiName EditIngredienteReceta
     * @apiGroup Recetas
     * @apiParam {Number} receta_id ID de la receta
     * @apiParam {Number} ingrediente_id ID del ingrediente
     * @apiParam {Number} cantidad Nueva cantidad
     * @apiParam {String} notas Nuevas notas (opcional)
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::put('/receta/{receta_id}/ingrediente/{ingrediente_id}', function(Request $request, $receta_id, $ingrediente_id) {
        $cantidad = $request->input('cantidad');
        $notas = $request->input('notas');
        if (!$cantidad || $cantidad <= 0) {
            abort(400, 'La cantidad es requerida y debe ser mayor a 0');
        }
        $existe = DB::table('receta_ingredientes')
            ->where('receta_id', $receta_id)
            ->where('ingrediente_id', $ingrediente_id)
            ->exists();
        if (!$existe) {
            abort(404, 'No existe ese ingrediente en la receta');
        }
        DB::table('receta_ingredientes')
            ->where('receta_id', $receta_id)
            ->where('ingrediente_id', $ingrediente_id)
            ->update([
                'cantidad' => $cantidad,
                'notas' => $notas
            ]);
        return response()->json([
            'message' => 'Ingrediente de la receta actualizado exitosamente',
            'data' => [ 'receta_id' => $receta_id, 'ingrediente_id' => $ingrediente_id, 'cantidad' => $cantidad, 'notas' => $notas ]
        ], 200);
    });

    /**
     * @api {put} /receta/{receta_id}/etiqueta/{etiqueta_id} Editar etiqueta de una receta
     * @apiName EditEtiquetaReceta
     * @apiGroup Recetas
     * @apiParam {Number} receta_id ID de la receta
     * @apiParam {Number} etiqueta_id ID de la etiqueta actual
     * @apiParam {Number} nueva_etiqueta_id Nuevo ID de la etiqueta
     * @apiSuccess {String} message Mensaje de confirmación
     */
    Route::put('/receta/{receta_id}/etiqueta/{etiqueta_id}', function(Request $request, $receta_id, $etiqueta_id) {
        $nueva_etiqueta_id = $request->input('nueva_etiqueta_id');
        if (!$nueva_etiqueta_id) {
            abort(400, 'El nuevo ID de etiqueta es requerido');
        }
        $existe = DB::table('receta_etiquetas')
            ->where('receta_id', $receta_id)
            ->where('etiqueta_id', $etiqueta_id)
            ->exists();
        if (!$existe) {
            abort(404, 'No existe esa etiqueta en la receta');
        }
        // Verificar que la nueva etiqueta existe
        if (!DB::table('etiquetas')->where('id', $nueva_etiqueta_id)->exists()) {
            abort(568, 'No existe una etiqueta con el ID proporcionado: ' . $nueva_etiqueta_id);
        }
        DB::table('receta_etiquetas')
            ->where('receta_id', $receta_id)
            ->where('etiqueta_id', $etiqueta_id)
            ->update(['etiqueta_id' => $nueva_etiqueta_id]);
        return response()->json([
            'message' => 'Etiqueta de la receta actualizada exitosamente',
            'data' => [ 'receta_id' => $receta_id, 'etiqueta_id_anterior' => $etiqueta_id, 'nueva_etiqueta_id' => $nueva_etiqueta_id ]
        ], 200);
    });

    /**
     * @api {get} /perfil Obtener perfil del usuario autenticado
     * @apiName GetPerfil
     * @apiGroup Usuario
     * @apiSuccess {Object} data Datos del perfil del usuario
     * @apiSuccess {Number} data.id ID del usuario
     * @apiSuccess {String} data.nombre_usuario Nombre de usuario
     * @apiSuccess {String} data.nombre_completo Nombre completo
     * @apiSuccess {String} data.email Email del usuario
     * @apiSuccess {String} data.bio Biografía del usuario
     * @apiSuccess {String} data.foto_perfil URL de la foto de perfil
     * @apiSuccess {String} data.fecha_registro Fecha de registro
     */
    Route::get('/perfil', function(Request $request) {
        $usuario = $request->user();
        
        if (!$usuario) {
            abort(401, 'Usuario no autenticado');
        }
    
        // Obtener datos completos del usuario
        $perfil = DB::table('usuarios')
            ->select([
                'id',
                'nombre_usuario',
                'nombre_completo',
                'email',
                'bio',
                'foto_perfil',
                'fecha_registro'
            ])
            ->where('id', $usuario->id)
            ->first();
    
        if (!$perfil) {
            abort(404, 'Perfil de usuario no encontrado');
        }
    
        // Construir URL completa para la foto de perfil
        if ($perfil->foto_perfil) {
            $perfil->foto_perfil = construirUrlImagen($perfil->foto_perfil, 'profiles');
        }
    
        return response()->json([
            'data' => $perfil
        ], 200);
    });



    /**
     * @api {get} /usuario/{nombre_usuario} Obtener perfil público de otro usuario por nombre de usuario
     * @apiName GetUsuarioPublicoPorNombre
     * @apiGroup Usuario
     * @apiParam {String} nombre_usuario Nombre de usuario a consultar
     * @apiSuccess {Object} data Datos del perfil público del usuario
     * @apiSuccess {Number} data.id ID del usuario
     * @apiSuccess {String} data.nombre_usuario Nombre de usuario
     * @apiSuccess {String} data.nombre_completo Nombre completo
     * @apiSuccess {String} data.bio Biografía del usuario
     * @apiSuccess {String} data.foto_perfil URL de la foto de perfil
     * @apiSuccess {String} data.fecha_registro Fecha de registro
     * @apiSuccess {Number} data.total_recetas Total de recetas del usuario
     * @apiSuccess {Number} data.total_favoritos_recibidos Total de favoritos recibidos en todas sus recetas
     */
    Route::get('/usuario/{nombre_usuario}', function(Request $request, $nombre_usuario) {
        // Verificar que el usuario existe
        $usuario = DB::table('usuarios')
            ->select([
                'id',
                'nombre_usuario',
                'nombre_completo',
                'bio',
                'foto_perfil',
                'fecha_registro'
            ])
            ->where('nombre_usuario', $nombre_usuario)
            ->first();
    
        if (!$usuario) {
            abort(404, 'Usuario no encontrado');
        }
    
        // Construir URL completa para la foto de perfil
        if ($usuario->foto_perfil) {
            $usuario->foto_perfil = construirUrlImagen($usuario->foto_perfil, 'profiles');
        }
    
        // Calcular estadísticas del usuario
        $totalRecetas = DB::table('recetas')
            ->where('usuario_id', $usuario->id)
            ->where('activa', true)
            ->count();
    
        $totalFavoritosRecibidos = DB::table('favoritos')
            ->join('recetas', 'favoritos.receta_id', '=', 'recetas.id')
            ->where('recetas.usuario_id', $usuario->id)
            ->where('recetas.activa', true)
            ->count();
    
        $usuario->total_recetas = $totalRecetas;
        $usuario->total_favoritos_recibidos = $totalFavoritosRecibidos;
    
        return response()->json([
            'data' => $usuario
        ], 200);
    });

    /**
     * @api {get} /usuario/{nombre_usuario}/recetas Obtener recetas de un usuario
     * @apiName GetRecetasUsuario
     * @apiGroup Usuario
     * @apiParam {String} nombre_usuario Nombre de usuario
     * @apiSuccess {Object[]} data Array de recetas del usuario
     * @apiSuccess {Number} data.id ID de la receta
     * @apiSuccess {String} data.titulo Título de la receta
     * @apiSuccess {String} data.descripcion Descripción de la receta
     * @apiSuccess {String} data.dificultad Dificultad de la receta
     * @apiSuccess {String} data.foto_principal URL de la foto principal
     * @apiSuccess {String} data.fecha_creacion Fecha de creación
     * @apiSuccess {String} data.categoria_nombre Nombre de la categoría
     * @apiSuccess {Object[]} data.ingredientes Lista de ingredientes
     * @apiSuccess {Object[]} data.etiquetas Lista de etiquetas
     * @apiSuccess {Number} data.total_favoritos Total de favoritos de la receta
     */
    Route::get('/usuario/{nombre_usuario}/recetas', function(Request $request, $nombre_usuario) {
        // Verificar que el usuario existe
        $usuario = DB::table('usuarios')
            ->where('nombre_usuario', $nombre_usuario)
            ->first();
    
        if (!$usuario) {
            abort(404, 'Usuario no encontrado');
        }
    
        // Obtener recetas del usuario con información completa
        $recetas = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.tiempo_preparacion',
                'recetas.tiempo_coccion',
                'recetas.porciones',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.instrucciones',
                'recetas.fecha_creacion',
                'recetas.fecha_actualizacion',
                'categorias.nombre as categoria_nombre',
                DB::raw('(SELECT COUNT(*) FROM favoritos WHERE favoritos.receta_id = recetas.id) as total_favoritos')
            ])
            ->leftJoin('categorias', 'recetas.categoria_id', '=', 'categorias.id')
            ->where('recetas.usuario_id', $usuario->id)
            ->where('recetas.activa', true)
            ->orderBy('recetas.fecha_creacion', 'desc')
            ->get()
            ->map(function ($receta) {
                // Construir URL completa para la foto principal
                if ($receta->foto_principal) {
                    $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
                }
                
                // Obtener ingredientes de la receta
                $ingredientes = DB::table('receta_ingredientes')
                    ->select([
                        'ingredientes.nombre',
                        'ingredientes.unidad_medida',
                        'receta_ingredientes.cantidad',
                        'receta_ingredientes.notas'
                    ])
                    ->join('ingredientes', 'receta_ingredientes.ingrediente_id', '=', 'ingredientes.id')
                    ->where('receta_ingredientes.receta_id', $receta->id)
                    ->get();
                
                // Obtener etiquetas de la receta
                $etiquetas = DB::table('receta_etiquetas')
                    ->select(['etiquetas.nombre', 'etiquetas.color'])
                    ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
                    ->where('receta_etiquetas.receta_id', $receta->id)
                    ->get();
                
                $receta->ingredientes = $ingredientes;
                $receta->etiquetas = $etiquetas;
                
                return $receta;
            });
    
        return response()->json([
            'data' => $recetas
        ], 200);
    });

    /**
     * @api {put} /perfil Actualizar perfil del usuario autenticado
     * @apiName UpdatePerfil
     * @apiGroup Usuario
     * @apiParam {String} [nombre_usuario] Nuevo nombre de usuario
     * @apiParam {String} [nombre_completo] Nuevo nombre completo
     * @apiParam {String} [email] Nuevo email
     * @apiParam {String} [bio] Nueva biografía
     * @apiParam {String} [foto_perfil] Nueva foto de perfil (base64)
     * @apiParam {String} [password_actual] Contraseña actual (requerida si se cambia la contraseña)
     * @apiParam {String} [password_nuevo] Nueva contraseña
     * @apiParam {String} [password_confirmar] Confirmación de la nueva contraseña
     * @apiSuccess {String} message Mensaje de confirmación
     * @apiSuccess {Object} data Datos de la operación
     */
    Route::put('/perfil', function(Request $request) {
        $usuario = $request->user();
        
        if (!$usuario) {
            abort(401, 'Usuario no autenticado');
        }
    
        $camposEditables = [
            'nombre_usuario' => $request->input('nombre_usuario'),
            'nombre_completo' => $request->input('nombre_completo'),
            'email' => $request->input('email'),
            'bio' => $request->input('bio'),
            'foto_perfil' => $request->input('foto_perfil')
        ];
    
        // Verificar que al menos un campo se está actualizando
        $camposActualizados = array_filter($camposEditables, function($valor) {
            return $valor !== null;
        });
    
        // Procesar cambio de contraseña si se proporciona
        $passwordActual = $request->input('password_actual');
        $passwordNuevo = $request->input('password_nuevo');
        $passwordConfirmar = $request->input('password_confirmar');
    
        if ($passwordNuevo || $passwordConfirmar || $passwordActual) {
            // Validar que se proporcionen todos los campos de contraseña
            if (!$passwordActual || !$passwordNuevo || !$passwordConfirmar) {
                abort(400, 'Para cambiar la contraseña debes proporcionar: contraseña actual, nueva contraseña y confirmación');
            }
    
            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($passwordActual, $usuario->password)) {
                abort(400, 'La contraseña actual es incorrecta');
            }
    
            // Verificar que las contraseñas nuevas coincidan
            if ($passwordNuevo !== $passwordConfirmar) {
                abort(400, 'La nueva contraseña y su confirmación no coinciden');
            }
    
            // Validar que la nueva contraseña tenga al menos 6 caracteres
            if (strlen($passwordNuevo) < 6) {
                abort(400, 'La nueva contraseña debe tener al menos 6 caracteres');
            }
    
            // Agregar la nueva contraseña hasheada a los campos actualizados
            $camposActualizados['password'] = Hash::make($passwordNuevo);
        }
    
        if (empty($camposActualizados)) {
            abort(400, 'Debes proporcionar al menos un campo para actualizar');
        }
    
        // Validar nombre de usuario si se proporciona
        if (isset($camposEditables['nombre_usuario']) && $camposEditables['nombre_usuario'] !== null) {
            if (empty(trim($camposEditables['nombre_usuario']))) {
                abort(400, 'El nombre de usuario no puede estar vacío');
            }
    
            // Verificar que el nombre de usuario no esté en uso por otro usuario
            $usuarioExistente = DB::table('usuarios')
                ->where('nombre_usuario', trim($camposEditables['nombre_usuario']))
                ->where('id', '!=', $usuario->id)
                ->exists();
    
            if ($usuarioExistente) {
                abort(400, 'El nombre de usuario ya está en uso');
            }
    
            $camposActualizados['nombre_usuario'] = trim($camposEditables['nombre_usuario']);
        }
    
        // Validar nombre completo si se proporciona
        if (isset($camposEditables['nombre_completo']) && $camposEditables['nombre_completo'] !== null) {
            if (empty(trim($camposEditables['nombre_completo']))) {
                abort(400, 'El nombre completo no puede estar vacío');
            }
            $camposActualizados['nombre_completo'] = trim($camposEditables['nombre_completo']);
        }
    
        // Validar email si se proporciona
        if (isset($camposEditables['email']) && $camposEditables['email'] !== null) {
            if (empty(trim($camposEditables['email']))) {
                abort(400, 'El email no puede estar vacío');
            }
    
            if (!filter_var($camposEditables['email'], FILTER_VALIDATE_EMAIL)) {
                abort(400, 'El formato del email no es válido');
            }
    
            // Verificar que el email no esté en uso por otro usuario
            $emailExistente = DB::table('usuarios')
                ->where('email', trim($camposEditables['email']))
                ->where('id', '!=', $usuario->id)
                ->exists();
    
            if ($emailExistente) {
                abort(400, 'El email ya está en uso');
            }
    
            $camposActualizados['email'] = trim($camposEditables['email']);
        }
    
        // Procesar imagen de perfil si se proporciona
        if (isset($camposEditables['foto_perfil']) && $camposEditables['foto_perfil']) {
            $resultadoImagen = procesarImagenPerfil($camposEditables['foto_perfil'], $usuario->id);
            
            if (!$resultadoImagen['success']) {
                abort(400, $resultadoImagen['error']);
            }
            
            $camposActualizados['foto_perfil'] = $resultadoImagen['filename'];
        }
    
        // Actualizar el usuario
        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update($camposActualizados);
    
        // Obtener el perfil actualizado
        $perfilActualizado = DB::table('usuarios')
            ->select([
                'id',
                'nombre_usuario',
                'nombre_completo',
                'email',
                'bio',
                'foto_perfil',
                'fecha_registro'
            ])
            ->where('id', $usuario->id)
            ->first();
    
        // Construir URL completa para la foto de perfil
        if ($perfilActualizado->foto_perfil) {
            $perfilActualizado->foto_perfil = construirUrlImagen($perfilActualizado->foto_perfil, 'profiles');
        }
    
        $mensaje = 'Perfil actualizado exitosamente';
        if (isset($camposActualizados['password'])) {
            $mensaje .= ' (incluyendo contraseña)';
        }
    
        return response()->json([
            'message' => $mensaje,
            'data' => [
                'perfil_actualizado' => $perfilActualizado,
                'campos_modificados' => array_keys($camposActualizados)
            ]
        ], 200);
    })->middleware('auth:sanctum') ->name('perfil.update');

    // --- POSTS PERSONALES ---
    /**
     * @api {get} /personal_posts_preview Vista previa de posts personales
     * @apiSuccess {Object[]} data
     */
    Route::get('/personal_posts_preview', function (Request $request) {
        // Obtener el usuario autenticado del token
        $usuario = $request->user();
        $usuarioId = $usuario->id;

        $recetas = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.usuario_id',
                'usuarios.nombre_usuario'
            ])
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->where('recetas.usuario_id', $usuarioId)
            ->where('recetas.activa', true)
            ->orderBy('recetas.fecha_creacion', 'desc')
            ->get()
            ->map(function ($receta) {
                // Construir URL completa para la imagen
                if ($receta->foto_principal) {
                    $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
                }
                
                // Contar favoritos de la receta
                $totalFavoritos = DB::table('favoritos')
                    ->where('receta_id', $receta->id)
                    ->count();
                
                $receta->total_favoritos = $totalFavoritos;
                return $receta;
            });

        return response()->json([
            'data' => $recetas
        ], 200);
    });

    /**
     * @api {get} /personal_posts/:id Detalles completos de post personal
     * @apiParam {Number} id ID de la receta
     * @apiSuccess {Object} data
     */
    Route::get('/personal_posts/{id}', function(Request $request, $id) {
        // Obtener el usuario autenticado del token
        $usuario = $request->user();
        $usuarioId = $usuario->id;

        $receta = DB::table('recetas')
            ->select([
                'recetas.id',
                'recetas.titulo',
                'recetas.descripcion',
                'recetas.tiempo_preparacion',
                'recetas.tiempo_coccion',
                'recetas.porciones',
                'recetas.dificultad',
                'recetas.foto_principal',
                'recetas.instrucciones',
                'recetas.fecha_creacion',
                'recetas.fecha_actualizacion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil',
                'categorias.nombre as categoria_nombre'
            ])
            ->join('usuarios', 'recetas.usuario_id', '=', 'usuarios.id')
            ->leftJoin('categorias', 'recetas.categoria_id', '=', 'categorias.id')
            ->where('recetas.id', $id)
            ->where('recetas.usuario_id', $usuarioId) // Solo recetas del usuario autenticado
            ->where('recetas.activa', true)
            ->first();

        if (!$receta) {
            abort(404, 'Receta no encontrada o no tienes permisos para verla');
        }

        // Construir URLs completas para las imágenes
        if ($receta->foto_principal) {
            $receta->foto_principal = construirUrlImagen($receta->foto_principal, 'posts');
        }
        if ($receta->foto_perfil) {
            $receta->foto_perfil = construirUrlImagen($receta->foto_perfil, 'profiles');
        }

        // Obtener ingredientes de la receta
        $ingredientes = DB::table('receta_ingredientes')
            ->select([
                'ingredientes.nombre',
                'ingredientes.unidad_medida',
                'receta_ingredientes.cantidad',
                'receta_ingredientes.notas'
            ])
            ->join('ingredientes', 'receta_ingredientes.ingrediente_id', '=', 'ingredientes.id')
            ->where('receta_ingredientes.receta_id', $id)
            ->get();

        // Obtener etiquetas de la receta
        $etiquetas = DB::table('receta_etiquetas')
            ->select(['etiquetas.nombre', 'etiquetas.color'])
            ->join('etiquetas', 'receta_etiquetas.etiqueta_id', '=', 'etiquetas.id')
            ->where('receta_etiquetas.receta_id', $id)
            ->get();

        // Obtener comentarios de la receta
        $comentarios = DB::table('comentarios')
            ->select([
                'comentarios.id',
                'comentarios.comentario',
                'comentarios.fecha_comentario',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('usuarios', 'comentarios.usuario_id', '=', 'usuarios.id')
            ->where('comentarios.receta_id', $id)
            ->where('comentarios.activo', true)
            ->orderBy('comentarios.fecha_comentario', 'desc')
            ->get()
            ->map(function ($comentario) {
                // Construir URL completa para la foto de perfil
                if ($comentario->foto_perfil) {
                    $comentario->foto_perfil = construirUrlImagen($comentario->foto_perfil, 'profiles');
                }
                return $comentario;
            });

        // Obtener valoraciones de la receta
        $valoraciones = DB::table('valoraciones')
            ->select([
                'valoraciones.id',
                'valoraciones.puntuacion',
                'valoraciones.fecha_valoracion',
                'usuarios.nombre_usuario',
                'usuarios.foto_perfil'
            ])
            ->join('usuarios', 'valoraciones.usuario_id', '=', 'usuarios.id')
            ->where('valoraciones.receta_id', $id)
            ->orderBy('valoraciones.fecha_valoracion', 'desc')
            ->get()
            ->map(function ($valoracion) {
                // Construir URL completa para la foto de perfil
                if ($valoracion->foto_perfil) {
                    $valoracion->foto_perfil = construirUrlImagen($valoracion->foto_perfil, 'profiles');
                }
                return $valoracion;
            });

        // Calcular promedio de valoraciones
        $promedioValoraciones = 0;
        $totalValoraciones = $valoraciones->count();
        
        if ($totalValoraciones > 0) {
            $sumaValoraciones = $valoraciones->sum('puntuacion');
            $promedioValoraciones = round($sumaValoraciones / $totalValoraciones, 1);
        }

        // Contar favoritos de la receta
        $totalFavoritos = DB::table('favoritos')
            ->where('receta_id', $id)
            ->count();

        // Estructurar la respuesta
        $receta->ingredientes = $ingredientes;
        $receta->etiquetas = $etiquetas;
        $receta->comentarios = $comentarios;
        $receta->valoraciones = $valoraciones;
        $receta->promedio_valoraciones = $promedioValoraciones;
        $receta->total_valoraciones = $totalValoraciones;
        $receta->total_favoritos = $totalFavoritos;

        return response()->json([
            'data' => $receta
        ], 200);
    });


});

// --- RUTAS PÚBLICAS PARA SERVIR IMÁGENES ---
//! Ruta simple para servir imágenes de posts
Route::get('/images/posts/{filename}', function($filename) {
    $baseImagePath = env('APP_IMAGES_PATH');
    $imagePath = $baseImagePath . '/posts/' . $filename;
    
    if (!file_exists($imagePath)) {
        abort(404, 'Imagen de post no encontrada');
    }
    
    return response()->file($imagePath);
});

//! Ruta simple para servir imágenes de perfil
Route::get('/images/profiles/{filename}', function($filename) {
    $baseImagePath = env('APP_IMAGES_PATH');
    $imagePath = $baseImagePath . '/profiles/' . $filename;
    
    if (!file_exists($imagePath)) {
        abort(404, 'Imagen de perfil no encontrada');
    }
    
    return response()->file($imagePath);
});

// --- AUTENTICACIÓN: REGISTRO DE USUARIO ---
/**
 * @api {post} /register Registro de usuario
 * @apiParam {String} nombre_usuario
 * @apiParam {String} nombre_completo
 * @apiParam {String} email
 * @apiParam {String} password
 * @apiSuccess {String} token
 */
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'nombre_usuario' => 'required|string|unique:usuarios,nombre_usuario',
        'nombre_completo' => 'required|string',
        'email' => 'required|email|unique:usuarios,email',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'nombre_usuario' => $validated['nombre_usuario'],
        'nombre_completo' => $validated['nombre_completo'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'fecha_registro' => now(),
        'activo' => true,
        'foto_perfil' => 'https://via.placeholder.com/150',
    ]);

    $token = $user->createToken('api_token', ['*'], now()->addDays(30))->plainTextToken;

    return response()->json([
        'token' => $token,
        'data' => [
            'id' => $user->id,
            'nombre_usuario' => $user->nombre_usuario,
            'email' => $user->email
        ]
    ], 201);
})->middleware('throttle:3,1'); //! 3 intentos por minuto

/**
 * @api {post} /login Login de usuario
 * @apiParam {String} email
 * @apiParam {String} password
 * @apiSuccess {String} token
 */
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    $token = $user->createToken('api_token', ['*'], now()->addDays(30))->plainTextToken;

    return response()->json([
        'token' => $token,
        'data' => [
            'id' => $user->id,
            'nombre_usuario' => $user->nombre_usuario,
            'email' => $user->email
        ]
    ], 200);
})->middleware('throttle:5,1'); //! 5 intentos por minuto

