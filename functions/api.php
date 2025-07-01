<?php

// Funciones helper para manejo de imágenes base64
function validarImagenBase64($base64String) {
    // Verificar que es base64 válido con formatos extendidos
    if (!preg_match('/^data:image\/(jpeg|png|gif|jpg|webp|bmp|tiff|heic|heif);base64,/', $base64String)) {
        return false;
    }

    // Extraer los datos base64
    $data = explode(',', $base64String);
    if (count($data) !== 2) {
        return false;
    }

    $imageData = base64_decode($data[1]);
    if ($imageData === false) {
        return false;
    }

    // Verificar que es una imagen válida
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $imageData);
    finfo_close($finfo);

    // Formatos soportados incluyendo HEIC/HEIF de iPhone
    $allowedTypes = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/tiff',
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence'
    ];
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }

    // Verificar tamaño (máximo 10MB para formatos modernos)
    if (strlen($imageData) > 10 * 1024 * 1024) {
        return false;
    }

    return true;
}

function comprimirImagenBase64($base64String) {
    // Extraer los datos base64
    $data = explode(',', $base64String);
    $imageData = base64_decode($data[1]);
    
    // Crear imagen desde string
    $image = imagecreatefromstring($imageData);
    if (!$image) {
        return $base64String; // Si no se puede procesar, devolver original
    }

    // Obtener dimensiones originales
    $width = imagesx($image);
    $height = imagesy($image);

    // Reducir dimensiones máximas para comprimir más
    $maxWidth = 800;
    $maxHeight = 600;

    if ($width > $maxWidth || $height > $maxHeight) {
        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Crear nueva imagen redimensionada
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia para PNG y formatos con alpha
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        // Redimensionar
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Capturar output con mayor compresión
        ob_start();
        imagejpeg($newImage, null, 85); // Calidad 85% (buena calidad)
        $compressedData = ob_get_contents();
        ob_end_clean();
        
        // Limpiar memoria
        imagedestroy($image);
        imagedestroy($newImage);
        
        // Convertir a base64
        return 'data:image/jpeg;base64,' . base64_encode($compressedData);
    }

    // Si no necesita redimensionar, solo comprimir
    ob_start();
    imagejpeg($image, null, 85); // Calidad 85%
    $compressedData = ob_get_contents();
    ob_end_clean();
    
    imagedestroy($image);
    
    return 'data:image/jpeg;base64,' . base64_encode($compressedData);
}

// Función para obtener la extensión correcta según el formato
function obtenerExtensionImagen($base64String) {
    // Extraer el tipo MIME
    if (preg_match('/^data:image\/([^;]+);base64,/', $base64String, $matches)) {
        $mimeType = $matches[1];
        
        // Mapear tipos MIME a extensiones
        $extensionMap = [
            'jpeg' => 'jpg',
            'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'bmp' => 'bmp',
            'tiff' => 'tiff',
            'heic' => 'heic',
            'heif' => 'heif'
        ];
        
        return $extensionMap[$mimeType] ?? 'jpg';
    }
    
    return 'jpg';
}

/**
 * Procesa y guarda una imagen base64 en disco
 * @param string $base64String Imagen en formato base64
 * @param string $directory Directorio donde guardar la imagen
 * @param string $filename Nombre del archivo (sin extensión)
 * @param array $options Opciones adicionales (maxSize, maxWidth, maxHeight, quality, imageType)
 * @return array Array con 'success', 'filename', 'path', 'url' y 'error' si falla
 */
