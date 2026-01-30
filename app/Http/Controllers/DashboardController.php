<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // Si el middleware por alguna razón no detecta al usuario, mandamos al login real
       if (!$usuario) {
    return redirect('http://localhost/AdministracionPlantulas/session/login.php');}

        return view('dashboard.index', [
            'encabezado' => "Panel de Control de Producción",
            'subtitulo' => "Módulo de Trazabilidad y Rendimiento",
            'usuario_nombre' => $usuario->Nombre, 
            'ruta' => route('logout'),
            'texto_boton' => "Cerrar Sesión"
        ]);
    }

   public function logout(Request $request)
{
    $usuario = Auth::user();

    if ($usuario) {
        // limpia la base de datos usando la conexión de Laravel
        DB::connection('mysql_principal')
            ->table('operadores')
            ->where('ID_Operador', $usuario->ID_Operador)
            ->update(['current_session_id' => null]);
    }

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

   
    return redirect('http://localhost/AdministracionPlantulas/session/logout.php');
}
}