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


class AclimatacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()

    {
        $aclimataciones = Aclimatacion::with(['loteLlegada', 'variedad', 'operadorResponsable'])->get();
        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

        return view('aclimatacion.index', compact('aclimataciones'))

            ->with(compact('ruta', 'texto_boton'));
    }

public function create()
{
    $operadores = Operador::where('estado', 1)->get();
  
    $registros_plantacion = Plantacion::with('loteLlegada.variedad') 
        ->get();
    
    
    $plantaciones_por_lote = $registros_plantacion->groupBy('ID_Llegada');

    $lotes_id_disponibles = []; 
    $data_calculada = [];     

    foreach ($plantaciones_por_lote as $id_llegada => $registros_del_lote) {
        $lote_origen = $registros_del_lote->first()->loteLlegada; 
       
        $total_sembrado_acumulado = $registros_del_lote->sum('cantidad_sembrada');
        $total_perdidas_acumulado = $registros_del_lote->sum('cantidad_perdida');
        $stock_recibido = $lote_origen->Cantidad_Plantas; 
        
      
        $stock_disponible = $stock_recibido - ($total_sembrado_acumulado + $total_perdidas_acumulado);

      
        if ($stock_disponible > 0) { 
            $lotes_id_disponibles[] = $id_llegada;

           
            $data_calculada[$id_llegada] = [
                'stock_disponible' => $stock_disponible,
            ];
        }
    }

    $lotes_consolidados = LlegadaPlanta::with('variedad')
        ->whereIn('ID_Llegada', $lotes_id_disponibles)
        ->get();

    $ruta = route('aclimatacion.index');
    $texto_boton = "Volver al Listado";

    return view('aclimatacion.create', compact('operadores', 'lotes_consolidados', 'data_calculada'))
        ->with(compact('ruta', 'texto_boton'));
}


    public function show(Aclimatacion $aclimatacion)

    {


        $aclimatacion->load(['loteLlegada', 'variedad', 'operadorResponsable']);
        $id_lote = $aclimatacion->ID_Llegada;
        $merma_historica_lote = Plantacion::where('ID_Llegada', $id_lote)
            ->sum('cantidad_perdida');
        $chequeos = ChequeoHyT::where('ID_Aclimatacion', $aclimatacion->ID_Aclimatacion)
            ->with('operadorResponsable')
            ->orderBy('Fecha_Chequeo', 'desc')
            ->get();

        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver al Listado";
        $total_plantas_sembradas = Plantacion::where('ID_Llegada', $id_lote)

            ->sum('cantidad_sembrada');
        $chequeos = ChequeoHyT::where('ID_Aclimatacion', $aclimatacion->ID_Aclimatacion)
            ->with('operadorResponsable')
            ->orderBy('Fecha_Chequeo', 'desc')
            ->get();



        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver al Listado";

        return view('aclimatacion.show', compact('aclimatacion', 'chequeos', 'merma_historica_lote', 'total_plantas_sembradas'))

            ->with(compact('ruta', 'texto_boton'));
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

            'ID_Llegada' => 'required|exists:llegada_planta,ID_Llegada', // CRÍTICO: Debe ser ID_Llegada
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
            'Fecha_Ingreso' => 'required|date',
            'Estado_Inicial' => 'required|string|max:50',
            'Duracion_Aclimatacion' => 'required|integer|min:1',
            'Observaciones' => 'nullable|string',
        ]);



        $llegada = \App\Models\LlegadaPlanta::find($request->ID_Llegada);
        if (!$llegada) {
            return redirect()->back()->withInput()->with('error', 'Lote de Llegada no encontrado.');
        }

        $stock_inicial_para_aclimatacion = (int) $llegada->Cantidad_Plantas;
        $data = $request->all();
        $data['ID_Llegada'] = $request->ID_Llegada;
        $data['ID_Variedad'] = $llegada->ID_Variedad;
        unset($data['ID_Plantacion']);

        $data['stock_inicial_etapa'] = $stock_inicial_para_aclimatacion;
        \App\Models\Aclimatacion::create($data);
        return redirect()->route('aclimatacion.index')->with('success', 'Etapa de Aclimatación iniciada correctamente.');
    }
    public function registrarMerma(Request $request, Aclimatacion $aclimatacion)
{
 
    if ($aclimatacion->fecha_cierre) {
        return redirect()->back()->with('error', 'No se puede registrar merma: La etapa ya fue cerrada.');
    }
    
    $request->validate([
        'cantidad_merma' => 'required|integer|min:1',
    ]);

    $merma_reportada = $request->cantidad_merma;
    $merma_acumulada_actual = $aclimatacion->merma_etapa ?? 0;
    $merma_acumulada_nueva = $merma_acumulada_actual + $merma_reportada;


    $total_sembrado = \App\Models\Plantacion::where('ID_Llegada', $aclimatacion->ID_Llegada)->sum('cantidad_sembrada');

    if ($merma_acumulada_nueva > $total_sembrado) {
         return redirect()->back()->with('error', 'La merma acumulada excede el stock sembrado total.');
    }
    
 
    $aclimatacion->update([
        'merma_etapa' => $merma_acumulada_nueva,
    ]);

    return redirect()->back()->with('success', 'Merma registrada. Pérdidas acumuladas: ' . number_format($merma_acumulada_nueva) . ' unidades.');
}
}
