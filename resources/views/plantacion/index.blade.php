@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registros de Plantación por Lote del mes </h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="mb-4">


        <div class="d-flex gap-2 mb-3">

            <input type="text"
                id="filtroLotesInput"
                class="form-control form-control-lg"
                placeholder="Buscar por Variedad, Código, Nombre del Operador, Mes o Semana..."
                value="{{ $filtro ?? '' }}">

            <button type="button" onclick="aplicarFiltro()" class="btn btn-success flex-shrink-0">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>



        <div class="d-flex gap-2">

            @if ($filtro || request('fecha_inicio'))
            <a href="{{ route('plantacion.index') }}" class="btn btn-secondary">Limpiar Filtro</a>
            @endif
        </div>
    </div>

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
    $inventario_actual = $total_recibidas - $total_perdidas;
    $restante_por_plantar = $inventario_actual - $total_sembradas;
    $restante_por_plantar = max(0, $restante_por_plantar);
    @endphp

    <div class="card shadow mb-5">
        {{-- ENCABEZADO DE GRUPO (Información Consolidada) --}}
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
               
                LOTE DE INVENTARIO: <span class="fw-bold text-dark">{{ $lote->nombre_lote_semana ?? 'ID Lote #' . $id_lote }}</span>
                <small>
                    {{ $variedad->nombre ?? 'N/A' }}
                    @if ($variedad && $variedad->codigo) [CÓDIGO: {{ $variedad->codigo }}] @endif
                    / Recibidas: {{ number_format($total_recibidas, 0) }}
                    / Total Sembrado: {{ number_format($total_sembradas, 0) }}
                    / Pérdida Acumulada: <span class="text-warning">{{ number_format($total_perdidas, 0) }}</span>
                    / **Inventario Actual:** <span class="badge bg-success">{{ number_format($inventario_actual, 0) }}</span>
                    / **Restante :** <span class="badge bg-info">{{ number_format($restante_por_plantar, 0) }}</span>
                </small>
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-clientes mb-0">
                    <thead>
                        <tr>
                            
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
                                <a href="{{ route('plantacion.show', $p->ID_Plantacion) }}" class="btn btn-sm btn-info "> ver</a>


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


@section('custom_scripts')
<script>

    function aplicarFiltro() {
        var valorBusqueda = document.getElementById('filtroLotesInput').value;
        var rutaIndex = "{{ route('plantacion.index') }}";

        if (valorBusqueda) {
            var url = rutaIndex + '?q=' + encodeURIComponent(valorBusqueda);
            window.location.href = url;
        } else {
            window.location.href = rutaIndex;
        }
    }
</script>
@endsection



@endsection