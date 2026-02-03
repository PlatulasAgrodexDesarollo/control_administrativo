<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['sincro.sesion', 'prevent-back'])->group(function () {

    // PANEL PRINCIPAL
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // OPERADORES
    Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 
    Route::put('operadores/{operador}/reactivate', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');
    Route::delete('operadores/{operador}/hard-delete', [OperadorController::class, 'hardDelete'])->name('operadores.hardDelete');

    Route::resource('operadores', OperadorController::class)->parameters(['operadores' => 'operador']);
    Route::resource('variedades', VariedadController::class)->parameters(['variedades' => 'variedad']);
    Route::resource('llegada_planta', LlegadaPlantaController::class)->parameters(['llegada_planta' => 'llegada_planta']);
    Route::resource('plantacion', PlantacionController::class);
    Route::resource('recuperacion', RecuperacionMermaController::class);

    // ACLIMATACIÓN
    Route::put('aclimatacion/{aclimatacion}/cerrar', [AclimatacionController::class, 'cerrarEtapa'])->name('aclimatacion.cerrar');
    Route::post('aclimatacion/{aclimatacion}/registrar-merma-lote', [AclimatacionController::class, 'registrarMermaLote'])->name('aclimatacion.registrar_merma_lote');
    Route::resource('aclimatacion', AclimatacionController::class);

    // ENDURECIMIENTO
    Route::prefix('endurecimiento')->group(function () {
        Route::post('/{id}/registrar-merma', [EndurecimientoController::class, 'registrarMerma'])->name('endurecimiento.registrarMerma');
        Route::post('/{id}/finalizar', [EndurecimientoController::class, 'finalizarEtapa'])->name('endurecimiento.finalizar');
    });
    Route::resource('endurecimiento', EndurecimientoController::class);

    // REPORTES Y PLAGAS
    Route::get('/reporte-mensual', [ReporteController::class, 'reporteMensual'])->name('reporte.mensual');
    Route::get('control_plagas/create/{etapa_type}/{etapa_id}', [ControlPlagasController::class, 'create'])->name('control_plagas.create');

    // --- GRUPOS POR ROL ---
    
    // ADMINISTRADOR
    Route::middleware(['rol:1'])->group(function () {
        Route::get('/configuracion', [DashboardController::class, 'index'])->name('configuracion');
        Route::resource('operadores', OperadorController::class);
        Route::get('/reportes-mensuales', [ReporteController::class, 'reporteMensual'])->name('reporte.mensual');
    });

    // SECRETARÍA
    Route::middleware(['rol:1,2'])->group(function () {
        Route::get('/bitacora', [DashboardController::class, 'index'])->name('bitacora');
        Route::resource('llegada-planta', LlegadaPlantaController::class);
        Route::resource('variedades', VariedadController::class);
    });

    // PRODUCCIÓN
    Route::middleware(['rol:1,3'])->group(function () {
        Route::get('/mi-rendimiento', [DashboardController::class, 'index'])->name('mi.rendimiento');
        Route::resource('aclimatacion', AclimatacionController::class);
        Route::resource('plantacion', PlantacionController::class);
        Route::resource('recuperacion', RecuperacionMermaController::class);
        Route::resource('endurecimiento', EndurecimientoController::class);
    });

    // RUTA DE CIERRE DE SESIÓN
    Route::get('/logout', [DashboardController::class, 'logout'])->name('logout');
});