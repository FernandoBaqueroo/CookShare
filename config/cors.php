<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    
    // Permitir orígenes específicos de Vercel y desarrollo local
    'allowed_origins' => [
        'http://localhost:5173',           // Vite dev local
        'http://localhost:3000',           // Alternativa dev local
        'https://*.vercel.app',            // Todos los dominios de Vercel (preview y producción)
        env('FRONTEND_URL', '*'),          // URL desde variable de entorno
    ],
    
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.vercel\.app$/',   // Pattern para dominios de Vercel
    ],
    
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];