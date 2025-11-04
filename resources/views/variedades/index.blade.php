@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')
    <div class="container mt-4">
        <h1>Catálogo de Variedades</h1>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <a href="{{ route('variedades.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Registrar Nueva Variedad
        </a>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-clientes">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 15%;">NOMBRE</th>
                                <th style="width: 15%;">ESPECIE</th>
                                <th style="width: 10%;">COLOR</th>
                                <th style="width: 10%;">CÓDIGO</th>
                                <th style="width: 10%;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($variedades as $variedad)
                            <tr>
                                <td>{{ $variedad->ID_Variedad }}</td>
                                <td>{{ $variedad->nombre }}</td>
                                <td>{{ $variedad->especie }}</td>
                                <td>{{ $variedad->color }}</td>
                                <td>{{ $variedad->codigo }}</td>
                               
                                
                                <td class="acciones-cell">
                                    <a href="{{ route('variedades.edit', $variedad->ID_Variedad) }}" class="btn btn-sm btn-warning">Editar</a>
                                    
                                    <form action="{{ route('variedades.destroy', $variedad->ID_Variedad) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro de eliminar permanentemente esta variedad?');">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($variedades->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">No hay variedades registradas.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection