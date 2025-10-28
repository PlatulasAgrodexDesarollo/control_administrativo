@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <div class="container mt-4">
        <h1>Registrar Nueva Variedad</h1>

        <form action="{{ route('variedades.store') }}" method="POST">
            @csrf 
            
            <div class="mb-3">
                <label for="nombre_variedad" class="form-label">Nombre de la Variedad (Ej: Tomate Cherry):</label>
                <input type="text" name="nombre_variedad" class="form-control" required value="{{ old('nombre_variedad') }}">
                @error('nombre_variedad') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Características, cuidados):</label>
                <textarea name="descripcion" class="form-control">{{ old('descripcion') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Guardar Variedad</button>
        </form>
    </div>

@endsection