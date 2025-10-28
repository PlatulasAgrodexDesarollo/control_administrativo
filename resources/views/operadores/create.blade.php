@extends('layouts.base') 



@section('content')

    <div class="container mt-4">
        <h1>Crear Nuevo Operador</h1>

        

        <form action="{{ route('operadores.store') }}" method="POST">
            @csrf {{-- ¡Token de seguridad OBLIGATORIO! --}}

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
                @error('nombre') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido:</label>
                <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}">
            </div>

            <div class="mb-3">
                <label for="identificacion" class="form-label">Puesto:</label>
                <input type="text" name="identificacion" class="form-control" required value="{{ old('identificacion') }}">
                @error('identificacion') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones:</label>
                <textarea name="observaciones" class="form-control">{{ old('observaciones') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Guardar Operador</button>
        </form>
    </div>

@endsection 