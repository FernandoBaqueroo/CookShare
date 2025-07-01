<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Importar funciones helper
require_once __DIR__ . '/../functions/api.php';

/**
 * @api {get} /feed Obtener feed de recetas
 * @apiName GetFeed
 * @apiGroup Recetas
 * @apiParam {Number} usuario_id ID del usuario que solicita el feed
 * @apiSuccess {Object[]} data Lista de recetas
 * @apiSuccess {String} data.titulo Título de la receta
 * @apiSuccess {String} data.descripcion Descripción de la receta
 * @apiSuccess {String} data.dificultad Nivel de dificultad
 * @apiSuccess {String} data.foto_principal URL de la foto principal
 * @apiSuccess {Object} data.usuario Datos del usuario que creó la receta
 * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
 * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
 */
Route::get('/feed', function (Request $request) {
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
        ->where('recetas.usuario_id', '!=', $usuarioId)
        ->where('recetas.activa', true)
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
 * @api {get} /receta/:id Obtener una receta específica completa
 * @apiName GetReceta
 * @apiGroup Recetas
 * @apiParam {Number} id ID de la receta
 * @apiSuccess {Object} data Datos completos de la receta
 * @apiSuccess {String} data.titulo Título de la receta
 * @apiSuccess {String} data.descripcion Descripción de la receta
 * @apiSuccess {Number} data.tiempo_preparacion Tiempo de preparación en minutos
 * @apiSuccess {Number} data.tiempo_coccion Tiempo de cocción en minutos
 * @apiSuccess {Number} data.porciones Número de porciones
 * @apiSuccess {String} data.dificultad Nivel de dificultad
 * @apiSuccess {String} data.foto_principal URL de la foto principal
 * @apiSuccess {String} data.instrucciones Instrucciones de preparación
 * @apiSuccess {String} data.fecha_creacion Fecha de creación
 * @apiSuccess {String} data.fecha_actualizacion Fecha de última actualización
 * @apiSuccess {Object} data.usuario Datos del usuario que creó la receta
 * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
 * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
 * @apiSuccess {Object} data.categoria Datos de la categoría
 * @apiSuccess {String} data.categoria.nombre Nombre de la categoría
 * @apiSuccess {Object[]} data.ingredientes Lista de ingredientes
 * @apiSuccess {String} data.ingredientes.nombre Nombre del ingrediente
 * @apiSuccess {String} data.ingredientes.cantidad Cantidad con unidad de medida
 * @apiSuccess {String} data.ingredientes.notas Notas adicionales
 * @apiSuccess {Object[]} data.etiquetas Lista de etiquetas
 * @apiSuccess {String} data.etiquetas.nombre Nombre de la etiqueta
 * @apiSuccess {String} data.etiquetas.color Color de la etiqueta (hexadecimal)
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
 * @api {get} /ingredientes Obtener ingredientes de una receta
 * @apiName GetIngredientes
 * @apiGroup Ingredientes
 * @apiParam {Number} receta_id ID de la receta
 * @apiSuccess {Object[]} data Lista de ingredientes
 * @apiSuccess {String} data.nombre Nombre del ingrediente
 * @apiSuccess {String} data.cantidad Cantidad con unidad de medida
 * @apiSuccess {String} data.notas Notas adicionales
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
 * @api {get} /favoritos Obtener recetas favoritas de un usuario
 * @apiName GetFavoritos
 * @apiGroup Favoritos
 * @apiParam {Number} usuario_id ID del usuario
 * @apiSuccess {Object[]} data Lista de recetas favoritas
 * @apiSuccess {String} data.titulo Título de la receta
 * @apiSuccess {Object} data.usuario Datos del usuario que creó la receta
 * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
 * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
 * @apiSuccess {String} data.fecha_favorito Fecha en que se marcó como favorita
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
 * @api {get} /comentarios Obtener comentarios de una receta
 * @apiName GetComentarios
 * @apiGroup Comentarios
 * @apiParam {Number} receta_id ID de la receta
 * @apiSuccess {Object[]} data Lista de comentarios
 * @apiSuccess {String} data.comentario Texto del comentario
 * @apiSuccess {String} data.fecha_comentario Fecha del comentario
 * @apiSuccess {Object} data.usuario Datos del usuario que comentó
 * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
 * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
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

    if ($comentarios->isEmpty()) {
        abort(563, 'La receta no tiene comentarios');
    }

    return response()->json([
        'data' => $comentarios
    ], 200);
});

/**
 * @api {get} /valoraciones Obtener valoraciones de una receta
 * @apiName GetValoraciones
 * @apiGroup Valoraciones
 * @apiParam {Number} receta_id ID de la receta
 * @apiSuccess {Object[]} data Lista de valoraciones
 * @apiSuccess {String} data.titulo Título de la receta
 * @apiSuccess {Object} data.usuario Datos del usuario que valoró
 * @apiSuccess {String} data.usuario.nombre_usuario Nombre del usuario
 * @apiSuccess {String} data.usuario.foto_perfil URL de la foto de perfil
 * @apiSuccess {Object} data.valoracion Datos de la valoración
 * @apiSuccess {Number} data.valoracion.puntuacion Puntuación dada (1-5)
 * @apiSuccess {String} data.valoracion.fecha_valoracion Fecha de la valoración
 * @apiNote Este endpoint solo muestra puntuaciones. Para comentarios usa /comentarios
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
                    'puntuacion' => $item->puntuacion,
                    'fecha_valoracion' => $item->fecha_valoracion
                ]
            ];
        });

    if ($valoraciones->isEmpty()) {
        abort(564, 'La receta no tiene valoraciones');
    }

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

//! Ruta simple para servir imágenes de posts
Route::get('/images/posts/{filename}', function($filename) {
    $baseImagePath = env('APP_IMAGES_PATH');
    $imagePath = $baseImagePath . '/posts/' . $filename;
    
    if (!file_exists($imagePath)) {
        abort(404, 'Imagen de post no encontrada');
    }
    
    return response()->file($imagePath);
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
});

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
});

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

