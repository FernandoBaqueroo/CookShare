<?php

use Illuminate\Support\Facades\Route;

// API pura - no hay rutas web
// Todas las rutas están en routes/api.php

Route::get('/', function () {
    return response()->json([
        'message' => 'CookShare API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});
