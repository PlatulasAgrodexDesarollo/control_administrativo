<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperadorController; 
use App\Http\Controllers\VariedadController;
use App\Http\Controllers\LlegadaPlantaController;
use App\Http\Controllers\PlantacionController;
use App\Http\Controllers\AclimatacionController;
use App\Http\Controllers\EndurecimientoController;
use App\Http\Controllers\RecuperacionMermaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ControlPlagasController;


Route::get('/', fn() => redirect()->route('dashboard'));
Route::get('/login', fn() => "Página de Login Simulada")->name('login'); 
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 
Route::put('operadores/{operador}/reactivate', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');
Route::delete('operadores/{operador}/hard-delete', [OperadorController::class, 'hardDelete'])->name('operadores.hardDelete');

Route::resource('operadores', OperadorController::class)->parameters(['operadores' => 'operador']);
Route::resource('variedades', VariedadController::class)->parameters(['variedades' => 'variedad']);
Route::resource('llegada_planta', LlegadaPlantaController::class)->parameters(['llegada_planta' => 'llegada_planta']);
Route::resource('plantacion', PlantacionController::class);
Route::resource('recuperacion', RecuperacionMermaController::class);

Route::put('aclimatacion/{aclimatacion}/cerrar', [AclimatacionController::class, 'cerrarEtapa'])->name('aclimatacion.cerrar');
Route::post('aclimatacion/{aclimatacion}/registrar-merma-lote', [AclimatacionController::class, 'registrarMermaLote'])->name('aclimatacion.registrar_merma_lote');
Route::resource('aclimatacion', AclimatacionController::class);

Route::prefix('endurecimiento')->group(function () {
    Route::post('/{id}/registrar-merma', [EndurecimientoController::class, 'registrarMerma'])->name('endurecimiento.registrarMerma');
    Route::post('/{id}/finalizar', [EndurecimientoController::class, 'finalizarEtapa'])->name('endurecimiento.finalizar');
});
Route::resource('endurecimiento', EndurecimientoController::class);

Route::get('/reporte-mensual', [ReporteController::class, 'reporteMensual'])->name('reporte.mensual');

Route::get('control_plagas/create/{etapa_type}/{etapa_id}', [ControlPlagasController::class, 'create'])->name('control_plagas.create');