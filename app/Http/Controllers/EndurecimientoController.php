<?php

namespace App\Http\Controllers;

use App\Models\Endurecimiento;
use App\Models\Aclimatacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Operador;


class EndurecimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cargamos los datos con sus relaciones
        $endurecimientos = Endurecimiento::with(['responsable', 'lotes'])
            ->orderBy('Fecha_Ingreso', 'desc')
            ->get();

        $ruta = route('dashboard'); 
        $texto_boton = "Regresar a Módulos";

        // ENVIAMOS LAS VARIABLES A LA VISTA
        return view('endurecimiento.index', compact('endurecimientos', 'ruta', 'texto_boton'));
    }

    // Lógica para iniciar endurecimiento desde una Aclimatación Cerrada
    public function iniciarDesdeAclimatacion(Aclimatacion $aclimatacion)
    {
        DB::beginTransaction();
        try {
            // 1. Crear la cabecera de Endurecimiento
            $endurecimiento = Endurecimiento::create([
                'Fecha_Ingreso' => now(),
                'cantidad_inicial' => $aclimatacion->cantidad_final, // Lo que salió de aclimatación
                'Operador_Responsable' => $aclimatacion->Operador_Responsable,
                'Estado_General' => 'En Proceso'
            ]);

            // 2. Traspasar cada lote de la pivote de Aclimatación a la pivote de Endurecimiento
            foreach ($aclimatacion->lotesAclimatados as $lote) {
                $endurecimiento->lotes()->attach($lote->ID_Llegada, [
                    'variedad_id' => $lote->pivot->variedad_id,
                    'cantidad_inicial_lote' => $lote->pivot->cantidad_plantas, // Stock actual
                    'cantidad_plantas' => $lote->pivot->cantidad_plantas,      // Inicia igual
                    'merma_acumulada_lote' => 0
                ]);
            }

            DB::commit();
            return redirect()->route('endurecimiento.show', $endurecimiento->ID_Endurecimiento)
                ->with('success', 'Etapa de Endurecimiento iniciada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al traspasar datos: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $aclimataciones_listas = \App\Models\Aclimatacion::whereNotNull('fecha_cierre')
            ->with('lotesAclimatados')
            ->get()
            ->map(function ($acli) {
                // Agrupamos nombres únicos para evitar repeticiones como "Lote 2, Lote 2"
                $nombresUnicos = $acli->lotesAclimatados->pluck('nombre_lote_semana')->unique()->implode(', ');

                // Limitamos a 25 caracteres para móviles (muy corto para que no falle)
                $acli->nombre_corto = \Illuminate\Support\Str::limit($nombresUnicos, 25);
                return $acli;
            });

        $operadores = \App\Models\Operador::all();
        $ruta = route('endurecimiento.index');
        $texto_boton = "Volver al listado";

        return view('endurecimiento.create', compact('aclimataciones_listas', 'operadores', 'ruta', 'texto_boton'));
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    $request->validate([
        'aclimatacion_id' => 'required',
        'Fecha_Ingreso' => 'required|date',
        'Operador_Responsable' => 'required'
    ]);

    DB::beginTransaction();
    try {
        // 1. Buscamos la aclimatación de origen
        $acli = \App\Models\Aclimatacion::with('lotesAclimatados')->find($request->aclimatacion_id);

        if (!$acli) {
            return back()->with('error', 'No se encontró la etapa de aclimatación de origen.');
        }

        $total_entrada_maestra = (int) $acli->cantidad_final; 

        // 3. Crear cabecera de Endurecimiento con el DATO MAESTRO
        $endurecimiento = \App\Models\Endurecimiento::create([
            'Fecha_Ingreso'        => $request->Fecha_Ingreso,
            'cantidad_inicial'     => $total_entrada_maestra, // <--- IGUAL AL INDEX
            'Operador_Responsable' => $request->Operador_Responsable,
            'Estado_General'       => 'En Proceso',
            'Observaciones'        => $request->Observaciones
        ]);

        // 4. Guardar el detalle de cada lote
        foreach ($acli->lotesAclimatados as $lote) {
            
            // Buscamos los datos del pivot de origen para las mermas pasadas
            $datos_acli_pivot = DB::table('aclimatacion_variedad')
                ->where('aclimatacion_id', $acli->ID_Aclimatacion)
                ->where('ID_llegada', $lote->ID_Llegada)
                ->where('variedad_id', $lote->pivot->variedad_id)
                ->first();

            // Sumatoria de mermas de operadores en plantación
            $m_plantacion_total = DB::table('plantacion')
                ->where('ID_Llegada', $lote->ID_Llegada)
                ->where('ID_Variedad', $lote->pivot->variedad_id)
                ->sum('cantidad_perdida');

            // CÁLCULO DEL STOCK FINAL DEL LOTE (Igual a la lógica de tu variedades_resumen del Index)
            $cant_orig_lote = $datos_acli_pivot->cantidad_inicial_lote ?? 0;
            $m_aclim_lote   = $datos_acli_pivot->merma_acumulada_lote ?? 0;
            
            // Stock Neto del lote = Inicial - Merma Acumulada
            $stock_final_lote_real = $cant_orig_lote - $m_aclim_lote;

            // 5. INSERTAR EN LA BASE DE DATOS
            DB::table('endurecimiento_variedad')->insert([
                'endurecimiento_id'         => $endurecimiento->ID_Endurecimiento,
                'ID_llegada'                => $lote->ID_Llegada,
                'variedad_id'               => $lote->pivot->variedad_id,
                'merma_inicial_plantacion'  => (int) $m_plantacion_total,
                'merma_aclimatacion_pasada' => (int) $m_aclim_lote,
                'cantidad_inicial_lote'     => $cant_orig_lote,
                'stock_entrada_etapa'       => $stock_final_lote_real,  
                'cantidad_plantas'          => $stock_final_lote_real,
                'merma_acumulada_lote'      => 0,
                'Estado_Lote'               => 'Normal'
            ]);
        }

        DB::commit();
        return redirect()->route('endurecimiento.index')->with('success', 'Traspaso exitoso: Datos sincronizados con Aclimatación.');

    } catch (\Exception $e) {
        DB::rollBack();
        dd("Error técnico: " . $e->getMessage());
    }
}
    /**
     * Show the form for editing the specified resource.
     */
  public function show($id)
{
    $endurecimiento = \App\Models\Endurecimiento::with('responsable')->findOrFail($id);

    // CRÍTICO: Añadimos withPivot para que la vista pueda leer merma_seleccion_final
    $lotes_detallados = $endurecimiento->lotes()
        ->with('variedad')
        ->withPivot(
            'cantidad_inicial_lote', 
            'merma_aclimatacion_pasada', 
            'merma_acumulada_lote', 
            'merma_seleccion_final' // <-- Este es el que te faltaba
        )
        ->get();

    $fecha_inicio = \Carbon\Carbon::parse($endurecimiento->Fecha_Ingreso);
    $fecha_fin = $endurecimiento->Fecha_Cierre ? \Carbon\Carbon::parse($endurecimiento->Fecha_Cierre) : now();
    $dias_transcurridos = $fecha_inicio->diffInDays($fecha_fin);

    $total_entrada_sincronizado = $lotes_detallados->sum(function($lote) {
        $inicial = $lote->pivot->cantidad_inicial_lote ?? 0;
        $merma_anterior = $lote->pivot->merma_aclimatacion_pasada ?? 0;
        return $inicial - $merma_anterior;
    });

    $ruta = route('endurecimiento.index');
    $texto_boton = "Regresar a Listado";

    return view('endurecimiento.show', compact(
        'endurecimiento',
        'lotes_detallados',
        'total_entrada_sincronizado', 
        'dias_transcurridos',
        'ruta',
        'texto_boton'
    ));
}
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
    public function registrarMerma(Request $request, $id)
{
    $request->validate([
        'id_llegada' => 'required',
        'id_variedad' => 'required',
        'cantidad_merma' => 'required|integer|min:1',
    ]);

    $pivot = DB::table('endurecimiento_variedad')
        ->where('endurecimiento_id', $id)
        ->where('ID_llegada', $request->id_llegada)
        ->where('variedad_id', $request->id_variedad)
        ->first();

    if ($pivot) {
        $nueva_merma = ($pivot->merma_acumulada_lote ?? 0) + $request->cantidad_merma;

        DB::table('endurecimiento_variedad')
            ->where('endurecimiento_id', $id)
            ->where('ID_llegada', $request->id_llegada)
            ->where('variedad_id', $request->id_variedad)
            ->update([
                'merma_acumulada_lote' => $nueva_merma,
                
            ]);

        return redirect()->back()->with('success', 'Merma actualizada correctamente.');
    }

    return redirect()->back()->with('error', 'Lote no encontrado.');
}
public function finalizarEtapa(Request $request, $id)
{
    $id = (int) $id;

    DB::beginTransaction();
    try {
        
        if ($request->has('merma_final')) {
            foreach ($request->merma_final as $lote_id => $variedades) {
                foreach ($variedades as $variedad_id => $cantidad) {
                    DB::table('endurecimiento_variedad')
                        ->where('endurecimiento_id', $id)
                        ->where('ID_llegada', $lote_id)
                        ->where('variedad_id', $variedad_id)
                        ->update([
                            'merma_seleccion_final' => (int)$cantidad, 
                            'Estado_Lote' => 'Finalizado'
                        ]);
                }
            }
        }

    
        $lotes_pivote = DB::table('endurecimiento_variedad')
            ->where('endurecimiento_id', $id)
            ->get();

        $stock_general_planta = 0;

        foreach($lotes_pivote as $lote) {
            
            $datos_acli = DB::table('aclimatacion_variedad')
                ->where('ID_llegada', $lote->ID_llegada)
                ->where('variedad_id', $lote->variedad_id)
                ->first();

            $entrada_neta = $datos_acli ? ($datos_acli->cantidad_inicial_lote - $datos_acli->merma_acumulada_lote) : 0;
            
            $merma_actual_proceso = $lote->merma_acumulada_lote ?? 0;
            
            $merma_final_seleccion = $lote->merma_seleccion_final ?? 0;

            $stock_general_planta += ($entrada_neta - ($merma_actual_proceso + $merma_final_seleccion));
        }

        $merma_total_etapa = $lotes_pivote->sum('merma_acumulada_lote') + $lotes_pivote->sum('merma_seleccion_final');

        DB::table('endurecimientos')
            ->where('ID_Endurecimiento', $id)
            ->update([
                'Estado_General'    => 'Finalizado',
                'Fecha_Cierre'      => now(),
                'cantidad_final'    => (int) $stock_general_planta, 
                'merma_total_etapa' => (int) $merma_total_etapa,
                'updated_at'        => now()
            ]);

        DB::commit();
        return redirect()->route('endurecimiento.index')
            ->with('success', 'Etapa finalizada. Stock guardado: ' . number_format($stock_general_planta));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al procesar el cierre: ' . $e->getMessage());
    }
}
}
