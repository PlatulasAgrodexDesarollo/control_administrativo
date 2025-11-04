<?php

namespace App\Http\Controllers;

use App\Models\Variedad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariedadController extends Controller
{
    // Muestra el listado de Variedades
    public function index()
    {
        $variedades = Variedad::all();

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

        return view('variedades.index', compact('variedades'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Muestra el formulario para crear una nueva Variedad
    public function create()
    {
        $ruta = route('variedades.index');
        $texto_boton = "Volver al Catálogo";

        return view('variedades.create')
            ->with(compact('ruta', 'texto_boton'));
    }

    // Guarda el nuevo registro de variedad
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:variedades,nombre',
            'especie' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'codigo' => 'required|string|unique:variedades,codigo|max:20', 
       
        ]);

        Variedad::create($request->all());

        return redirect()->route('variedades.index')
            ->with('success', 'Variedad registrada exitosamente.');
    }

    // Muestra el formulario de edición
    public function edit(Variedad $variedad)
    {
        $ruta = route('variedades.index');
        $texto_boton = "Volver al Catálogo";

        return view('variedades.edit', compact('variedad'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Actualiza el registro
    public function update(Request $request, Variedad $variedad)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('variedades', 'nombre')->ignore($variedad->ID_Variedad, 'ID_Variedad')
            ],
            'especie' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('variedades', 'codigo')->ignore($variedad->ID_Variedad, 'ID_Variedad')
            ],
        
        ]);

        $variedad->update($request->all());

        return redirect()->route('variedades.index')
            ->with('success', 'Variedad actualizada exitosamente.');
    }

    // Implementación mínima de otros métodos
    public function show(Variedad $variedad)
    {
        return redirect()->route('variedades.edit', $variedad->ID_Variedad);
    }

    public function destroy(Variedad $variedad)
    {
        try {
            $variedad->delete();
            return redirect()->route('variedades.index')->with('success', 'Variedad eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('variedades.index')->with('error', 'ERROR: No se puede eliminar la variedad. Existen registros de actividad asociados.');
        }
    }
}
