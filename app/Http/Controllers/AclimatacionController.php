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
            $query->with('variedad') 
                ->withPivot('cantidad_plantas', 'variedad_id', 'cantidad_inicial_lote', 'Estado_Inicial_Lote', 'merma_acumulada_lote');
        },
        'operadorResponsable',
        'chequeos.operadorResponsable'
    ]);

  
    $primer_chequeo = $aclimatacion->chequeos()->oldest('Fecha_Chequeo')->first();
    if ($primer_chequeo) {
        $aclimatacion->fecha_primer_registro_curso = $primer_chequeo->Fecha_Chequeo;
    }

    $lotes_detallados = $aclimatacion->lotesAclimatados->map(function ($lote) use ($aclimatacion) {
        
   
        $merma_plantacion = \App\Models\Plantacion::where('ID_Llegada', $lote->ID_Llegada)->sum('cantidad_perdida') ?? 0;

        
        $cantidad_inicial = $lote->pivot->cantidad_inicial_lote;
        $cantidad_actual = $lote->pivot->cantidad_plantas;
        $merma_acumulada_aclimatacion = $lote->pivot->merma_acumulada_lote ?? 0;
        $merma_calculada = max(0, $cantidad_inicial - $cantidad_actual);

   
        return [
            'ID_Llegada' => $lote->ID_Llegada,
            'nombre' => $lote->nombre_lote_semana,
            'Fecha_Ingreso' => $lote->Fecha_Ingreso, 
            'variedad_nombre' => $lote->variedad->nombre ?? 'N/A',
            'variedad_codigo' => $lote->variedad->codigo ?? 'N/A',
            'merma_acumulada_lote' => $merma_acumulada_aclimatacion, 
            
            
            'merma_inicial_plantacion' => $merma_plantacion, 

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
    if ($aclimatacion->fecha_cierre) {
        return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
                         ->with('error', 'Esta etapa de aclimatación ya ha sido cerrada previamente.');
    }

    DB::beginTransaction();

    try {
        $stock_inicial_total = 0;
        $merma_acumulada_total = 0;
        $inventario_lotes_detalle = []; 

       
        $lotes = $aclimatacion->lotesAclimatados()->withPivot('cantidad_inicial_lote', 'merma_acumulada_lote')->get();

        foreach ($lotes as $lote) {
            $cantidad_ingresada = $lote->pivot->cantidad_inicial_lote ?? 0; 
            $merma_lote = $lote->pivot->merma_acumulada_lote ?? 0;
            
            $stock_restante = $cantidad_ingresada - $merma_lote;

            $stock_inicial_total += $cantidad_ingresada;
            $merma_acumulada_total += $merma_lote;
            

            if ($stock_restante > 0) {
                $nombre_lote = $lote->nombre_lote_semana ?? "Lote ID: {$lote->ID_Llegada}";
                $inventario_lotes_detalle[] = "{$nombre_lote} ({$stock_restante} und.)";
            }
        }
        
        $inventario_pasante_calculado = $stock_inicial_total - $merma_acumulada_total;

        
        $aclimatacion->fecha_cierre = Carbon::now();
        $aclimatacion->cantidad_final = $inventario_pasante_calculado; 
        $aclimatacion->merma_etapa = $merma_acumulada_total;
        $aclimatacion->save();

        DB::commit();

        
        $mensaje_general = "¡Etapa de Aclimatación finalizada con éxito!";
        $mensaje_total = "Stock Total Pasante: **" . number_format($inventario_pasante_calculado) . " unidades**.";
        
        if (!empty($inventario_lotes_detalle)) {
            $detalle = "Inventario por lote: " . implode(', ', $inventario_lotes_detalle);
        } else {
            $detalle = "No quedó inventario pasante en ningún lote.";
        }
        
        $mensaje_final = $mensaje_general . "\n" . $mensaje_total . "\n" . $detalle;


    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al cerrar la etapa de Aclimatación: ' . $e->getMessage()); 
        
        return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
                         ->with('error', 'Hubo un error al intentar cerrar la etapa. Por favor, contacte al administrador.');
    }

    return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
                     ->with('success', $mensaje_final);
}



    public function store(Request $request)
    {

        $request->validate([
            'Fecha_Ingreso'         => 'required|date',
            
            'Duracion_Aclimatacion' => 'required|integer|min:1',
            'Observaciones'         => 'nullable|string',
            'Operador_Responsable'  => 'required|integer|exists:operadores,ID_Operador',

            'lotes_a_mover'         => [
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
            'lotes_a_mover.*.id_lote'       => 'required|integer|exists:llegada_planta,ID_Llegada',
            'lotes_a_mover.*.id_variedad'   => 'required|integer|exists:variedades,ID_Variedad',
            'lotes_a_mover.*.cantidad'      => 'required|integer|min:1',
        
            'lotes_a_mover.*.estado_inicial' => 'required|string|max:50',
        ]);


       
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
                    'merma_acumulada_lote' => 0,
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
            return back()->withErrors(['cantidad_merma' => 'La merma ingresada excede el stock restante de este lote (Stock actual: ' . $stock_actual . ' und.).']);
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
