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

        // 1. OBTENER TODAS LAS PLANTACIONES Y AGRUPAR POR LOTE DE LLEGADA (ID_Llegada)
        $registros_plantacion = Plantacion::with(['variedad', 'loteLlegada']) 
            ->get();

        
        $plantaciones_por_lote = $registros_plantacion->groupBy('ID_Llegada');

        // 2. CONSOLIDAR EN UN ARRAY DE OPCIONES (Metricas del Lote)
        $lotes_consolidados = [];

        foreach ($plantaciones_por_lote as $id_llegada => $registros_del_lote) {

            $lote_origen = $registros_del_lote->first()->loteLlegada; 
           
            $total_sembrado_acumulado = $registros_del_lote->sum('cantidad_sembrada');
            $total_perdidas_acumulado = $registros_del_lote->sum('cantidad_perdida');
            $stock_recibido = $lote_origen->Cantidad_Plantas; 
            $stock_disponible = $stock_recibido - ($total_sembrado_acumulado + $total_perdidas_acumulado);

            if ($stock_disponible >= 0) {
                $lotes_consolidados[] = (object) [
                    
                    'id_llegada' => $id_llegada,
                    'stock_recibido' => $stock_recibido,
                    'total_sembrado' => $total_sembrado_acumulado,
                    'total_perdidas' => $total_perdidas_acumulado,
                    'stock_disponible' => $stock_disponible,
                    'nombre_variedad' => $lote_origen->variedad->nombre ?? 'N/A',
                    'codigo_variedad' => $lote_origen->variedad->codigo ?? 'N/A',

                    'id_plantacion_fk' => $registros_del_lote->last()->ID_Plantacion,
                ];
            }
        }

        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver al Listado";

    
        return view('aclimatacion.create', compact('operadores', 'lotes_consolidados'))
            ->with(compact('ruta', 'texto_boton'));
    }

    /**
     * Muestra el detalle y gestión de una etapa.
     */

    public function show(Aclimatacion $aclimatacion)
    {
        
        $aclimatacion->load(['loteLlegada', 'variedad', 'operadorResponsable']);

        // 1. OBTENER LA MERMA HISTÓRICA ACUMULADA DE SIEMBRA
        $id_lote = $aclimatacion->ID_Llegada;


        $merma_historica_lote = Plantacion::where('ID_Llegada', $id_lote)
            ->sum('cantidad_perdida');

        // 2. CARGAR EL HISTORIAL DE CHEQUEOS AMBIENTALES
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
        

        $request->validate([
            'merma_etapa' => 'required|integer|min:0',
        ]);

        $merma_reportada = $request->merma_etapa;

     
        $stock_inicial_real = $aclimatacion->loteLlegada->Cantidad_Plantas ?? 0;

     
        $stock_inicial_real = (int) $stock_inicial_real;

        // 1. Cálculo del Inventario Pasante
        $cantidad_pasante = $stock_inicial_real - $merma_reportada;

        // 2. Verificación de Merma
        if ($cantidad_pasante < 0) {
            return redirect()->back()->withErrors([
                'merma_etapa' => 'Error: La merma reportada (' . number_format($merma_reportada) . ') excede la cantidad inicial del lote (' . number_format($stock_inicial_real) . ').'
            ])->withInput();
        }

        // 3. Actualizar la Etapa de Aclimatación (Cierre)
        $aclimatacion->update([
            'fecha_cierre' => now(),
            'merma_etapa' => $merma_reportada,
            'cantidad_final' => $cantidad_pasante, 
        ]);

   

        return redirect()->route('aclimatacion.show', $aclimatacion->ID_Aclimatacion)
            ->with('success', '¡Etapa cerrada exitosamente! Se registró la merma.');
    }
 
    public function edit(string $id)
    {
      
        return redirect()->route('aclimatacion.show', $id);
    }



    public function store(Request $request)
    {
        // 1. VALIDACIÓN
        $request->validate([
            'ID_Llegada' => 'required|exists:llegada_planta,ID_Llegada', // CRÍTICO: Debe ser ID_Llegada
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
            'Fecha_Ingreso' => 'required|date',
            'Estado_Inicial' => 'required|string|max:50',
            'Duracion_Aclimatacion' => 'required|integer|min:1',
            'Observaciones' => 'nullable|string',
        ]);

        // 2. OBTENER EL LOTE DE LLEGADA (INVENTARIO ORIGEN)
    
        $llegada = \App\Models\LlegadaPlanta::find($request->ID_Llegada);

        if (!$llegada) {
            return redirect()->back()->withInput()->with('error', 'Lote de Llegada no encontrado.');
        }

        // 3. OBTENER EL STOCK INICIAL DE LA ETAPA
        
        $stock_inicial_para_aclimatacion = (int) $llegada->Cantidad_Plantas;

        // 4. PREPARAR DATOS Y ASIGNAR FKs
        $data = $request->all();

        $data['ID_Llegada'] = $request->ID_Llegada;

        
        $data['ID_Variedad'] = $llegada->ID_Variedad;

       
        unset($data['ID_Plantacion']);

        $data['stock_inicial_etapa'] = $stock_inicial_para_aclimatacion;

        
        \App\Models\Aclimatacion::create($data);

        return redirect()->route('aclimatacion.index')->with('success', 'Etapa de Aclimatación iniciada correctamente.');
    }
}
