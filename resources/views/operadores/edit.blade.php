@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')

    <div class="container mt-4">
        <h1>Editar Operador {{ $operador->ID_Operador }} </h1> 
        
        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('operadores.update', $operador->ID_Operador) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- CAMPO NOMBRE --}}
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required 
                       value="{{ old('nombre', $operador->nombre) }}">
            </div>

            {{--CAMPO APELLIDO --}}
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido:</label>
                <input type="text" name="apellido" class="form-control" 
                       value="{{ old('apellido', $operador->apellido) }}">
            </div>
            
            {{--CAMPO PUESTO --}}
            <div class="mb-3">
                <label for="puesto" class="form-label">Puesto:</label>
                <input type="text" name="puesto" class="form-control" 
                       value="{{ old('puesto', $operador->puesto) }}">
            </div>

            {{--CAMPO ESTADO (para la gestión Activo/Inactivo) --}}
            <div class="mb-3">
                <label for="estado" class="form-label">Estado del Operador</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="1" {{ old('estado', $operador->estado) == 1 ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ old('estado', $operador->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>
@endsection