<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Para rutas API, solo usar autenticación por token
        if ($request->expectsJson()) {
            // Verificar si hay un token Bearer
            if ($request->bearerToken()) {
                // Intentar autenticar con Sanctum
                if (Auth::guard('sanctum')->check()) {
                    return $next($request);
                }
            }
            
            // Si no hay token válido, devolver 401
            return response()->json([
                'message' => 'Token de autenticación requerido'
            ], 401);
        }
        
        // Para otras rutas, usar el comportamiento normal
        return $next($request);
    }
} 