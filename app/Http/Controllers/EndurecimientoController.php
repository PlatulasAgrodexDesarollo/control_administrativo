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
    // 1. Traemos los endurecimientos activos
    $endurecimientos = DB::table('endurecimientos as e')
        ->leftJoin('operadores as o', 'e.Operador_Responsable', '=', 'o.ID_Operador')
        ->select('e.*', 'o.nombre as responsable_nombre')
        ->orderBy('e.Fecha_Ingreso', 'desc')
        ->get();

    // 2. Cargamos los detalles de cada uno
    foreach ($endurecimientos as $e) {
        $e->detalles = DB::table('endurecimiento_variedad as ev')
            ->join('variedades as v', 'ev.variedad_id', '=', 'v.ID_Variedad')
            ->join('llegada_planta as lp', 'ev.ID_llegada', '=', 'lp.ID_Llegada')
            ->where('ev.endurecimiento_id', $e->ID_Endurecimiento)
            ->select('v.nombre as var_nombre', 'v.codigo as var_codigo', 'ev.cantidad_plantas', 'lp.Fecha_Llegada', 'lp.ID_Llegada')
            ->get();
    }

    $ruta = route('dashboard'); 
    $texto_boton = "Regresar a Módulos";

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
    $aclimataciones_listas = DB::table('aclimatacion_variedad as av')
        ->join('aclimatacion as a', 'av.aclimatacion_id', '=', 'a.ID_Aclimatacion')
        ->join('variedades as v', 'av.variedad_id', '=', 'v.ID_Variedad')
        ->whereNotNull('av.fecha_finalizado_variedad')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('endurecimiento_variedad as ev')
                ->join('endurecimientos as e', 'ev.endurecimiento_id', '=', 'e.ID_Endurecimiento')
                ->whereRaw('ev.ID_llegada = av.ID_llegada')
                ->where('e.Estado_General', 'En Proceso');
        })
        ->select(
            'av.id_aclimatacion_va as pivot_id',
            'av.ID_llegada',
            'v.nombre as variedad_nombre',
            'v.codigo as variedad_codigo',
            'av.cantidad_plantas',
            'av.merma_acumulada_lote'
        )
        ->get()
        ->map(function ($item) {
            $loteOriginal = \App\Models\LlegadaPlanta::find($item->ID_llegada);
            
            $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            
            $nombre_lote = $loteOriginal ? $loteOriginal->nombre_lote_semana : "Lote #" . $item->ID_llegada;
            $nombre_lote_es = str_ireplace($meses_en, $meses_es, $nombre_lote);
            
            $obj = new \stdClass();
            $obj->pivot_id = $item->pivot_id;
            $obj->nombre_lote_semana = $nombre_lote_es;
            $obj->variedad_nombre = $item->variedad_nombre;
            $obj->variedad_codigo = $item->variedad_codigo;
            
            // ESTAS SON LAS PROPIEDADES QUE TU VISTA NECESITA:
            $obj->pivot_cantidad_inicial_lote = $item->cantidad_plantas;
            $obj->pivot_merma_acumulada_lote = $item->merma_acumulada_lote ?? 0;
            
            return $obj;
        });

    $operadores = \App\Models\Operador::where('estado', 1)->get(); 
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
        'aclimatacion_variedad_id' => 'required', 
        'Fecha_Ingreso' => 'required|date',
        'Operador_Responsable' => 'required'
    ]);

    DB::beginTransaction();
    try {
        // 1. Buscamos la variedad en el origen (Aclimatación)
        $fuente = DB::table('aclimatacion_variedad')
            ->where('id_aclimatacion_va', $request->aclimatacion_variedad_id)
            ->first();
        
        if (!$fuente) return back()->with('error', 'Origen no encontrado.');

        // NUEVO: Buscamos la merma inicial de plantación para guardarla físicamente
        $merma_inicial_p = DB::table('plantacion')
            ->where('ID_Llegada', $fuente->ID_llegada)
            ->where('ID_Variedad', $fuente->variedad_id)
            ->sum('cantidad_perdida');

        $stock_neto = $fuente->cantidad_plantas - ($fuente->merma_acumulada_lote ?? 0);

        // 2. BUSQUEDA EXACTA: 
        $registroExistente = DB::table('endurecimiento_variedad as ev')
            ->join('endurecimientos as e', 'ev.endurecimiento_id', '=', 'e.ID_Endurecimiento')
            ->where('ev.aclimatacion_id', $fuente->aclimatacion_id) 
            ->where('e.Estado_General', 'En Proceso')
            ->select('e.ID_Endurecimiento')
            ->first();

        if ($registroExistente) {
            $id_maestro = $registroExistente->ID_Endurecimiento;
            DB::table('endurecimientos')->where('ID_Endurecimiento', $id_maestro)->increment('cantidad_inicial', $stock_neto);
        } else {
            $id_maestro = DB::table('endurecimientos')->insertGetId([
                'Fecha_Ingreso'        => $request->Fecha_Ingreso,
                'cantidad_inicial'     => $stock_neto,
                'Operador_Responsable' => $request->Operador_Responsable,
                'Estado_General'       => 'En Proceso',
                'created_at'           => now(),
                'updated_at'           => now()
            ]);
        }

        // 3. INSERTAMOS EL DETALLE
        DB::table('endurecimiento_variedad')->updateOrInsert(
            [
                'endurecimiento_id' => $id_maestro,
                'variedad_id'       => $fuente->variedad_id,
                'ID_llegada'        => $fuente->ID_llegada,
            ],
            [
                'aclimatacion_id'           => $fuente->aclimatacion_id,
                'merma_inicial_plantacion'  => (int)$merma_inicial_p, 
                'merma_aclimatacion_pasada' => (int)($fuente->merma_acumulada_lote ?? 0),
                'cantidad_inicial_lote'     => $fuente->cantidad_plantas,
                'stock_entrada_etapa'       => $stock_neto,  
                'cantidad_plantas'          => $stock_neto,
                'merma_acumulada_lote'      => 0,
                'Estado_Lote'               => 'Normal',
                'created_at'                => now(),
                'updated_at'                => now()
            ]
        );

        DB::commit();
        return redirect()->route('endurecimiento.index')->with('success', 'Variedad integrada y datos de plantación guardados.');

    } catch (\Exception $e) {
        DB::rollBack();
        dd("Error: ", $e->getMessage());
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

public function finalizarVariedad(Request $request, $id)
{
    DB::beginTransaction();
    try {
        // Actualizamos la fila exacta
        DB::table('endurecimiento_variedad')
            ->where('endurecimiento_id', $id)
            ->where('ID_llegada', $request->id_llegada)
            ->where('variedad_id', $request->id_variedad)
            ->update([
                'merma_seleccion_final' => $request->merma_final_individual,
                'Estado_Lote'           => 'Finalizado',
                'fecha_finalizado'      => now(), // Detiene el contador
                'updated_at'            => now()  // Actualiza el registro
            ]);

        // Verificamos si ya podemos cerrar el endurecimiento completo
        $pendientes = DB::table('endurecimiento_variedad')
            ->where('endurecimiento_id', $id)
            ->where('Estado_Lote', '!=', 'Finalizado')
            ->count();

        if ($pendientes === 0) {
            DB::table('endurecimientos')
                ->where('ID_Endurecimiento', $id)
                ->update(['Estado_General' => 'Finalizado', 'Fecha_Cierre' => now()]);
        }

        DB::commit();
        return back()->with('success', 'Variedad cerrada. El contador se ha detenido.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }

}
}