function procesarYGuardarImagen($base64String, $directory, $filename, $options = []) {
    // Configurar opciones por defecto
    $defaultOptions = [
        'maxSize' => 10 * 1024 * 1024,
        'maxWidth' => 800,
        'maxHeight' => 600,
        'quality' => 85,
        'resizeProfile' => false,
        'profileSize' => 150,
        'imageType' => 'posts' // 'posts' o 'profiles'
    ];
    $options = array_merge($defaultOptions, $options);
    
    try {
        // 1. Validar formato y tamaño
        if (!validarImagenBase64($base64String)) {
            return [
                'success' => false,
                'error' => 'Formato de imagen inválido o tamaño excede el límite permitido'
            ];
        }
        
        // 2. Comprimir y optimizar imagen
        $imagenOptimizada = comprimirImagenBase64($base64String);
        
        // 3. Obtener extensión
        $extension = obtenerExtensionImagen($imagenOptimizada);
        
        // 4. Crear directorio si no existe
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return [
                    'success' => false,
                    'error' => 'No se pudo crear el directorio: ' . $directory
                ];
            }
        }
        
        // 5. Construir ruta completa del archivo
        $filePath = $directory . '/' . $filename . '.' . $extension;
        
        // 6. Decodificar y guardar
        $imageData = base64_decode(explode(',', $imagenOptimizada)[1]);
        
        if (!file_put_contents($filePath, $imageData)) {
            return [
                'success' => false,
                'error' => 'No se pudo guardar la imagen en: ' . $filePath
            ];
        }
        
        // 7. Si es imagen de perfil, redimensionar a tamaño específico
        if ($options['resizeProfile']) {
            $imagenRedimensionada = redimensionarImagenPerfil($filePath, $options['profileSize']);
            if ($imagenRedimensionada) {
                file_put_contents($filePath, $imagenRedimensionada);
            }
        }
        
        // 8. Generar URL específica según el tipo de imagen
        $urlPath = '/api/images/' . $options['imageType'] . '/' . $filename . '.' . $extension;
        
        return [
            'success' => true,
            'filename' => $filename . '.' . $extension,
            'path' => $filePath,
            'url' => $urlPath
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Error procesando imagen: ' . $e->getMessage()
        ];
    }
}

/**
 * Redimensiona una imagen de perfil a un tamaño específico
 * @param string $filePath Ruta del archivo de imagen
 * @param int $size Tamaño en píxeles (cuadrado)
 * @return string|false Datos de la imagen redimensionada o false si falla
 */
