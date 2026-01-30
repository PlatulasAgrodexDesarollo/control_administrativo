<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plantacion;
use App\Models\Variedad;
use App\Models\LlegadaPlanta;
use Carbon\Carbon;use App\Models\Operador;


class ReporteController extends Controller
{
public function reporteMensual(Request $request)
{
    $mes = $request->get('mes', date('m'));
    $anio = $request->get('anio', date('Y'));
    $nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    $lotesMes = DB::table('llegada_planta as lp')
        ->join('variedades as v', 'lp.ID_Variedad', '=', 'v.ID_Variedad')
        ->select('lp.ID_Llegada', 'v.ID_Variedad', 'v.nombre as variedad', 'v.codigo', 'v.color', 'lp.cantidad_plantas')
        ->whereMonth('lp.fecha_llegada', $mes)
        ->whereYear('lp.fecha_llegada', $anio)
        ->get();

    $reporte = $lotesMes->map(function($lote) {
        $id_llegada = $lote->ID_Llegada;

        $fecha_inicio = DB::table('plantacion')
            ->where('ID_Llegada', $id_llegada)
            ->where('ID_Variedad', $lote->ID_Variedad)
            ->min('Fecha_Plantacion');

        $coloresCss = [
            'ROJO' => 'red', 'AZUL' => 'blue', 'VERDE' => 'green', 'AMARILLO' => 'yellow',
            'NARANJA' => 'orange', 'ROSA' => 'pink', 'MORADO' => 'purple', 'FUCSIA' => '#FF00FF',
            'CORAL' => '#FF7F50', 'BLANCO' => '#ffffff', 'NEGRO' => '#000000', 'GRIS' => 'gray',
            'CAFE' => 'brown', 'CAFÉ' => 'brown'  
        ];
        $colorFinal = $coloresCss[strtoupper(trim($lote->color))] ?? $lote->color;

        $m_plant = DB::table('plantacion')->where('ID_Llegada', $id_llegada)->sum('cantidad_perdida') ?? 0;
        $m_recuperada = DB::table('recuperacion_mermas')->where('ID_Llegada', $id_llegada)->sum('Cantidad_Recuperada') ?? 0;
        $total_sembrado = DB::table('plantacion')->where('ID_Llegada', $id_llegada)->sum('cantidad_sembrada') ?? 0;
        $m_aclim = DB::table('aclimatacion_variedad')->where('ID_llegada', $id_llegada)->sum('merma_acumulada_lote') ?? 0;
        
        $endur = DB::table('endurecimiento_variedad')->where('ID_llegada', $id_llegada)
            ->select(DB::raw('SUM(merma_acumulada_lote) as m_e, SUM(merma_seleccion_final) as m_s'))->first();

        $m_e = $endur->m_e ?? 0;
        $m_s = $endur->m_s ?? 0;

        $mermas_totales = $m_plant + $m_aclim + $m_e + $m_s;
        $saldo_final = ($lote->cantidad_plantas - $mermas_totales) + $m_recuperada;

        return (object)[
            'variedad' => $lote->variedad,
            'codigo' => $lote->codigo,
            'color' => $colorFinal,
            'fecha_inicio' => $fecha_inicio,
            'total_ingreso' => $lote->cantidad_plantas,
            'm_plant' => $m_plant,
            'm_recuperada' => $m_recuperada,
            'total_sembrado' => $total_sembrado,
            'm_aclim' => $m_aclim,
            'm_endur' => $m_e,
            'm_selec' => $m_s,
            'saldo_neto' => $saldo_final
        ];
    });

    // 1. Buscamos la fecha más antigua de cada variedad
    $agrupado = $reporte->groupBy('variedad');
    
    $variedadesOrdenadas = $agrupado->map(function($items) {
        return $items->min('fecha_inicio') ?? '9999-12-31';
    })->sort(); // Esto ordena las VARIEDADES por su fecha más vieja

    $reporteFinal = collect();

    // 2. Construimos la lista final recorriendo las variedades en el orden de su fecha
    foreach ($variedadesOrdenadas as $nombreVariedad => $fechaReferencia) {
        $lotesDeEstaVariedad = $agrupado->get($nombreVariedad)->sortBy('fecha_inicio');
        foreach ($lotesDeEstaVariedad as $item) {
            $reporteFinal->push($item);
        }
    }
    
    $reporte = $reporteFinal; 

    $resumenVariedades = $reporte->groupBy('variedad')->map(function ($items, $nombre) {
        return (object)[
            'variedad' => $nombre,
            'codigo' => $items->first()->codigo,
            'color' => $items->first()->color,
            'total_ingreso' => $items->sum('total_ingreso'),
            'm_plant' => $items->sum('m_plant'),
            'm_recuperada' => $items->sum('m_recuperada'),
            'total_sembrado' => $items->sum('total_sembrado'),
            'm_aclim' => $items->sum('m_aclim'),
            'm_endur' => $items->sum('m_endur'),
            'm_selec' => $items->sum('m_selec'),
            'saldo_neto' => $items->sum('saldo_neto'),
            'lotes_contados' => $items->count()
        ];
    });

    $totales = [
        'ingreso' => $reporte->sum('total_ingreso'),
        'm_plant' => $reporte->sum('m_plant'),
        'sembrado' => $reporte->sum('total_sembrado'),
        'm_aclim' => $reporte->sum('m_aclim'),
        'm_endur' => $reporte->sum('m_endur'),
        'm_selec' => $reporte->sum('m_selec'),
        'saldo' => $reporte->sum('saldo_neto'), 
    ];

    $ruta = route('dashboard');
    $texto_boton = "Regresar a Módulos";

    return view('reportes.mensual', compact('reporte', 'resumenVariedades', 'totales', 'mes', 'anio', 'ruta', 'texto_boton', 'nombresMeses'));
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
        $variedad_id = data_get($lote->variedad, 'ID_Variedad', '');
        $stock_final_correcto = $stock_sembrado_map[$variedad_id] ?? 0;

        if ($stock_final_correcto <= 0) {
            continue;
        }

        $variedad_nombre = data_get($lote->variedad, 'nombre', 'N/A');
        $variedad_codigo = data_get($lote->variedad, 'codigo', 'N/A');

        // --- INICIO DEL CAMBIO SOLICITADO ---
        // Buscamos la fecha de origen real para que coincida con el mes de plantación
        $fecha_llegada_referencia = DB::table('llegada_planta')->where('ID_Llegada', $lote->ID_Llegada)->value('Fecha_Llegada');
        $fecha_carbon = Carbon::parse($fecha_llegada_referencia ?? $lote->Fecha_Ingreso);
        
        $abr_espanol = $meses_espanol_abr[$fecha_carbon->month];
        $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon->year;

        // Reemplazo del mes y año dentro del paréntesis respetando el nombre original del lote
        $nombre_lote_semana = preg_replace(
            '/\(.*\)/',
            "($fecha_espanol_manual)",
            $lote->nombre_lote_semana ?? 'N/A'
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
}