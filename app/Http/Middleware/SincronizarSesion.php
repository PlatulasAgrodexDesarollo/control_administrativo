<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SincronizarSesion
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Buscamos la cookie que genera tu sistema de PHP puro
        $sessionId = $_COOKIE['PHPSESSID'] ?? null;

        if ($sessionId) {
            // 2. Buscamos al operador en la base de datos de Agrodex
            $operador = User::where('current_session_id', $sessionId)
                            ->where('Activo', 1) 
                            ->first();

            if ($operador) {
                // 3. Si existe, lo logueamos en Laravel automáticamente
                if (!Auth::check() || Auth::user()->ID_Operador !== $operador->ID_Operador) {
                    Auth::login($operador);
                }
                return $next($request);
            }
        }

 
        return redirect('http://localhost/AdministracionPlantulas/login.php');
    }
}