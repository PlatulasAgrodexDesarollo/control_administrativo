<?php

namespace App\Http\Controllers;

use App\Models\Aclimatacion;
use App\Models\Operador;
use App\Models\Plantacion;
use App\Models\Variedad;
use App\Models\LlegadaPlanta;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ChequeoHyT;
use App\Models\PerdidaInvernadero;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AclimatacionController extends Controller
{

    public function index()
    {

        $aclimataciones = Aclimatacion::with([
            'lotesAclimatados' => function ($query) {

                $query->withPivot('cantidad_plantas', 'Estado_Inicial_Lote', 'variedad_id');
            },
            'operadorResponsable'
        ])->get();

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

        return view('aclimatacion.index', compact('aclimataciones'))
            ->with(compact('ruta', 'texto_boton'));
    }

    public function create()
    {
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');
        Carbon::setLocale('es');
        $operadores = Operador::where('estado', 1)->get();
        $meses_espanol_abr = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic'
        ];

        $lotes_id_disponibles = [];
        $data_calculada = [];

        $registros_plantacion = Plantacion::with('loteLlegada.variedad')->get();
        $plantaciones_por_lote = $registros_plantacion->groupBy('ID_Llegada');


        foreach ($plantaciones_por_lote as $id_llegada => $registros_del_lote) {
            $lote_origen = $registros_del_lote->first()->loteLlegada;

            $total_sembrado_acumulado = $registros_del_lote->sum('cantidad_sembrada');
            $total_perdidas_acumulado = $registros_del_lote->sum('cantidad_perdida');
            $stock_recibido = $lote_origen->Cantidad_Plantas;

            $stock_disponible = $stock_recibido - ($total_sembrado_acumulado + $total_perdidas_acumulado);

            $lotes_id_disponibles[] = $id_llegada;
            $data_calculada[$id_llegada] = ['stock_disponible' => $stock_disponible];
        }


        $lotes_consolidados = LlegadaPlanta::with('variedad')
            ->whereIn('ID_Llegada', $lotes_id_disponibles)
            ->get();



        $all_variedades = Variedad::all();
        $stock_sembrado_map = [];

        foreach ($all_variedades as $v) {
            $total_sembrado_variedad = Plantacion::query()
                ->whereHas('loteLlegada', function ($q) use ($v) {
                    $q->where('ID_Variedad', $v->ID_Variedad);
                })
                ->sum('cantidad_sembrada');

            $stock_sembrado_map[$v->ID_Variedad] = $total_sembrado_variedad;
        }


        $lote_options_js = '<option value="">Seleccione variedad</option>';

        foreach ($lotes_consolidados as $lote) {

            $fecha_carbon = Carbon::parse($lote->Fecha_Ingreso);
            $fecha_espanol = $fecha_carbon->translatedFormat('M Y');
            $variedad_id = data_get($lote->variedad, 'ID_Variedad', '');
            $stock_final_correcto = $stock_sembrado_map[$variedad_id] ?? 0;


            if ($stock_final_correcto <= 0) {
                continue;
            }

            $variedad_nombre = data_get($lote->variedad, 'nombre', 'N/A');
            $variedad_codigo = data_get($lote->variedad, 'codigo', 'N/A');
            $nombre_lote_semana = $lote->nombre_lote_semana ?? 'N/A';


            $fecha_carbon = Carbon::parse($lote->Fecha_Ingreso);
            $abr_espanol = $meses_espanol_abr[$fecha_carbon->month];
            $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon->year;


            $patron_mes_anio = '/\b[A-Za-z]{3,}\s\d{4}\b/';


            $nombre_lote_semana = preg_replace(
                $patron_mes_anio,
                $fecha_espanol_manual,
                $nombre_lote_semana
            );
            $lote_options_js .= "<option value='{$lote->ID_Llegada}'"
                . " data-variedad-id='{$variedad_id}'"

                . " data-stock-disponible='{$stock_final_correcto}'"
                . " data-total-sembrado='{$stock_final_correcto}'>";

            $lote_options_js .= "{$nombre_lote_semana} - Var: {$variedad_nombre} [CÓDIGO: {$variedad_codigo}]";


            $lote_options_js .= " (Stock: {$stock_final_correcto})";
            $lote_options_js .= "</option>";
        }


        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver al Listado";


        return view('aclimatacion.create', compact('operadores', 'lotes_consolidados', 'data_calculada', 'all_variedades', 'stock_sembrado_map', 'lote_options_js'))
            ->with(compact('ruta', 'texto_boton'));
    }

    public function show(Aclimatacion $aclimatacion)
    {

        $aclimatacion->load([

            'lotesAclimatados' => function ($query) {
                $query->withPivot('cantidad_plantas', 'variedad_id', 'cantidad_inicial_lote','Estado_Inicial_Lote','merma_acumulada_lote');
            },
            'operadorResponsable',
            'chequeos.operadorResponsable'
        ]);


        $lotes_detallados = $aclimatacion->lotesAclimatados->map(function ($lote) use ($aclimatacion) {


            $cantidad_inicial = $lote->pivot->cantidad_inicial_lote;
            $cantidad_actual = $lote->pivot->cantidad_plantas;
            


            $merma_calculada = max(0, $cantidad_inicial - $cantidad_actual);

            return [
                'ID_Llegada' => $lote->ID_Llegada,
                'nombre' => $lote->nombre_lote_semana,
                'variedad_nombre' => $lote->variedad->nombre ?? 'N/A',
                'variedad_codigo' => $lote->variedad->codigo ?? 'N/A',
                'merma_acumulada_lote' => $lote->pivot->merma_acumulada_lote ?? 0,

                'cantidad_ingresada' => $cantidad_inicial,
                'stock_restante' => $cantidad_actual,


                'merma_lote_acumulada' => $merma_calculada,
            ];
        });


        $chequeos = $aclimatacion->chequeos;

        $ruta = route('aclimatacion.index');
        $texto_boton = "Regresar a Aclimatación";

        return view('aclimatacion.show', compact('aclimatacion', 'chequeos', 'lotes_detallados', 'ruta', 'texto_boton'));
    }

    public function cerrarEtapa(Request $request, Aclimatacion $aclimatacion)

    {

        $total_sembrado_acumulado = \App\Models\Plantacion::where('ID_Llegada', $aclimatacion->ID_Llegada)
            ->sum('cantidad_sembrada');
        $merma_acumulada = $aclimatacion->merma_etapa ?? 0;
        $cantidad_pasante = $total_sembrado_acumulado - $merma_acumulada;

        if ($cantidad_pasante < 0) {
            return redirect()->back()->with('error', 'Error crítico: La merma acumulada (' . number_format($merma_acumulada) . ') excede el stock sembrado total.');
        }

        $lote_llegada = $aclimatacion->loteLlegada;
        $lote_llegada->update([
            'Cantidad_Plantas' => $cantidad_pasante,
        ]);


        $aclimatacion->update([
            'fecha_cierre' => now(),
            'cantidad_final' => $cantidad_pasante,

        ]);

        return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
            ->with('success', '¡Etapa cerrada! El stock se actualizó con la merma acumulada.');
    }




    public function store(Request $request)
    {
        
        $request->validate([
            'Fecha_Ingreso'         => 'required|date',
            'Estado_Inicial'        => 'required|string|max:255',
            'Duracion_Aclimatacion' => 'required|integer|min:1',
            'Observaciones'         => 'nullable|string',
            'Operador_Responsable'  => 'required|integer|exists:operadores,ID_Operador',

            'lotes_a_mover'           => [
                'required',
                'array',
                'min:1',

                function ($attribute, $value, $fail) {
                    $combinations = [];
                    foreach ($value as $item) {

                        $key = $item['id_lote'] . '-' . $item['id_variedad'];
                        if (in_array($key, $combinations)) {
                            $fail('La combinación de Lote y Variedad (' . $item['id_lote'] . '/' . $item['id_variedad'] . ') no puede repetirse en la misma etapa.');
                        }
                        $combinations[] = $key;
                    }
                },
            ],
            'lotes_a_mover.*.id_lote' => 'required|integer|exists:llegada_planta,ID_Llegada',
            'lotes_a_mover.*.id_variedad' => 'required|integer|exists:variedades,ID_Variedad',
            'lotes_a_mover.*.cantidad'    => 'required|integer|min:1'
        ]);


        // 2. PREPARACIÓN DE DATOS Y VALIDACIÓN DE STOCK
        $total_ingresado_global = 0;

        foreach ($request->lotes_a_mover as $lote_a_mover) {
            $id_llegada = $lote_a_mover['id_lote'];
            $cantidad_a_mover = $lote_a_mover['cantidad'];
            $total_ingresado_global += $cantidad_a_mover;

            $llegada = LlegadaPlanta::find($id_llegada);

            if (!$llegada) {
                return redirect()->back()->withInput()->with('error', 'Lote de Llegada ID ' . $id_llegada . ' no encontrado.');
            }

         
            $registros_plantacion = Plantacion::where('ID_Llegada', $id_llegada)->get();
            $stock_recibido = $llegada->Cantidad_Plantas ?? 0;
            $total_movido_previo = $registros_plantacion->sum('cantidad_sembrada') + $registros_plantacion->sum('cantidad_perdida');
            $stock_disponible_calculado = $stock_recibido - $total_movido_previo;

            if ($cantidad_a_mover > $stock_disponible_calculado) {
                return redirect()->back()->withInput()->with('error', 'Error de Stock: La cantidad a aclimatar excede el stock disponible (' . $stock_disponible_calculado . ') para el Lote ID ' . $id_llegada);
            }
        }


     
        DB::beginTransaction();
        try {
       
            $aclimatacion = Aclimatacion::create([
                'Operador_Responsable' => $request->Operador_Responsable,
                'Fecha_Ingreso' => $request->Fecha_Ingreso,
                'Estado_Inicial' => $request->Estado_Inicial,
                'Duracion_Aclimatacion' => $request->Duracion_Aclimatacion,
                'Observaciones' => $request->Observaciones,
                'cantidad_final' => $total_ingresado_global,
                'merma_etapa' => 0,
            ]);

            $aclimatacionId = $aclimatacion->ID_Aclimatacion;


            $pivotDataToInsert = [];
            foreach ($request->lotes_a_mover as $lote) {
                $cantidad_a_mover = $lote['cantidad'];
                $estado_lote = $lote['estado_inicial'];
                $pivotDataToInsert[] = [
                    'aclimatacion_id' => $aclimatacionId,
                    'ID_llegada'      => $lote['id_lote'],
                    'variedad_id'     => $lote['id_variedad'],
                    'cantidad_plantas' => $cantidad_a_mover,
                    'cantidad_inicial_lote' => $cantidad_a_mover,
                    'Estado_Inicial_Lote' => $estado_lote,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            \Illuminate\Support\Facades\DB::table('aclimatacion_variedad')->insert($pivotDataToInsert);


            DB::commit();

            return redirect()->route('aclimatacion.index')->with('success', 'Etapa de Aclimatación iniciada correctamente con múltiples lotes.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fallo fatal al registrar la aclimatación: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Error fatal en la base de datos. Causa: ' . $e->getMessage());
        }


        $request->validate([
            'lote_id' => 'required|integer',
            'cantidad_merma' => 'required|integer|min:1',
        ]);

        if ($aclimatacion->fecha_cierre) {
            return redirect()->back()->with('error', 'No se puede registrar merma: La etapa ya fue cerrada.');
        }

        $loteId = $request->lote_id;
        $mermaReportada = $request->cantidad_merma;

        $pivotRow = DB::table('aclimatacion_variedad')
            ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
            ->where('ID_llegada', $loteId)
            ->first();

        if (!$pivotRow) {
            return redirect()->back()->with('error', 'Lote no encontrado o no asociado a esta etapa.');
        }

        $cantidadIngresada = $pivotRow->cantidad_plantas;


        $stockRestante = $cantidadIngresada - $mermaReportada;


        if ($stockRestante < 0) {
            return redirect()->back()->with('error', 'La merma reportada excede la cantidad restante del lote. Stock restante: ' . number_format($cantidadIngresada) . ' unidades.');
        }

        DB::beginTransaction();
        try {

            DB::table('aclimatacion_variedad')
                ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
                ->where('ID_llegada', $loteId)

                ->update(['cantidad_plantas' => $stockRestante]);


            $nuevaMermaAcumulada = ($aclimatacion->merma_etapa ?? 0) + $mermaReportada;
            $aclimatacion->update(['merma_etapa' => $nuevaMermaAcumulada]);

            DB::commit();

            return redirect()->back()->with('success', 'Merma registrada con éxito para el Lote ID ' . $loteId . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fallo al registrar merma por lote: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la merma: ' . $e->getMessage());
        }
    }

    public function storeChequeo(Request $request, Aclimatacion $aclimatacion)
{
    $request->validate([
        'Operador_Responsable' => 'required|exists:operadores,ID_Operador', 
        'Hr' => 'required|numeric|min:0',
        'Temperatura' => 'required|numeric|min:0',
        'Lux' => 'required|numeric|min:0',
        'Actividades' => 'nullable|string',
        'Observaciones' => 'nullable|string',
        'lotes_seleccionados' => 'required|array|min:1', 
        'lotes_seleccionados.*' => 'exists:llegada_planta,ID_Llegada',
    ]);

    $data_base = [
        'ID_Aclimatacion' => $aclimatacion->ID_Aclimatacion,
        'Hr' => $request->Hr,
        'Temperatura' => $request->Temperatura,
        'Lux' => $request->Lux,
        'Actividades' => $request->Actividades,
        'Observaciones' => $request->Observaciones,
        'Operador_Responsable' => $request->Operador_Responsable,
        'Fecha_Chequeo' => Carbon::now()->toDateString(), 
        'Hora_Chequeo' => Carbon::now()->toTimeString(),  
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];

 
    $registros_insertados = 0;
    foreach ($request->lotes_seleccionados as $id_lote) {
        $registro = $data_base;
        $registro['id_lote_llegada'] = $id_lote; 
        
     
        DB::table('aclimatacion_chequeos_ht')->insert($registro); 
        $registros_insertados++;
    }

    return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
        ->with('success', "Chequeo H/T registrado para {$registros_insertados} lote(s) con éxito.");
}
public function registrarMermaLote(Request $request, Aclimatacion $aclimatacion)
{
    $request->validate([
        'lote_id' => 'required|exists:llegada_planta,ID_Llegada', 
        'cantidad_merma' => 'required|integer|min:1',
    
    ]);

    
    $lote_pivot = DB::table('aclimatacion_variedad')
        ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
        ->where('ID_llegada', $request->lote_id)
        ->first();

    if (!$lote_pivot) {
        return back()->with('error', 'Error de trazabilidad: Lote no encontrado en esta etapa.');
    }
    

    $merma_acumulada_actual = $lote_pivot->merma_acumulada_lote ?? 0;
    $stock_actual = $lote_pivot->cantidad_plantas - $merma_acumulada_actual;
    
    if ($request->cantidad_merma > $stock_actual) {
        return back()->withErrors(['cantidad_merma' => 'La merma ingresada excede el stock restante de este lote (Stock actual: '.$stock_actual.' und.).']);
    }

   
    DB::table('aclimatacion_variedad')
        ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
        ->where('ID_llegada', $request->lote_id)
        ->increment('merma_acumulada_lote', $request->cantidad_merma);
        
    
    $nueva_merma_total = DB::table('aclimatacion_variedad')
        ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
        ->sum('merma_acumulada_lote');
        
    $aclimatacion->merma_etapa = $nueva_merma_total;
    $aclimatacion->save();

    return back()->with('success', "Merma de {$request->cantidad_merma} unidades registrada exitosamente para el lote.");
}
}
