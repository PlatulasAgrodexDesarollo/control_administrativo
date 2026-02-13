@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')
    <div class="container mt-4">
        <h1>Editar Variedad </h1> 
        
        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('variedades.update', $variedad->ID_Variedad) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Variedad:</label>
                <input type="text" name="nombre" class="form-control" required 
                       value="{{ old('nombre', $variedad->nombre) }}">
            </div>

            <div class="mb-3">
                <label for="especie" class="form-label">Especie:</label>
                <input type="text" name="especie" class="form-control" 
                       value="{{ old('especie', $variedad->especie) }}">
            </div>

            <div class="mb-3">
                <label for="color" class="form-label">Color:</label>
                <input type="text" name="color" class="form-control" 
                       value="{{ old('color', $variedad->color) }}">
            </div>

            <div class="mb-3">
                <label for="codigo" class="form-label">Código:</label>
                <input type="text" name="codigo" class="form-control" required 
                       value="{{ old('codigo', $variedad->codigo) }}">
            </div>

          
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>
@endsection