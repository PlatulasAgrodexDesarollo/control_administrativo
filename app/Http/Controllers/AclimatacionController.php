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
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ];

            $lotes_id_disponibles = [];
            $data_calculada = [];

            $registros_plantacion = Plantacion::with('loteLlegada.variedad')->get();
            $plantaciones_por_lote = $registros_plantacion->groupBy('ID_Llegada');

            foreach ($plantaciones_por_lote as $id_llegada => $registros_del_lote) {
                $lote_origen = $registros_del_lote->first()->loteLlegada;
                $total_sembrado_acumulado = $registros_del_lote->sum('cantidad_sembrada');
                $total_perdidas_acumulado = $registros_del_lote->sum('cantidad_perdida');
                $stock_recibido = $lote_origen->Cantidad_Plants;

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
            $variedad_id = data_get($lote->variedad, 'ID_Variedad', '');

            // --- Sumar solo lo sembrado de ESTE lote específico ---
            $stock_lote_especifico = DB::table('plantacion')
                ->where('ID_Llegada', $lote->ID_Llegada)
                ->where('ID_Variedad', $variedad_id)
                ->sum('cantidad_sembrada') ?? 0;

            if ($stock_lote_especifico <= 0) {
                continue;
            }

            $variedad_nombre = data_get($lote->variedad, 'nombre', 'N/A');
            $variedad_codigo = data_get($lote->variedad, 'codigo', 'N/A');
            
            // Lógica de fecha real (Mantenida)
            $fecha_llegada_real = DB::table('llegada_planta')->where('ID_Llegada', $lote->ID_Llegada)->value('Fecha_Llegada');
            $fecha_carbon = \Carbon\Carbon::parse($fecha_llegada_real ?? $lote->Fecha_Ingreso);
            $fecha_espanol_manual = $meses_espanol_abr[$fecha_carbon->month] . ' ' . $fecha_carbon->year;

            $buscar = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $reemplazar = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $nombre_lote_base = str_replace($buscar, $reemplazar, $lote->nombre_lote_semana ?? 'N/A');
            $nombre_lote_final = preg_replace('/\s?\(.*\)/', " ($fecha_espanol_manual)", $nombre_lote_base);

            // Usamos $stock_lote_especifico en lugar del mapa global
            $lote_options_js .= "<option value='{$lote->ID_Llegada}'"
                . " data-variedad-id='{$variedad_id}'"
                . " data-stock-disponible='{$stock_lote_especifico}'"
                . " data-total-sembrado='{$stock_lote_especifico}'>";

            $lote_options_js .= "{$nombre_lote_final} - Var: {$variedad_nombre} [CÓDIGO: {$variedad_codigo}]";
            $lote_options_js .= " (Stock: {$stock_lote_especifico})";
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
                    ->withPivot('cantidad_plantas', 'variedad_id', 'cantidad_inicial_lote', 'Estado_Inicial_Lote', 'merma_acumulada_lote', 'merma_inicial_plantacion');
            },
            'operadorResponsable'
        ]);

        $lotes_detallados = $aclimatacion->lotesAclimatados->map(function ($lote) {
    
            $merma_plantacion = $lote->pivot->merma_inicial_plantacion ?? 0;
            $cantidad_inicial = $lote->pivot->cantidad_inicial_lote;
            $merma_aclim_acum = $lote->pivot->merma_acumulada_lote ?? 0;
            
            return [
                'ID_Llegada' => $lote->ID_Llegada,
                'nombre' => $lote->nombre_lote_semana,
                'variedad_nombre' => $lote->variedad->nombre ?? 'N/A',
                'variedad_codigo' => $lote->variedad->codigo ?? 'N/A',
                'merma_acumulada_lote' => $merma_aclim_acum, 
                'merma_inicial_plantacion' => $merma_plantacion, 
                'cantidad_ingresada' => $cantidad_inicial,
                'stock_restante' => $cantidad_inicial - $merma_aclim_acum,
            ];
        });

        $ruta = route('aclimatacion.index');
        $texto_boton = "Regresar a Aclimatación";

        return view('aclimatacion.show', compact('aclimatacion', 'lotes_detallados', 'ruta', 'texto_boton'));
    }
  public function cerrarEtapa(Request $request, Aclimatacion $aclimatacion) 
    {
        if ($aclimatacion->fecha_cierre) {
            return back()->with('error', 'Esta etapa ya está cerrada.');
        }

        DB::beginTransaction();
        try {
            $lotes = $aclimatacion->lotesAclimatados()->get();
            $total_merma = 0;
            $total_inicial = 0;

            foreach ($lotes as $lote) {
                $total_inicial += $lote->pivot->cantidad_inicial_lote;
                $total_merma += $lote->pivot->merma_acumulada_lote;
            }

            // El Stock Final es la resta exacta
            $cantidad_viva_final = $total_inicial - $total_merma;

            $aclimatacion->update([
                'fecha_cierre'   => Carbon::now(),
                'cantidad_final' => $cantidad_viva_final, 
                'merma_etapa'    => $total_merma,
            ]);

            DB::commit();
            return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)->with('success', 'Etapa cerrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cerrar: ' . $e->getMessage());
        }
    }




   public function store(Request $request)
    {
        $request->validate([
            'Fecha_Ingreso'         => 'required|date',
            'Duracion_Aclimatacion' => 'nullable|integer', 
            'Operador_Responsable'  => 'required|integer|exists:operadores,ID_Operador',
            'lotes_a_mover'         => 'required|array|min:1',
            'lotes_a_mover.*.id_lote'      => 'required|integer|exists:llegada_planta,ID_Llegada',
            'lotes_a_mover.*.id_variedad'  => 'required|integer|exists:variedades,ID_Variedad',
            'lotes_a_mover.*.cantidad'     => 'required|integer|min:1',
            'lotes_a_mover.*.estado_inicial' => 'required|string|max:50',
        ]);

        $total_ingresado_global = 0;
        foreach ($request->lotes_a_mover as $lote) {
            $total_ingresado_global += $lote['cantidad'];
        }

        DB::beginTransaction();
        try {
            $aclimatacion = Aclimatacion::create([
                'Operador_Responsable' => $request->Operador_Responsable,
                'Fecha_Ingreso'        => $request->Fecha_Ingreso,
                'Duracion_Aclimatacion' => $request->Duracion_Aclimatacion ?? 30, // Default 30 si viene vacío
                'Observaciones'        => $request->Observaciones,
                'cantidad_final'       => $total_ingresado_global,
                'merma_etapa'          => 0, 
            ]);

            foreach ($request->lotes_a_mover as $lote) {
                // RESCATE DE MERMA DE PLANTACIÓN (Consolidado de operadores)
                $m_plantacion = DB::table('plantacion')
                    ->where('ID_Llegada', $lote['id_lote'])
                    ->where('ID_Variedad', $lote['id_variedad'])
                    ->sum('cantidad_perdida');

                DB::table('aclimatacion_variedad')->insert([
                    'aclimatacion_id'          => $aclimatacion->ID_Aclimatacion,
                    'ID_llegada'               => $lote['id_lote'],
                    'variedad_id'              => $lote['id_variedad'],
                    'cantidad_plantas'         => $lote['cantidad'],
                    'cantidad_inicial_lote'    => $lote['cantidad'],
                    'Estado_Inicial_Lote'      => $lote['estado_inicial'],
                    'merma_inicial_plantacion' => (int)$m_plantacion, // GUARDADO FÍSICO
                    'merma_acumulada_lote'     => 0,
                    'created_at'               => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('aclimatacion.index')->with('success', 'Etapa iniciada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
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
