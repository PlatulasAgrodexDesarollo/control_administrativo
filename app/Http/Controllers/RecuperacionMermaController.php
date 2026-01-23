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

        return view('recuperacion.index', compact('recuperaciones', 'ruta', 'texto_boton'));
    }

    public function create()
    {
        $operadores = Operador::where('estado', 1)->get();

        // Obtenemos lotes que tengan historial de pérdida
        $lotes_recuperables = LlegadaPlanta::with('variedad')->get();

        foreach ($lotes_recuperables as $lote) {
            $lote->perdida_acumulada_siembra = Plantacion::where('ID_Llegada', $lote->ID_Llegada)
                ->sum('cantidad_perdida') ?? 0;
        }

        $ruta = route('recuperacion.index');
        $texto_boton = "Volver a historial";

        return view('recuperacion.create', compact('lotes_recuperables', 'operadores', 'ruta', 'texto_boton'));
    }

   public function store(Request $request)
{
    $request->validate([
        'ID_Llegada' => 'required|exists:llegada_planta,ID_Llegada',
        'Cantidad_Recuperada' => 'required|integer|min:1',
        'Fecha_Recuperacion' => 'required|date',
        'Operador_Responsable' => 'required|exists:operadores,ID_Operador',
        // Se quitó la validación de Observaciones
    ]);

    $lote_original = LlegadaPlanta::find($request->ID_Llegada);

    // Guardado sin el campo de observaciones
    RecuperacionMerma::create([
        'ID_Llegada' => $request->ID_Llegada,
        'Cantidad_Recuperada' => $request->Cantidad_Recuperada,
        'Fecha_Recuperacion' => $request->Fecha_Recuperacion,
        'Operador_Responsable' => $request->Operador_Responsable,
    ]);

    return redirect()->route('recuperacion.index')
        ->with('success', '¡Recuperación registrada!');
}
}