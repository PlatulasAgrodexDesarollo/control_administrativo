<?php

namespace App\Http\Controllers;

use App\Models\ChequeoHyT;
use App\Models\Aclimatacion;
use App\Models\Operador;
use Illuminate\Http\Request;

class ChequeoHyTController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($aclimatacion_id)
    {
        $operadores = Operador::where('estado', 1)->get(); 
        $aclimatacion = Aclimatacion::find($aclimatacion_id); 

        if (!$aclimatacion) {
            return redirect()->route('aclimatacion.index')->with('error', 'Etapa de Aclimatación no encontrada.');
        }

        $ruta = route('aclimatacion.show', $aclimatacion->ID_Aclimatacion);
        $texto_boton = "Volver a Gestión de Etapa";

        return view('chequeo_hyt.create', compact('operadores', 'aclimatacion'))
            ->with(compact('ruta', 'texto_boton'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Fecha_Chequeo' => 'required|date',
            'Hora_Chequeo' => 'required', 
            'Temperatura' => 'required|numeric',
            'Hr' => 'nullable|numeric', 
        'Lux' => 'nullable|integer|min:0',
            'Actividaes' => 'required|string',
            'ID_Aclimatacion' => 'required|exists:aclimatacion,ID_Aclimatacion',
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
            'Observaciones' => 'nullable|string',
        ]);

       

        ChequeoHyT::create($request->all());

      
        return redirect()->route('aclimatacion.show', $request->ID_Aclimatacion)
            ->with('success', 'Chequeo H/T registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
