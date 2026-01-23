<?php

namespace App\Http\Controllers;

use App\Models\LlegadaPlanta;
use App\Models\Variedad;
use App\Models\Operador;
use Illuminate\Http\Request;

class LlegadaPlantaController extends Controller
{

    public function index()
    {
        
   $lotes_llegada = LlegadaPlanta::with(['variedad', 'operadorLlegada'])->get();

        $ruta = route('dashboard');
        $texto_boton = "Regresar a Módulos";

        return view('llegada_planta.index', compact('lotes_llegada'))
            ->with(compact('ruta', 'texto_boton'));
    }


    public function create()
    {
        // Carga las dependencias para los dropdowns
        $variedades = Variedad::all();
        $operadores = Operador::where('estado', 1)->get(); // Solo operadores activos

        $ruta = route('llegada_planta.index');
        $texto_boton = "Volver al Inventario";

        return view('llegada_planta.create', compact('variedades', 'operadores'))
            ->with(compact('ruta', 'texto_boton'));
    }


   public function store(Request $request)
{
    $request->validate([
        'Fecha_Llegada' => 'required|date',
        'Cantidad_Plantas' => 'required|integer|min:1',
        'ID_Variedad' => 'required|exists:variedades,ID_Variedad',
        'Operador_Llegada' => 'required|exists:operadores,ID_Operador',
    ]);

    $data = $request->all();
    $data['Pre_Aclimatacion'] = $request->has('Pre_Aclimatacion') ? 1 : 0; 

    // Guardamos los datos que SI existen en tu tabla
    \App\Models\LlegadaPlanta::create($data);

    return redirect()->route('llegada_planta.index')
        ->with('success', 'Planta recibida y registrada exitosamente.');
}
    public function show(LlegadaPlanta $llegadaPlanta)
    {
        // Cargamos las relaciones necesarias: Operador y Variedad
        $lote = $llegadaPlanta->load(['variedad', 'operadorLlegada']);

        $ruta = route('llegada_planta.index');
        $texto_boton = "Volver al Listado";

        // Retorna la vista de detalle
        return view('llegada_planta.show', compact('lote'))
            ->with(compact('ruta', 'texto_boton'));
    }
}
