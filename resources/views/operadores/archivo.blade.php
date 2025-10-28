@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <div class="container mt-4">
        <h1>ARCHIVO HISTÓRICO DE OPERADORES (INACTIVOS)</h1>
        
        <p class="lead">Esta sección muestra los operadores desactivados. Sus registros históricos aún son visibles en reportes de trazabilidad.</p>
        
        <div class="card shadow mt-4">
            <div class="card-body">
                <div class="table-responsive">
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
                                
                                <td><span class="badge bg-danger">INACTIVO</span></td>
                                
                                <td class="acciones-cell">
                                    
                                    {{-- 1. REACTIVAR (SOLUCIÓN FINAL: USAR POST) --}}
                                    <form action="{{ route('operadores.reactivate', $operador->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                      
                                        <button type="submit" 
                                                class="btn btn-sm btn-success" 
                                                title="Reactivar">
                                            Reactivar
                                        </button>
                                    </form>

                                  
                                    <form action="{{ route('operadores.hard_delete', $operador->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE') 
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="return confirm(' ADVERTENCIA: Se borrará PERMANENTEMENTE. ¿Continuar?');">
                                            Borrar Definitivo
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($operadores->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">No hay operadores inactivos.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection