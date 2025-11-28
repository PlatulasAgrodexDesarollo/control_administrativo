<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecuperacionMerma;
use App\Models\LlegadaPlanta;
use App\Models\Operador;
use App\Models\Plantacion;

class RecuperacionMermaController extends Controller
{

    public function index()
    {

        $recuperaciones = RecuperacionMerma::with(['operadorResponsable', 'loteLlegada.variedad'])
            ->orderBy('Fecha_Recuperacion', 'desc')
            ->get();


        $ruta = route('aclimatacion.index');
        $texto_boton = "Volver a Aclimatación";


        return view('recuperacion.index', compact('recuperaciones'))
            ->with(compact('ruta', 'texto_boton'));
    }
    public function create()
    {
        $operadores = Operador::where('estado', 1)->get();

        // 1. OBTENER LOTES Y CARGAR RELACIONES
        $lotes_recuperables = LlegadaPlanta::where('Cantidad_Plantas', '>', 0)
            ->with('variedad')
            ->get();

        // 2. CALCULAR PÉRDIDA ACUMULADA POR LOTE
        foreach ($lotes_recuperables as $lote) {

            $perdida_acumulada = Plantacion::where('ID_Llegada', $lote->ID_Llegada)
                ->sum('cantidad_perdida');


            $lote->perdida_acumulada_siembra = $perdida_acumulada;
        }


        $ruta = route('recuperacion.index');
        $texto_boton = "Volver a historial de recuperacion";

        return view('recuperacion.create', compact('lotes_recuperables', 'operadores'))
            ->with(compact('ruta', 'texto_boton'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'ID_Llegada' => 'required|exists:llegada_planta,ID_Llegada',
            'Cantidad_Recuperada' => 'required|integer|min:1',
            'Fecha_Recuperacion' => 'required|date',
            'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
            'Observaciones' => 'nullable|string',
        ]);

        // 2. OBTENER DATOS DEL LOTE ORIGINAL (Necesario para el mensaje de éxito)
        $lote_original = LlegadaPlanta::find($request->ID_Llegada);

        // 3. REGISTRAR LA ACCIÓN DE RECUPERACIÓN EN SU TABLA HISTÓRICA
        RecuperacionMerma::create([
            'ID_Llegada' => $request->ID_Llegada,
            'Cantidad_Recuperada' => $request->Cantidad_Recuperada,
            'Fecha_Recuperacion' => $request->Fecha_Recuperacion,
            'Operador_Responsable' => $request->Operador_Responsable,
            'Observaciones' => $request->Observaciones,
        ]);



        return redirect()->route('recuperacion.index')
            ->with('success', '¡Recuperación registrada! Se contabilizaron ' . number_format($request->Cantidad_Recuperada) . ' unidades recuperadas del Lote #' . $lote_original->ID_Llegada . '. Este stock debe ser dado de alta manualmente si ingresa a otro proceso.');
    }
}
