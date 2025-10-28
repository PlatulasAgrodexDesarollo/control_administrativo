<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperadorController; 
use App\Http\Controllers\VariedadController;


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

// Control de Plagas:
Route::get('/control_plaga', function () {
    return "Cargando Módulo Control Plagas...";
})->name('control_plaga.index');

// Chequeo Agronómico:
Route::get('/chequeo_agronomico', function () {
    return "Cargando Módulo Chequeos...";
})->name('chequeo_agronomico.index');


Route::get('operadores/listaoperadores', [OperadorController::class, 'listaoperadores'])->name('operadores.listaoperadores'); 


Route::get('operadores/archivo', [OperadorController::class, 'archivo'])->name('operadores.archivo');


Route::post('operadores/{operador}/desactivar', [OperadorController::class, 'destroy'])->name('operadores.destroy');
Route::delete('variedades/{variedad}', [VariedadController::class, 'destroy'])->name('variedades.destroy');

Route::post('operadores/{operador}/reactivar', [OperadorController::class, 'reactivate'])->name('operadores.reactivate');



Route::delete('operadores/delete/{operador}', [OperadorController::class, 'hardDelete'])->name('operadores.hard_delete');


Route::resource('operadores', OperadorController::class) ->parameters([ 'operadores' => 'operador',  ]) ->except(['destroy' ]);
Route::resource('variedades', VariedadController::class) ->parameters(['variedades'=>'variedad',]);