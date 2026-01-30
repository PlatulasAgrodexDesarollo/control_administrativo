<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    /**
     * Verifica si el usuario tiene el rol necesario para acceder.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
{
    // 1. Verificamos si está logueado
    if (!Auth::check()) {
        abort(403, 'Sesión no iniciada.');
    }

    // 2. Revisamos si el ID_Rol del usuario está dentro de la lista permitida
   
    if (in_array(Auth::user()->ID_Rol, $roles)) {
        return $next($request);
    }

    
    abort(403, 'No tienes permisos para acceder a esta sección del módulo de plántulas.');
}
}