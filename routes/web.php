<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperadorController; 
use App\Http\Controllers\VariedadController;
use App\Http\Controllers\LlegadaPlantaController;
use App\Http\Controllers\PlantacionController;
//use App\Http\Controllers\ControlPlagasController;
use App\Http\Controllers\AclimatacionController;
//use App\Http\Controllers\ChequeoHyTController;
use App\Http\Controllers\EndurecimientoController;
use App\Http\Controllers\RecuperacionMermaController;

Route::get('/login', function () {
    return "Página de Login Simulada";
})->name('login'); 


// Ruta del DASHBOARD (Menú de Módulos)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//  Ruta Principal (Redirige al Dashboard)
Route::get('/', function () {
    return redirect()->route('dashboard');
});


//Operadores:
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

//aclimatacion
Route::get('/aclimatacion', function(){
    return "cargando modulo Aclimatacion...";
})->name('aclimatacion.idex');

//endurecimiento 
Route::get('/endurecimiento', function(){
    return "cargando modulo Control endureceimiento...";
})->name('endurecimiento.idex');




Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 
Route::get('control_plagas/create/{etapa_type}/{etapa_id}', [App\Http\Controllers\ControlPlagasController::class, 'create'])->name('control_plagas.create');
//Route::get('chequeo_hyt/create/{aclimatacion_id}', [ChequeoHyTController::class, 'create'])->name('chequeo_hyt.create');
//Route::get('chequeo_hyt/listado/{aclimatacion_id}', [App\Http\Controllers\ChequeoHyTController::class, 'listadoPorAclimatacion'])->name('chequeo_hyt.listado_aclimatacion');
Route::put('aclimatacion/cerrar/{aclimatacion}', [\App\Http\Controllers\AclimatacionController::class, 'cerrarEtapa'])->name('aclimatacion.cerrar');
Route::put('aclimatacion/{aclimatacion}/cerrar', [AclimatacionController::class, 'cerrarEtapa']) ->name('aclimatacion.cerrar');



Route::resource('operadores', OperadorController::class)  ->parameters(['operadores' => 'operador']);
Route::resource('variedades', VariedadController::class) ->parameters(['variedades' => 'variedad']);
Route::resource('llegada_planta', LlegadaPlantaController::class) ->parameters(['llegada_planta' => 'llegada_planta']);
Route::resource('plantacion', PlantacionController::class) ->parameters(['plantacion' => 'plantacion']);
//Route::resource('control_plagas', ControlPlagasController::class);
Route::resource('aclimatacion', AclimatacionController::class) ->parameters(['aclimatacion' => 'aclimatacion']);
Route::resource('endurecimiento', EndurecimientoController::class);
//Route::resource('chequeo_hyt', ChequeoHyTController::class);
Route::resource('recuperacion', RecuperacionMermaController::class)->parameters(['recuperacion' => 'recuperacion']);



Route::put('operadores/{operador}/reactivate', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');
Route::put('/aclimatacion/{aclimatacion}/cerrar', [AclimatacionController::class, 'cerrarEtapa'])->name('aclimatacion.cerrar');




Route::post('/aclimatacion/{aclimatacion}/registrar-merma', [AclimatacionController::class, 'registrarMerma'])->name('aclimatacion.registrar_merma');
Route::post('aclimatacion/{aclimatacion}/registrar-merma-lote', [AclimatacionController::class, 'registrarMermaLote']) ->name('aclimatacion.registrar_merma_lote');
//Route::post('chequeo_hyt/store', [ChequeoHyTController::class, 'store'])->name('chequeo_hyt.store');

Route::delete('operadores/{operador}/hard-delete', [OperadorController::class, 'hardDelete'])->name('operadores.hardDelete');



    Route::prefix('endurecimiento')->group(function () {
    Route::get('/', [EndurecimientoController::class, 'index'])->name('endurecimiento.index');
    Route::get('/create', [EndurecimientoController::class, 'create'])->name('endurecimiento.create');
    Route::post('/store', [EndurecimientoController::class, 'store'])->name('endurecimiento.store');
    Route::get('/{endurecimiento}', [EndurecimientoController::class, 'show'])->name('endurecimiento.show');
    Route::post('/{endurecimiento}/merma', [EndurecimientoController::class, 'registrarMerma'])->name('endurecimiento.registrar_merma');
});
    