<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperadorController; 
use App\Http\Controllers\VariedadController;
use App\Http\Controllers\LlegadaPlantaController;
use App\Http\Controllers\PlantacionController;
use App\Http\Controllers\ControlPlagasController;
use App\Http\Controllers\AclimatacionController;
use App\Http\Controllers\ChequeoHyTController;
use App\Http\Controllers\ChequeoAgribonController;
use App\Http\Controllers\EndurecimientoController;


Route::get('/login', function () {
    return "Página de Login Simulada";
})->name('login'); 


// Ruta del DASHBOARD (Menú de Módulos)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//  Ruta Principal (Redirige al Dashboard)
Route::get('/', function () {
    return redirect()->route('dashboard');
});



Route::get('/operadores', function () {
    return "Cargando Módulo Operadores...";
})->name('operadores.index');


// Variedades:
Route::get('/variedades', function () {
    return "Cargando Módulo Variedades...";
})->name('variedades.index');

// Llegada de Planta:
Route::get('/llegada_planta', function () {
    return "Cargando Módulo Llegada Planta...";
})->name('llegada_planta.index');

// Plantación:
Route::get('/plantacion', function () {
    return "Cargando Módulo Plantación...";
})->name('plantacion.index');

//control plagas:
Route::get('/control_plagas', function(){
    return "cargando modulo Control plagas...";
})->name('control_plagas.index');




Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 
Route::get('control_plagas/create/{etapa_type}/{etapa_id}', [App\Http\Controllers\ControlPlagasController::class, 'create'])->name('control_plagas.create');
Route::get('chequeo_hyt/create/{aclimatacion_id}', [ChequeoHyTController::class, 'create'])->name('chequeo_hyt.create');
Route::get('chequeo_agribon/create/{aclimatacion_id}', [ChequeoAgribonController::class, 'create'])->name('chequeo_agribon.create');

Route::resource('operadores', OperadorController::class)  ->parameters(['operadores' => 'operador']);
Route::resource('variedades', VariedadController::class) ->parameters(['variedades' => 'variedad']);
Route::resource('llegada_planta', LlegadaPlantaController::class) ->parameters(['llegada_planta' => 'llegada_planta']);
Route::resource('plantacion', PlantacionController::class) ->parameters(['plantacion' => 'plantacion']);
Route::resource('control_plagas', ControlPlagasController::class);
Route::resource('aclimatacion', AclimatacionController::class) ->parameters(['aclimatacion' => 'aclimatacion']);
Route::resource('endurecimiento', EndurecimientoController::class);
Route::resource('chequeo_hyt', ChequeoHyTController::class);
Route::resource('chequeo_agribon', ChequeoAgribonController::class);



Route::put('operadores/{operador}/reactivate', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');
Route::delete('operadores/{operador}/hard-delete', [OperadorController::class, 'hardDelete'])->name('operadores.hardDelete');