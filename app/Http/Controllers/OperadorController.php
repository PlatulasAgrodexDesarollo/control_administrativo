<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; 
class OperadorController extends Controller
{
    
    // INDEX: Muestra el Sub-Dashboard (Menú de Tarjetas)
    
    public function index()
    {
        $ruta = route('dashboard');
        $texto_boton = "Regresar al Menú Principal";
        $usuario_nombre = "Admin Invernadero";

        return view('operadores.index', compact('usuario_nombre'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // CREATE: Muestra el Formulario de Registro

    public function create()
    {
        $ruta = route('operadores.index');
        $texto_boton = "Menú de Operadores";

        return view('operadores.create')
            ->with(compact('ruta', 'texto_boton'));
    }

  
    //  STORE: Guarda un Nuevo Operador
 
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'puesto' => 'required|string|unique:operadores,identificacion|max:50',
            'observaciones' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['estado'] = 1; 

        Operador::create($data);

        return redirect()->route('operadores.listaoperadores') 
            ->with('success', 'Operador registrado exitosamente.');
    }

  
    //LISTADO: Muestra la Tabla de Gestión (ACTIVOS)

    public function listaoperadores()
    {
      
        $operadores = Operador::where('estado', 1)->get();

        $ruta = route('operadores.index');
        $texto_boton = "Menú de Operadores";

        return view('operadores.listaoperadores', compact('operadores'))
            ->with(compact('ruta', 'texto_boton'));
    }

   
    //EDIT: Muestra el Formulario de Edición

    public function edit(Operador $operador)
    {
        $ruta = route('operadores.listaoperadores');
        $texto_boton = "Volver a Gestión";

        return view('operadores.edit', compact('operador'))
            ->with(compact('ruta', 'texto_boton'));
    }

    
    //UPDATE: Actualiza el Operador en la BD

    public function update(Request $request, Operador $operador)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',

            'identificacion' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('operadores')->ignore($operador->id)
            ],
            'observaciones' => 'nullable|string',
            'estado' => 'required|boolean',
        ]);

        // Actualiza el registro con los nuevos datos (incluyendo el estado)
        $operador->update($request->all());

        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador "' . $operador->nombre . '" actualizado exitosamente.');
    }
    
    
    // 7. DESTROY: DESACTIVACIÓN LÓGICA (Llamado por la ruta DELETE)
    
    public function destroy(Operador $operador)
    {
        $nombre_operador = $operador->nombre;
        $id_operador = $operador->id;
        
        
        DB::table('operadores')
            ->where('id', $id_operador)
            ->update(['estado' => 0]);

     
        return redirect()->route('operadores.listaoperadores') 
            ->with('success', 'Operador "' . $nombre_operador . '" desactivado y movido al archivo.');
    }
    
  
    //REACTIVATE: REACTIVAR (Llamado por la ruta PUT)

    public function reactivate(Operador $operador)
    {
        
        $operador->update(['estado' => 1]); 
        
        return redirect()->route('operadores.listaoperadores')
            ->with('success', 'Operador "' . $operador->nombre . '" reactivado exitosamente.');
    }
    

    // ARCHIVO: Muestra la Tabla de Gestión (INACTIVOS)
    
    public function archivo()
    {
        
        $operadores = Operador::where('estado', 0)->get();

        $ruta = route('operadores.index');
        $texto_boton = "Menú de Operadores";

        return view('operadores.archivo', compact('operadores'))
            ->with(compact('ruta', 'texto_boton'));
    }

    
    // HARDDELETE: Eliminacion permanente del los datos administrativos

    public function hardDelete(Operador $operador)
    {
        try {
            $nombre_operador = $operador->nombre;
           
            $operador->forceDelete(); 

            return redirect()->route('operadores.archivo')
                ->with('success', 'Operador "' . $nombre_operador . '" ELIMINADO PERMANENTEMENTE. Se borraron los datos administrativos.');
        } catch (\Illuminate\Database\QueryException $e) {
         
            return redirect()->route('operadores.archivo')
                ->with('error', 'ERROR: No se puede borrar al operador. Aún tiene registros de actividad (Plantación, Chequeos, etc.) asociados en el sistema.');
        }
    }
    public function show(Operador $operador)
    {
        
     return redirect()->route('operadores.edit', $operador->id);
    }
}