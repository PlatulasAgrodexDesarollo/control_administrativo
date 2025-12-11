<?php

namespace App\Http\Controllers;

use App\Models\ChequeoHyT;
use App\Models\Aclimatacion;
use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;

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
        
        
        $aclimatacion = Aclimatacion::with(['lotesAclimatados.variedad'])->find($aclimatacion_id); 
        
        
        if (!$aclimatacion) {
            return redirect()->route('aclimatacion.index')->with('error', 'Etapa de Aclimatación no encontrada.');
        }

     $lotes_aclimatados = $aclimatacion->lotesAclimatados; 

    $ruta = route('aclimatacion.show', $aclimatacion->ID_Aclimatacion);
    $texto_boton = "Volver a Gestión de Etapa";

    
    return view('chequeo_hyt.create', compact('operadores', 'aclimatacion', 'lotes_aclimatados'))
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
            'Hr' => 'nullable|numeric|min:0|max:100',
            'Lux' => 'nullable|integer|min:0',
            'Actividades' => 'required|string',
            'ID_Aclimatacion' => 'required|exists:aclimatacion,ID_Aclimatacion',
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
            'Observaciones' => 'nullable|string',
          
            'lotes_seleccionados' => 'required|array|min:1', 
            'lotes_seleccionados.*' => 'exists:llegada_planta,ID_Llegada',
        ]);

        $data_base = [
            'ID_Aclimatacion' => $request->ID_Aclimatacion,
            'Fecha_Chequeo' => $request->Fecha_Chequeo,
            'Hora_Chequeo' => $request->Hora_Chequeo,
            'Temperatura' => $request->Temperatura,
            'Hr' => $request->Hr,
            'Lux' => $request->Lux,
            'Actividades' => $request->Actividades,
            'Observaciones' => $request->Observaciones,
            'Operador_Responsable' => $request->Operador_Responsable,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        
        
        $registros_insertados = 0;
        foreach ($request->lotes_seleccionados as $id_lote) {
            $registro = $data_base;
            
            $registro['id_lote_llegada'] = $id_lote; 
            
           
            ChequeoHyT::create($registro); 
            $registros_insertados++;
        }

        return redirect()->route('chequeo_hyt.listado_aclimatacion', $request->ID_Aclimatacion)
            ->with('success', "Chequeo H/T registrado para {$registros_insertados} lote(s) exitosamente.");
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
    
    
public function listadoPorAclimatacion($aclimatacion_id)
{
    
    $aclimatacion = Aclimatacion::with(['operadorResponsable'])->findOrFail($aclimatacion_id);

    
    $chequeos = ChequeoHyT::where('ID_Aclimatacion', $aclimatacion_id)
        ->with(['operadorResponsable', 'loteLlegada.variedad'])
        ->orderBy('Fecha_Chequeo', 'desc')
        ->orderBy('Hora_Chequeo', 'desc')
        ->get();

    $ruta = route('aclimatacion.show', $aclimatacion->ID_Aclimatacion);
    $texto_boton = "Volver a Gestión de Etapa";

    return view('chequeo_hyt.index', compact('chequeos', 'aclimatacion'))
        ->with(compact('ruta', 'texto_boton'));
}
}