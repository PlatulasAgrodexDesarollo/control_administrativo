<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantacion;
use Illuminate\Support\Facades\DB;
use App\Models\LlegadaPlanta;
use App\Models\Operador;

class PlantacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plantaciones = Plantacion::with(['loteLlegada.variedad', 'operadorPlantacion'])->get();


        $plantaciones_agrupadas = $plantaciones->groupBy('ID_Llegada');

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";


        return view('plantacion.index', compact('plantaciones_agrupadas'))
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
