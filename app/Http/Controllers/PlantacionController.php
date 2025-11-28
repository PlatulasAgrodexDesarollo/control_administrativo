<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantacion;
use Illuminate\Support\Facades\DB;
use App\Models\LlegadaPlanta;
use App\Models\Operador;
use Carbon\Carbon;


class PlantacionController extends Controller
{

    public function index(Request $request)
    {

        $filtro = $request->input('q');

        $meses_es = [
            'ENERO' => 1,
            'FEBRERO' => 2,
            'MARZO' => 3,
            'ABRIL' => 4,
            'MAYO' => 5,
            'JUNIO' => 6,
            'JULIO' => 7,
            'AGOSTO' => 8,
            'SEPTIEMBRE' => 9,
            'OCTUBRE' => 10,
            'NOVIEMBRE' => 11,
            'DICIEMBRE' => 12,
            'ENE' => 1,
            'FEB' => 2,
            'MAR' => 3,
            'ABR' => 4,
            'MAY' => 5,
            'JUN' => 6,
            'JUL' => 7,
            'AGO' => 8,
            'SEP' => 9,
            'OCT' => 10,
            'NOV' => 11,
            'DIC' => 12,
        ];
        $mes_buscado = strtoupper($filtro);
        $mes_numero = $meses_es[$mes_buscado] ?? null;

        $anio_actual = date('Y');

       
        $query = Plantacion::with(['loteLlegada.variedad', 'operadorPlantacion']);

        if ($filtro) {
       
            $query->where(function ($q) use ($filtro, $mes_numero, $anio_actual) {

          
                $q->whereHas('operadorPlantacion', function ($subq) use ($filtro) {
                    $subq->where('nombre', 'like', '%' . $filtro . '%');
                })
                    ->orWhereHas('loteLlegada.variedad', function ($subq) use ($filtro) {
                        $subq->where('nombre', 'like', '%' . $filtro . '%')
                            ->orWhere('codigo', 'like', '%' . $filtro . '%');
                    });

               
                $numericSearchTerm = null;

         
                if (str_contains(strtolower($filtro), 'lote') || is_numeric($filtro)) {
                    if (preg_match('/\d+/', $filtro, $matches)) {
                        $numericSearchTerm = $matches[0];
                    }
                }

                
                $q->orWhereHas('loteLlegada', function ($subq) use ($filtro, $mes_numero, $numericSearchTerm, $anio_actual) {

                 
                    $subq->where(function ($lotQuery) use ($filtro, $mes_numero, $numericSearchTerm, $anio_actual) {

                        $hasLotFilter = false;

                        if ($numericSearchTerm && is_numeric($numericSearchTerm)) {
                            $lotQuery->where(function ($q) use ($numericSearchTerm) {
                              
                                $q->where('ID_Llegada', $numericSearchTerm);

                                
                                $q->orWhere(DB::raw('CEIL(DAYOFMONTH(Fecha_Llegada) / 7)'), '=', $numericSearchTerm);

                                
                                $q->orWhereYear('Fecha_Llegada', $numericSearchTerm)
                                    ->orWhereMonth('Fecha_Llegada', $numericSearchTerm);
                            });
                            $hasLotFilter = true;
                        }

                       
                        if ($mes_numero) {
                    
                            $method = $hasLotFilter ? 'orWhere' : 'where';

                            $lotQuery->{$method}(function ($q) use ($mes_numero, $anio_actual) {
                            
                                $q->whereMonth('Fecha_Llegada', $mes_numero);
                                $q->whereYear('Fecha_Llegada', $anio_actual);
                            });

                            $hasLotFilter = true;
                        }
                        if (!$hasLotFilter && str_contains(strtolower($filtro), 'semana')) {
                            if (preg_match('/\d+/', $filtro, $matches)) {
                                $weekNumber = $matches[0];

                                $lotQuery->where(DB::raw('CEIL(DAYOFMONTH(Fecha_Llegada) / 7)'), '=', $weekNumber);
                                $hasLotFilter = true;
                            }
                        }

                        
                        if (!$hasLotFilter) {
                            $lotQuery->whereRaw('0 = 1');
                        }
                    });
                });
            });
        }


        $plantaciones = $query->get();
        $plantaciones_agrupadas = $plantaciones->groupBy('ID_Llegada');

        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver a Aclimatación";

        return view('plantacion.index', compact('plantaciones_agrupadas', 'filtro'))
            ->with(compact('ruta', 'texto_boton'));
    }

