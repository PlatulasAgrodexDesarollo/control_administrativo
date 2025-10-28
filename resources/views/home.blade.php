@extends('layouts.app') {{-- O el nombre que le hayas dado al layout principal --}}

{{-- Si quieres cambiar el título de la pestaña del navegador --}}
@php
    $titulo = 'Dashboard Principal';
@endphp

{{-- La sección 'content' inyecta el HTML en el @yield('content') del layout --}}
@section('content')

    {{-- Aquí va el contenido único de esta página (tablas, formularios, etc.) --}}
    <h1>¡Bienvenido a la administración de Plantas Agrodex!</h1>
    <p>Empieza a trabajar con los datos.</p>

@endsection