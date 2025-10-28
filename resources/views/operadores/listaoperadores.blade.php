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
        <div class="card-header bg-white">
            <h5 class="mb-0">Listado de Personal Activo e Inactivo</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {{-- La tabla va dentro del body de la tarjeta --}}
                <table class="table table-striped table-bordered table-clientes">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 25%;">NOMBRE COMPLETO</th>
                            <th style="width: 15%;">PUESTO</th>
                            <th style="width: 30%;">OBSERVACIONES</th>
                            <th style="width: 10%;">ESTADO</th>
                            <th style="width: 15%;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($operadores as $operador)
                        <tr>
                            <td>{{ $operador->id }}</td>
                            <td>{{ $operador->nombre }} {{ $operador->apellido }}</td>
                            <td>{{ $operador->puesto }}</td>
                            <td>{{ $operador->observaciones ?? 'N/A' }}</td>
                            <td>
                                @if ($operador->estado == 1)
                                <span class="badge bg-success">ACTIVO</span>
                                @else
                                <span class="badge bg-danger">INACTIVO</span>
                                @endif
                            </td>
                            <td class="acciones-cell">
                                @if ($operador->estado == 1)
                                <a href="{{ route('operadores.edit', $operador->id) }}" class="btn btn-sm btn-success">EDITAR</a>

                  
                                <form action="{{ route('operadores.destroy', $operador->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                  
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea DESACTIVAR a este operador?');">
                                        Desactivar
                                    </button>
                                </form>
                                @else
                              
                                <form action="{{ route('operadores.reactivate', $operador->id) }}" method="POST" style="display:inline;">
                                    @csrf
                          
                                    <button type="submit" class="btn btn-sm btn-success">Reactivar</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        @if($operadores->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">No hay operadores registrados en el sistema.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 

</div>

@endsection