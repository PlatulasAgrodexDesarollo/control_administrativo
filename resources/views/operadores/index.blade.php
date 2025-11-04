@php
// Estas variables deben venir del OperadorController@index
$usuario_nombre = "Admin Invernadero"; // Simulación de $_SESSION['Nombre']
$ruta = route('dashboard'); // El botón "Regresar" va al Dashboard de módulos
$texto_boton = "Regresar al Menú";
@endphp

@extends('layouts.base', [

'ruta' => $ruta,
'texto_boton' => $texto_boton
])

@section('content')

<main class="container py-4">

    <h1 class="h2 mb-4">Gestión de Operadores</h1>

    <h5 class="mb-3">¡Hola, {{ $usuario_nombre ?? 'Usuario' }}!</h5>

    <div class="row g-4">
        <div class="row mb-4 g-4 justify-content-center">
            <div class="col-12 col-sm-6 col-lg-4">
                {{-- Apunta a la ruta nombrada: operadores.create --}}
                <a href="{{ route('operadores.create') }}" class="text-decoration-none">
                    <div class="card shadow-sm card-admin h-100">
                        <div class="card-body text-center">
                            <h4 class="card-title">➕ Registrar operador</h4>
                            <p class="card-text small text-muted">Crear nuevos usuarios para el sistema.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                {{-- Apunta a una nueva ruta para la lista: operadores.list --}}
                <a href="{{ route('operadores.listaoperadores') }}" class="text-decoration-none">
                    <div class="card shadow-sm card-admin h-100">
                        <div class="card-body text-center">
                            <h4 class="card-title">👥 Gestionar operadores</h4>
                            <p class="card-text small text-muted">Ver, editar y desactivar usuarios existentes.</p>
                        </div>
                    </div>
                </a>
            </div>
           

        </div>
</main>

@endsection