    public function show($id)
    {

        $plantacion = \App\Models\Plantacion::find($id);

        if (!$plantacion) {
            return redirect()->route('plantacion.index')->with('error', 'Registro de plantación no encontrado.');
        }


        $registro = $plantacion->load(['loteLlegada', 'operadorPlantacion', 'variedad', 'operadorLlegada']);

        $ruta = route('plantacion.index');
        $texto_boton = "Volver al Listado";


        return view('plantacion.show', compact('registro'))
            ->with(compact('ruta', 'texto_boton'));
    }
    public function create()
    {
        $operadores = Operador::where('estado', 1)->get();
        $lotes_disponibles = LlegadaPlanta::with('variedad')->get();

        $ruta = route('plantacion.index');
        $texto_boton = "Volver al Listado";

        return view('plantacion.create', compact('operadores', 'lotes_disponibles'))
            ->with(compact('ruta', 'texto_boton'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'Fecha_Plantacion' => 'required|date',
            'cantidad_sembrada' => 'required|integer|min:0',
            'sin_raiz' => 'required|integer|min:0',
            'pequena_o_mal_formada' => 'required|integer|min:0',
            'ID_Llegada' => 'required|exists:llegada_planta,ID_Llegada',
            'Operador_Plantacion' => 'required|exists:operadores,ID_Operador',
            'Observaciones' => 'nullable|string',
        ]);

        $lote = LlegadaPlanta::find($request->ID_Llegada);

        $consumido_previamente = Plantacion::where('ID_Llegada', $request->ID_Llegada)
            ->sum(DB::raw('cantidad_sembrada + cantidad_perdida'));


        $cantidad_perdida_calculada = $request->sin_raiz + $request->pequena_o_mal_formada;
        $nuevo_consumo = $request->cantidad_sembrada + $cantidad_perdida_calculada;


        $total_final_consumido = $consumido_previamente + $nuevo_consumo;
        $stock_inicial = $lote->Cantidad_Plantas;

        if ($total_final_consumido > $stock_inicial) {
            $stock_restante = $stock_inicial - $consumido_previamente;

            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_sembrada' => "ERROR: El lote solo tiene " . number_format($stock_restante, 0) . " plantas restantes. La cantidad total excede el inventario inicial."]);
        }


        $data = $request->all();
        $data['cantidad_perdida'] = $cantidad_perdida_calculada;
        $data['ID_Variedad'] = $lote->ID_Variedad;
        $data['Operador_Llegada'] = $lote->Operador_Llegada;
        $data['editado'] = 0;

        Plantacion::create($data);

        return redirect()->route('plantacion.index')
            ->with('success', 'Registro de plantación guardado exitosamente.');
    }


    public function update(Request $request, Plantacion $plantacion)
    {

        $lote = LlegadaPlanta::find($request->ID_Llegada);


        $consumido_previamente = Plantacion::where('ID_Llegada', $request->ID_Llegada)
            ->where('ID_Plantacion', '!=', $plantacion->ID_Plantacion)
            ->sum(DB::raw('cantidad_sembrada + cantidad_perdida'));


        $cantidad_perdida_calculada = $request->sin_raiz + $request->pequena_o_mal_formada;
        $nuevo_consumo = $request->cantidad_sembrada + $cantidad_perdida_calculada;
        $total_final_consumido = $consumido_previamente + $nuevo_consumo;
        $stock_inicial = $lote->Cantidad_Plantas;

        if ($total_final_consumido > $stock_inicial) {
            $stock_restante = $stock_inicial - $consumido_previamente;
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad_sembrada' => "ERROR: El lote solo tiene $stock_restante plantas restantes. La cantidad total excede el inventario inicial."]);
        }


        $data = $request->all();
        $data['ID_Variedad'] = $lote->ID_Variedad;
        $data['Operador_Llegada'] = $lote->Operador_Llegada;
        $data['cantidad_perdida'] = $cantidad_perdida_calculada;
        $data['editado'] = 1;

        $plantacion->update($data);

        return redirect()->route('plantacion.index')
            ->with('success', 'Registro N°' . $plantacion->ID_Plantacion . ' actualizado y marcado como EDITADO.');
    }
    public function edit(Plantacion $plantacion)
    {

        $operadores = \App\Models\Operador::where('estado', 1)->get();
        $lotes_disponibles = \App\Models\LlegadaPlanta::with('variedad')->get();

        $ruta = route('plantacion.index');
        $texto_boton = "Volver al Listado";


        return view('plantacion.edit', compact('plantacion', 'operadores', 'lotes_disponibles'))
            ->with(compact('ruta', 'texto_boton'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
