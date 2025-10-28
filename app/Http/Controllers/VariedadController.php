<?php

namespace App\Http\Controllers;
use App\models\Variedad;
use Illuminate\Http\Request;

class VariedadController extends Controller
{
    
    public function index()
    {
        $variedades = Variedad::all();
        
        $ruta = route('dashboard'); 
        $texto_boton = "Regresar a Módulos";

        return view('variedades.index', compact('variedades'))
        ->with(compact('ruta', 'texto_boton'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
