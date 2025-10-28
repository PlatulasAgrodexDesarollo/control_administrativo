<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        
        $encabezado = "Panel de Control de Producción";
        $subtitulo = "Seleccione un módulo para comenzar la gestión.";

        $ruta = route('login');
        $texto_boton = "Cerrar Sesión";

        
        $usuario_nombre = "Admin Invernadero";

        return view('dashboard.index', compact('encabezado', 'subtitulo', 'usuario_nombre'))
            ->with(compact('ruta', 'texto_boton'));
    }
}
