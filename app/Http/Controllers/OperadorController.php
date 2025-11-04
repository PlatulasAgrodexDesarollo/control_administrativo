<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperadorController extends Controller
{

    public function index()
    {
        $ruta = route('dashboard');
        $texto_boton = "Regresar al Menú Principal";
        $usuario_nombre = "Admin Invernadero";

        return view('operadores.index', compact('usuario_nombre'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Muestra el Formulario de Registro
    public function create()
    {
        $ruta = route('operadores.index');
        $texto_boton = "Menú de Operadores";

        return view('operadores.create')
            ->with(compact('ruta', 'texto_boton'));
    }

    // Guarda un Nuevo Operador
    public function store(Request $request)
    {

        $request->validate(['nombre' => 'required|string|max:100',]);

        Operador::create($request->all());

        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador registrado exitosamente.');
    }

    // Muestra la Tabla de Gestión
    public function listaoperadores()
    {
        $operadores = Operador::all();

        $ruta = route('operadores.index');
        $texto_boton = "Menú de Operadores";

        return view('operadores.listaoperadores', compact('operadores'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Muestra el Formulario de Edición
    public function edit(Operador $operador)
    {
        $ruta = route('operadores.listaoperadores');
        $texto_boton = "Volver a Gestión";

        return view('operadores.edit', compact('operador'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Actualiza el Operador en la BD
    public function update(Request $request, Operador $operador)
    {
        $request->validate(['nombre' => 'required|string|max:100',]);

        $operador->update($request->all());

        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador "' . $operador->nombre . '" actualizado exitosamente.');
    }

    public function destroy(Operador $operador)
    {
        $nombre_operador = $operador->nombre;

        // Desactivación Lógica: Cambia el estado a 0
        $operador->update(['estado' => 0]);

        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador "' . $nombre_operador . '" DESACTIVADO.');
    }

    // REACTIVAR (Llamado por el botón "Reactivar")
    public function reactivate(Operador $operador)
    {
        $operador->update(['estado' => 1]);

        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador "' . $operador->nombre . '" REACTIVADO exitosamente.');
    }

    //  Método para el Borrado Definitivo (Hard Delete)
    public function hardDelete(Operador $operador)
    {

        $nombre_operador = $operador->nombre;
        $id_operador = $operador->ID_Operador;

        try {
            // Deshabilitar temporalmente la verificación de FKs
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Ejecutar la eliminación física (Hard Delete)
            DB::table('operadores')->where('ID_Operador', $id_operador)->delete();

            // Volver a habilitar la verificación de FKs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('operadores.listaoperadores')
                ->with('success', 'Operador "' . $nombre_operador . '" ELIMINADO DEFINITIVAMENTE del sistema.');
        } catch (\Exception $e) {
            // En caso de un fallo inesperado (que no sea por FK)
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 

           
            $operador->update(['estado' => 0]);

            return redirect()->route('operadores.listaoperadores')
                ->with('error', 'ADVERTENCIA: Fallo crítico. El operador fue DESACTIVADO.');
        }
    }
}
