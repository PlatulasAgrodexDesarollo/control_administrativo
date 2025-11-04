<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperadorController; 
use App\Http\Controllers\VariedadController;
use App\Http\Controllers\LlegadaPlantaController;
use App\Http\Controllers\PlantacionController;


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




Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 


Route::resource('operadores', OperadorController::class)  ->parameters(['operadores' => 'operador']);
Route::resource('variedades', VariedadController::class) ->parameters(['variedades' => 'variedad']);
Route::resource('llegada_planta', LlegadaPlantaController::class) ->parameters(['llegada_planta' => 'llegada_planta']);
Route::resource('plantacion', PlantacionController::class) ->parameters(['plantacion' => 'plantacion']);

Route::put('operadores/{operador}/reactivate', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');
Route::delete('operadores/{operador}/hard-delete', [OperadorController::class, 'hardDelete'])->name('operadores.hardDelete');