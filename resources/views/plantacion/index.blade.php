@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registros de Plantación por Lote</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('plantacion.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-geo-alt-fill"></i> Registrar Nueva Plantación
    </a>

    {{-- INICIO DEL BUCLE DE AGRUPACIÓN POR LOTE --}}
    @foreach ($plantaciones_agrupadas as $id_lote => $plantaciones_del_lote)

    @php
    // Se toma el primer registro del grupo para obtener datos del lote
    $lote = $plantaciones_del_lote->first()->loteLlegada;
    $variedad = $lote->variedad ?? null;

   
    $total_recibidas = $lote->Cantidad_Plantas;
    $total_sembradas = $plantaciones_del_lote->sum('cantidad_sembrada');
    $total_perdidas = $plantaciones_del_lote->sum('cantidad_perdida');
    @endphp

    <div class="card shadow mb-5">
        {{-- ENCABEZADO DE GRUPO (Información Consolidada) --}}
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                LOTE DE INVENTARIO: #{{ $id_lote }}
                <small>
                    {{ $variedad->nombre ?? 'N/A' }}
                    @if ($variedad && $variedad->codigo) [CÓDIGO: {{ $variedad->codigo }}] @endif
                    / Recibidas: {{ number_format($total_recibidas, 0) }}
                    / Total Sembrado: {{ number_format($total_sembradas, 0) }}
                    / Pérdida Acumulada: <span class="text-warning">{{ number_format($total_perdidas, 0) }}</span>
                </small>
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-clientes mb-0">
                    <thead>
                        <tr>
                            <th>ID Reg.</th>
                            <th>Fecha</th>
                            <th>Sembradas</th>
                            <th>Pérdidas Totales</th>
                            <th>Pérdidas (Sin Raíz)</th>
                            <th>Pérdidas (Mal Formada/pequeña )</th>
                            <th>Operador</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plantaciones_del_lote as $p)
                        <tr>
                            <td> {{ $p->ID_Plantacion }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->Fecha_Plantacion)->format('d/m/Y') }}</td>
                            <td>{{ number_format($p->cantidad_sembrada, 0) }}</td>

                            {{-- Pérdida TOTAL (Suma de las categorías) --}}
                            <td class="text-danger">{{ number_format($p->cantidad_perdida, 0) }}</td>

                            {{-- Desglose de Pérdidas --}}
                            <td>{{ number_format($p->sin_raiz, 0) }} und.</td>
                            <td>{{ number_format($p->pequena_o_mal_formada, 0) }} und.</td>

                            <td>
                                {{ $p->operadorPlantacion->nombre ?? 'N/A' }}
                                @if ($p->editado)
                                <br>
                                <span class="badge bg-warning">EDITADO</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('plantacion.edit', $p->ID_Plantacion) }}" class="btn btn-sm btn-warning bi bi-pencil"></a>
                                <a href="{{ route('plantacion.show', $p->ID_Plantacion) }}" class="btn btn-sm btn-info "> Ver</a>


                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    @if(empty($plantaciones_agrupadas) || $plantaciones_agrupadas->isEmpty())
    <p class="lead text-center">No hay registros de plantación en el invernadero.</p>
    @endif
</div>
@endsection