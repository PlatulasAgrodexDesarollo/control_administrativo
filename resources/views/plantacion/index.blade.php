@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Registros de Plantación General</h1>
        <a href="{{ route('plantacion.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Plantación
        </a>
    </div>

    {{-- Buscador --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex gap-2">
                <input type="text" id="filtroLotesInput" class="form-control form-control-lg" 
                       placeholder="Buscar lote, variedad, código, operador o mes..." value="{{ $filtro ?? '' }}">
                <button type="button" onclick="aplicarFiltro()" class="btn btn-success px-4">
                    <i class="bi bi-search"></i> Buscar
                </button>
                @if ($filtro)
                    <a href="{{ route('plantacion.index') }}" class="btn btn-secondary">Limpiar</a>
                @endif
            </div>
        </div>
    </div>

    @foreach ($plantaciones_agrupadas as $id_lote => $plantaciones_del_lote)
        @php
            $lote = $plantaciones_del_lote->first()->loteLlegada;
            $variedad = $lote->variedad ?? null;

            // Traducción de Meses
            $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $nombre_lote_es = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana ?? 'Lote #' . $id_lote);

            // Cálculos del Lote
            $total_recibidas = $lote->Cantidad_Plantas;
            $total_sembradas = $plantaciones_del_lote->sum('cantidad_sembrada');
            $perdida_total = $total_recibidas - $total_sembradas;
            
            // Porcentaje Total del Lote
            $porcentaje_total = ($total_sembradas > 0) ? ($perdida_total * 100) / $total_sembradas : 0;
        @endphp

        <div class="card shadow mb-5 border-0">
            <div class="card-header bg-dark text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        LOTE: <span class="text-warning">{{ $nombre_lote_es }}</span> 
                        <span class="ms-3 badge bg-secondary text-uppercase">
                            {{ $variedad->nombre ?? 'Sin Variedad' }} 
                            @if($variedad && $variedad->codigo) 
                                <span class="text-info ps-1">[{{ $variedad->codigo }}]</span> 
                            @endif
                        </span>
                    </h5>
                    <div class="text-end">
                        <small class="d-block text-light">Total Recibidas:</small>
                        <span class="h5 mb-0 fw-bold">{{ number_format($total_recibidas) }}</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th class="text-start ps-4">Fecha</th>
                                <th class="text-start">Operador Responsable</th>
                                <th>Sembrada</th>
                                <th>Diferencia</th>
                                <th>% Pérdida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($plantaciones_del_lote as $p)
                            @php
                                $diferencia_fila = $total_recibidas - $p->cantidad_sembrada;
                                $porcentaje_fila = ($p->cantidad_sembrada > 0) ? ($diferencia_fila * 100) / $p->cantidad_sembrada : 0;
                            @endphp
                            <tr>
                                <td class="text-start ps-4">{{ \Carbon\Carbon::parse($p->Fecha_Plantacion)->format('d/m/Y') }}</td>
                                <td class="text-start fw-bold">
                                    {{ $p->operadorPlantacion->nombre ?? 'N/A' }}
                                </td>
                                <td class="fw-bold text-success">{{ number_format($p->cantidad_sembrada) }}</td>
                                
                                {{-- Diferencia (Azul si es excedente, Rojo si es pérdida) --}}
                                <td class="fw-bold {{ $diferencia_fila < 0 ? 'text-primary' : 'text-danger' }}">
                                    {{ number_format($diferencia_fila) }}
                                </td>

                                {{-- Porcentaje de Pérdida (Rojo si > 6, Verde si <= 6) --}}
                                <td class="fw-bold {{ $porcentaje_fila > 6 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($porcentaje_fila, 2) }}%
                                </td>

                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('plantacion.edit', $p->ID_Plantacion) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('plantacion.show', $p->ID_Plantacion) }}" class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark text-center">
                            <tr>
                                <td colspan="2" class="text-start ps-4 fw-bold">BALANCE FINAL DEL LOTE</td>
                                <td class="fw-bold text-info">{{ number_format($total_sembradas) }}</td>
                                <td class="fw-bold {{ $perdida_total < 0 ? 'text-primary' : 'text-warning' }}">
                                    {{ number_format($perdida_total) }}
                                </td>
                                <td class="fw-bold {{ $porcentaje_total > 6 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($porcentaje_total, 2) }}%
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>

@section('custom_scripts')
<script>
    function aplicarFiltro() {
        var valor = document.getElementById('filtroLotesInput').value;
        var rutaIndex = "{{ route('plantacion.index') }}";
        window.location.href = valor ? (rutaIndex + '?q=' + encodeURIComponent(valor)) : rutaIndex;
    }
</script>
@endsection

@endsection