<?php

namespace App\Http\Controllers;

use App\Models\Aclimatacion;
use App\Models\Operador;
use App\Models\Plantacion;
use Illuminate\Http\Request;


class AclimatacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aclimataciones = \App\Models\Aclimatacion::with([
            'plantacion',
            'variedad',
            'operadorResponsable'
        ])->get();

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

       
        return view('aclimatacion.index', compact('aclimataciones'))
            ->with(compact('ruta', 'texto_boton'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $operadores = Operador::where('estado', 1)->get();


        $plantaciones = Plantacion::with('variedad')->get();

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

        return view('aclimatacion.create', compact('operadores', 'plantaciones'))
            ->with(compact('ruta', 'texto_boton'));
    }

    // Guarda el registro
    public function store(Request $request)
    {
        $request->validate([
            'Fecha_Ingreso' => 'required|date',
            'Estado_Inicial' => 'required|string|max:50',
            'Duracion_Aclimatacion' => 'required|integer|min:1',
            'Observaciones' => 'nullable|string',
            'ID_Plantacion' => 'required|exists:plantacion,ID_Plantacion',
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
        ]);

    
        $plantacion = Plantacion::find($request->ID_Plantacion);

        $data = $request->all();
        $data['ID_Variedad'] = $plantacion->ID_Variedad;

        Aclimatacion::create($data);

        return redirect()->route('aclimatacion.index')
            ->with('success', 'Etapa de Aclimatación registrada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aclimatacion $aclimatacion)
    {
        $aclimatacion->load(['plantacion.variedad', 'operadorResponsable']);

        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver al Listado";

      
        return view('aclimatacion.show', compact('aclimatacion'))
            ->with(compact('ruta', 'texto_boton'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
