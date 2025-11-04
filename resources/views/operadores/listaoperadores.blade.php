@extends('layouts.base', [
    'ruta' => $ruta, 
    'texto_boton' => $texto_boton
]) 

@section('content')

    <div class="container mt-4">
        <h1>Gestión y Consulta de Operadores</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <a href="{{ route('operadores.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-person-plus"></i> Registrar Nuevo Operador
        </a>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 25%;">NOMBRE COMPLETO</th>
                                <th style="width: 15%;">PUESTO</th>
                                <th style="width: 10%;">ESTADO</th>
                                <th style="width: 15%;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operadores as $operador)
                            <tr>
                                <td>{{ $operador->ID_Operador }}</td>
                                {{-- NOMBRE COMPLETO --}}
                                <td>{{ $operador->nombre }} {{ $operador->apellido }}</td> 
                                {{-- PUESTO --}}
                                <td>{{ $operador->puesto }}</td> 
                           
                            
                                {{-- ESTADO --}}
                                <td>
                                    @if ($operador->estado == 1)
                                        <span class="badge bg-success">ACTIVO</span>
                                    @else
                                        <span class="badge bg-danger">INACTIVO</span>
                                    @endif
                                </td>
                                
                           <td class="acciones-cell">
                                    @if ($operador->estado == 1)
                                        {{-- 1. ACTIVO: EDITAR y DESACTIVAR --}}
                                        <a href="{{ route('operadores.edit', $operador->ID_Operador) }}" class="btn btn-sm btn-warning">EDITAR</a>
                                        
                                        <form action="{{ route('operadores.destroy', $operador->ID_Operador) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE') 
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea DESACTIVAR a {{ $operador->nombre }}?');">
                                                Desactivar
                                            </button>
                                        </form>
                                    @else
                                        {{-- 2. INACTIVO: REACTIVAR y ELIMINAR PERMANENTE --}}
                                        <form action="{{ route('operadores.reactivate', $operador->ID_Operador) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">Reactivar</button>
                                        </form>
                                        
                                        <form action="{{ route('operadores.hardDelete', $operador->ID_Operador) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE') 
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(' ¿Seguro de ELIMINAR PERMANENTEMENTE a {{ $operador->nombre }}?');">
                                                Eliminar Definitivo
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($operadores->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">No hay operadores registrados.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection