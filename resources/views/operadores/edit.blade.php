@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')

<div class="container mt-4">
    {{-- Muestra el nombre del operador que se está editando --}}
    <h1 class="h3">Editar Operador: {{ $operador->nombre }} {{ $operador->apellido }}</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- El formulario apunta al método 'update' y pasa el objeto $operador (ID) --}}
   <form action="{{ route('operadores.update', $operador) }}" method="POST">
        @csrf
        @method('PUT') {{-- Indica que esta es una petición de Actualización --}}

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            {{-- Muestra el valor antiguo o el valor actual del objeto --}}
            <input type="text" name="nombre" class="form-control" required 
                   value="{{ old('nombre', $operador->nombre) }}">
        </div>

        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido:</label>
            <input type="text" name="apellido" class="form-control" 
                   value="{{ old('apellido', $operador->apellido) }}">
        </div>

        <div class="mb-3">
            <label for="identificacion" class="form-label">Puesto:</label>
            <input type="text" name="identificacion" class="form-control" required 
                   value="{{ old('identificacion', $operador->identificacion) }}">
            {{-- Laravel valida que esta ID sea única, excepto para este registro --}}
        </div>
        
        {{-- CAMPO CLAVE PARA LA GESTIÓN (ACTIVO/INACTIVO) --}}
        <div class="mb-3">
            <label for="estado" class="form-label">Estado del Operador</label>
            <select class="form-select" id="estado" name="estado" required>
                {{-- Si el estado actual es 1 (o si hay un old('estado') == 1) lo selecciona --}}
                <option value="1" {{ old('estado', $operador->estado) == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ old('estado', $operador->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
            <div class="form-text">Usar 'Inactivo' para archivar o desactivar al operador.</div>
        </div>
        
        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones:</label>
            <textarea name="observaciones" class="form-control">{{ old('observaciones', $operador->observaciones) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar Cambios</button>
    </form>
</div>

@endsection