function redimensionarImagenPerfil($filePath, $size = 150) {
    try {
        // Crear imagen desde archivo
        $image = imagecreatefromstring(file_get_contents($filePath));
        if (!$image) {
            return false;
        }
        
        // Obtener dimensiones originales
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Crear nueva imagen cuadrada
        $newImage = imagecreatetruecolor($size, $size);
        
        // Preservar transparencia
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Calcular dimensiones para mantener proporción y centrar
        $ratio = max($size / $width, $size / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        $x = ($size - $newWidth) / 2;
        $y = ($size - $newHeight) / 2;
        
        // Redimensionar
        imagecopyresampled($newImage, $image, $x, $y, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Capturar output
        ob_start();
        imagejpeg($newImage, null, 90);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Limpiar memoria
        imagedestroy($image);
        imagedestroy($newImage);
        
        return $imageData;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Valida y procesa una imagen de perfil
 * @param string $base64String Imagen en formato base64
 * @param int $usuarioId ID del usuario
 * @return array Resultado del procesamiento
 */
function procesarImagenPerfil($base64String, $usuarioId) {
    // Usar la ruta base de imágenes y añadir subcarpeta 'profiles'
    $baseImagePath = env('APP_IMAGES_PATH');
    $profileImagePath = $baseImagePath . '/profiles';
    
    return procesarYGuardarImagen($base64String, $profileImagePath, $usuarioId, [
        'resizeProfile' => true,
        'profileSize' => 150,
        'maxWidth' => 800,
        'maxHeight' => 800,
        'imageType' => 'profiles'
    ]);
}

/**
 * Valida y procesa una imagen de receta
 * @param string $base64String Imagen en formato base64
 * @param int $recetaId ID de la receta
 * @return array Resultado del procesamiento
 */
function procesarImagenReceta($base64String, $recetaId) {
    // Usar la ruta base de imágenes y añadir subcarpeta 'posts'
    $baseImagePath = env('APP_IMAGES_PATH');
    $postsImagePath = $baseImagePath . '/posts';
    
    return procesarYGuardarImagen($base64String, $postsImagePath, $recetaId, [
        'maxWidth' => 1200,
        'maxHeight' => 800,
        'quality' => 85,
        'imageType' => 'posts'
    ]);
}

/**
 * Construye la URL completa de una imagen según su tipo
 * @param string $filename Nombre del archivo de imagen
 * @param string $type Tipo de imagen ('posts' o 'profiles')
 * @return string URL completa de la imagen
 */
function construirUrlImagen($filename, $type = 'posts') {
    if (!$filename) {
        return null;
    }
    
    return url('/api/images/' . $type . '/' . $filename);
}

/**
 * Valida que un usuario existe
 * @param int $usuarioId ID del usuario
 * @return bool True si existe, false si no
 */
function validarUsuarioExiste($usuarioId) {
    return DB::table('usuarios')->where('id', $usuarioId)->exists();
}

/**
 * Valida que una receta existe
 * @param int $recetaId ID de la receta
 * @return bool True si existe, false si no
 */
function validarRecetaExiste($recetaId) {
    return DB::table('recetas')->where('id', $recetaId)->exists();
}

/**
 * Valida que una categoría existe
 * @param int $categoriaId ID de la categoría
 * @return bool True si existe, false si no
 */
function validarCategoriaExiste($categoriaId) {
    return DB::table('categorias')->where('id', $categoriaId)->exists();
}

/**
 * Valida campos requeridos de una request
 * @param Request $request Request de Laravel
 * @param array $campos Array de campos requeridos ['campo' => 'mensaje_error']
 * @return array Array con 'success' y 'error' si falla
 */
function validarCamposRequeridos($request, $campos) {
    foreach ($campos as $campo => $mensaje) {
        if (!$request->input($campo)) {
            return ['success' => false, 'error' => $mensaje];
        }
    }
    return ['success' => true];
}

/**
 * Valida que un usuario no esté valorando su propia receta
 * @param int $recetaId ID de la receta
 * @param int $usuarioId ID del usuario
 * @return array Array con 'success' y 'error' si falla
 */
function validarNoValorarPropiaReceta($recetaId, $usuarioId) {
    $autorReceta = DB::table('recetas')->where('id', $recetaId)->value('usuario_id');
    if ($autorReceta == $usuarioId) {
        return ['success' => false, 'error' => 'No puedes valorar tu propia receta'];
    }
    return ['success' => true];
}

/**
 * Valida que no exista una valoración previa
 * @param int $recetaId ID de la receta
 * @param int $usuarioId ID del usuario
 * @return array Array con 'success' y 'error' si falla
 */
function validarNoValoracionPrevia($recetaId, $usuarioId) {
    $valoracionExistente = DB::table('valoraciones')
        ->where('receta_id', $recetaId)
        ->where('usuario_id', $usuarioId)
        ->exists();
    
    if ($valoracionExistente) {
        return ['success' => false, 'error' => 'Ya has valorado esta receta anteriormente'];
    }
    return ['success' => true];
}

/**
 * Valida que no exista un favorito previo
 * @param int $recetaId ID de la receta
 * @param int $usuarioId ID del usuario
 * @return array Array con 'success' y 'error' si falla
 */
function validarNoFavoritoPrevio($recetaId, $usuarioId) {
    $favoritoExistente = DB::table('favoritos')
        ->where('receta_id', $recetaId)
        ->where('usuario_id', $usuarioId)
        ->exists();
    
    if ($favoritoExistente) {
        return ['success' => false, 'error' => 'Ya has añadido esta receta a favoritos'];
    }
    return ['success' => true];
}

/**
 * Valida que un nombre de usuario sea único
 * @param string $nombreUsuario Nombre de usuario
 * @param int $excludeId ID a excluir (para ediciones)
 * @return array Array con 'success' y 'error' si falla
 */
function validarNombreUsuarioUnico($nombreUsuario, $excludeId = null) {
    $query = DB::table('usuarios')->where('nombre_usuario', $nombreUsuario);
    
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }
    
    if ($query->exists()) {
        return ['success' => false, 'error' => 'El nombre de usuario ya está en uso'];
    }
    return ['success' => true];
}

/**
 * Valida que un email sea único
 * @param string $email Email
 * @param int $excludeId ID a excluir (para ediciones)
 * @return array Array con 'success' y 'error' si falla
 */
function validarEmailUnico($email, $excludeId = null) {
    $query = DB::table('usuarios')->where('email', $email);
    
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }
    
    if ($query->exists()) {
        return ['success' => false, 'error' => 'El email ya está en uso'];
    }
    return ['success' => true];
}

/**
 * Valida campos de receta
 * @param array $campos Array de campos a validar
 * @return array Array con 'success' y 'error' si falla
 */
function validarCamposReceta($campos) {
    $validaciones = [
        'titulo' => function($valor) { return !empty(trim($valor)) ? true : 'El título no puede estar vacío'; },
        'descripcion' => function($valor) { return !empty(trim($valor)) ? true : 'La descripción no puede estar vacía'; },
        'tiempo_preparacion' => function($valor) { return $valor > 0 ? true : 'El tiempo de preparación debe ser mayor a 0'; },
        'tiempo_coccion' => function($valor) { return $valor > 0 ? true : 'El tiempo de cocción debe ser mayor a 0'; },
        'porciones' => function($valor) { return $valor > 0 ? true : 'El número de porciones debe ser mayor a 0'; },
        'dificultad' => function($valor) { 
            return in_array($valor, ['Fácil', 'Intermedio', 'Difícil']) ? true : 'La dificultad debe ser: Fácil, Intermedio o Difícil'; 
        }
    ];

    foreach ($validaciones as $campo => $validacion) {
        if (isset($campos[$campo])) {
            $resultado = $validacion($campos[$campo]);
            if ($resultado !== true) {
                return ['success' => false, 'error' => $resultado];
            }
        }
    }
    
    return ['success' => true];
}

/**
 * Valida que un ingrediente existe
 * @param int $ingredienteId ID del ingrediente
 * @return bool True si existe, false si no
 */
function validarIngredienteExiste($ingredienteId) {
    return DB::table('ingredientes')->where('id', $ingredienteId)->exists();
}

/**
 * Valida que una etiqueta existe
 * @param int $etiquetaId ID de la etiqueta
 * @return bool True si existe, false si no
 */
function validarEtiquetaExiste($etiquetaId) {
    return DB::table('etiquetas')->where('id', $etiquetaId)->exists();
}

/**
 * Valida que un array de ingredientes es válido
 * @param array $ingredientes Array de ingredientes
 * @return array Array con 'success' y 'error' si falla
 */
function validarIngredientes($ingredientes) {
    if (!is_array($ingredientes) || empty($ingredientes)) {
        return ['success' => false, 'error' => 'Los ingredientes deben ser un array no vacío'];
    }
    
    foreach ($ingredientes as $ingrediente) {
        if (!isset($ingrediente['ingrediente_id']) || !isset($ingrediente['cantidad'])) {
            return ['success' => false, 'error' => 'Cada ingrediente debe tener ingrediente_id y cantidad'];
        }
        
        if (!validarIngredienteExiste($ingrediente['ingrediente_id'])) {
            return ['success' => false, 'error' => 'No existe un ingrediente con el ID proporcionado: ' . $ingrediente['ingrediente_id']];
        }
        
        if ($ingrediente['cantidad'] <= 0) {
            return ['success' => false, 'error' => 'La cantidad del ingrediente debe ser mayor a 0'];
        }
    }
    
    return ['success' => true];
}

/**
 * Valida que un array de etiquetas es válido
 * @param array $etiquetas Array de IDs de etiquetas
 * @return array Array con 'success' y 'error' si falla
 */
function validarEtiquetas($etiquetas) {
    if (!is_array($etiquetas)) {
        return ['success' => false, 'error' => 'Las etiquetas deben ser un array'];
    }
    
    foreach ($etiquetas as $etiquetaId) {
        if (!validarEtiquetaExiste($etiquetaId)) {
            return ['success' => false, 'error' => 'No existe una etiqueta con el ID proporcionado: ' . $etiquetaId];
        }
    }
    
    return ['success' => true];
}
