<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();
            
            // Verificar si el token ha expirado
            if ($token && $token->expires_at && $token->expires_at->isPast()) {
                $token->delete();
                
                return response()->json([
                    'message' => 'Token expirado. Por favor, inicia sesión nuevamente.'
                ], 401);
            }
        }

        return $next($request);
    }
} 