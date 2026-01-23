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
        'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6,
        'JULIO' => 7, 'AGOSTO' => 8, 'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
        'ENE' => 1, 'FEB' => 2, 'MAR' => 3, 'ABR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AGO' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DIC' => 12,
    ];

    $mes_buscado = strtoupper($filtro);
    $mes_numero = $meses_es[$mes_buscado] ?? null;

    $query = Plantacion::with(['loteLlegada.variedad', 'operadorPlantacion']);

    if ($filtro) {
        $query->where(function ($q) use ($filtro, $mes_numero) {
            
            // 1. Busqueda por OPERADOR (Prioridad texto)
            $q->whereHas('operadorPlantacion', function ($sub) use ($filtro) {
                $sub->where('nombre', 'like', '%' . $filtro . '%');
            });

            // 2. Busqueda por VARIEDAD O CÓDIGO (Texto)
            $q->orWhereHas('loteLlegada.variedad', function ($sub) use ($filtro) {
                $sub->where('nombre', 'like', '%' . $filtro . '%')
                    ->orWhere('codigo', 'like', '%' . $filtro . '%');
            });

            // 3. Busqueda por FECHAS (Solo si el filtro parece ser un mes o contiene números)
            $numero = preg_match('/\d+/', $filtro, $matches) ? $matches[0] : null;

            if ($mes_numero || $numero) {
                $q->orWhereHas('loteLlegada', function ($subq) use ($filtro, $mes_numero, $numero) {
                    $subq->where(function ($inner) use ($mes_numero, $numero, $filtro) {
                        
                        // Si es un Mes por nombre
                        if ($mes_numero) {
                            $inner->orWhereMonth('Fecha_Llegada', $mes_numero);
                        }

                        // Si es un Lote X o Semana X
                        if ($numero) {
                            if (str_contains(strtolower($filtro), 'lote') || str_contains(strtolower($filtro), 'sem')) {
                                $inner->orWhereRaw('CEIL(DAYOFMONTH(Fecha_Llegada) / 7) = ?', [$numero]);
                            } else {
                                // Evitamos que el año actual (2026) filtre todo si el usuario solo puso un nombre corto
                                if (strlen($filtro) < 5) { 
                                    $inner->orWhere('ID_Llegada', $numero)
                                          ->orWhereMonth('Fecha_Llegada', $numero);
                                } else {
                                    $inner->orWhereYear('Fecha_Llegada', $numero);
                                }
                            }
                        }
                    });
                });
            }
        });
    }

    $plantaciones = $query->orderBy('ID_Plantacion', 'desc')->get();
    $plantaciones_agrupadas = $plantaciones->groupBy('ID_Llegada');

    $ruta = route('aclimatacion.index');
    $texto_boton = "Volver a Aclimatación";

    return view('plantacion.index', compact('plantaciones_agrupadas', 'filtro', 'ruta', 'texto_boton'));
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
