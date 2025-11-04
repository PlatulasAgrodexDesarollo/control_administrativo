@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')

    <div class="container mt-4">
        <h1>Registrar Nuevo Operador</h1>

        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('operadores.store') }}" method="POST">
            @csrf 
            
            {{-- CAMPO NOMBRE --}}
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
            </div>

            {{-- CAMPO APELLIDO  --}}
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido:</label>
                <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}">
            </div>
            
            {{-- CAMPO PUESTO --}}
            <div class="mb-3">
                <label for="puesto" class="form-label">Puesto:</label>
                <input type="text" name="puesto" class="form-control" required value="{{ old('puesto') }}">
            </div>

            <button type="submit" class="btn btn-success">Guardar Operador</button>
        </form>
    </div>
@endsection