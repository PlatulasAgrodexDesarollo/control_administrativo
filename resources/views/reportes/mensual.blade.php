@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4 text-start">
    {{-- Título y Filtros --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Análisis Global Mensual</h2>
            <p class="text-secondary mb-0">Existencias físicas considerando plantas recuperadas</p>
        </div>

        <form action="{{ route('reporte.mensual') }}" method="GET" class="d-flex gap-2">
            <select name="mes" class="form-select shadow-sm border-0" style="width: 160px;">
                @foreach($nombresMeses as $key => $m)
                <option value="{{ $key + 1 }}" {{ $mes == ($key + 1) ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <input type="number" name="anio" class="form-control shadow-sm border-0" style="width: 100px;" value="{{ $anio }}">
            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">FILTRAR</button>
        </form>
    </div>

    {{-- TARJETAS DE SUMATORIA --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-3 h-100">
                <small class="opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Ingreso Total</small>
                <h2 class="fw-bold m-0">{{ number_format($totales['ingreso']) }} und.</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white p-3 h-100">
                <small class="opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Mermas Acumuladas</small>
                @php $total_m = ($totales['m_plant'] ?? 0) + ($totales['m_aclim'] ?? 0) + ($totales['m_endur'] ?? 0) + ($totales['m_selec'] ?? 0); @endphp
                <h2 class="fw-bold m-0">{{ number_format($total_m) }} und.</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3 h-100">
                <small class="opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Stock Final Disponible</small>
                <h2 class="fw-bold m-0">{{ number_format($totales['saldo']) }} und.</h2>
            </div>
        </div>
    </div>

    {{-- NUEVA TABLA: RESUMEN CONSOLIDADO POR VARIEDAD --}}
    <div class="mb-3 mt-5">
        <h4 class="fw-bold text-dark"><i class="bi bi- briefcase-fill me-2"></i>Resumen General por Variedad</h4>
        <p class="text-muted small">Totales agrupados de todas las entradas del periodo.</p>
    </div>
    <div class="card shadow-sm border-0 overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 text-center border">
                <thead class="bg-secondary text-white small text-uppercase">
                    <tr>
                        <th class="ps-4 py-2 text-start">Variedad Principal</th>
                        <th>Cant. Lotes</th>
                        <th>Ingreso Total</th>
                        <th>Mermas Totales</th>
                        <th >Total Recup.</th>
                        <th class="bg-success text-white pe-4 text-end">Stock Final</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($resumenVariedades as $rv)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td class="ps-4 text-start">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: {{ $rv->color }}; border: 1px solid #ccc;"></div>
                                <span class="fw-bold">{{ $rv->variedad }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $rv->lotes_contados }}</span></td>
                        <td class="fw-bold text-primary">{{ number_format($rv->total_ingreso) }}</td>
                        <td class="text-danger fw-bold">{{ number_format($rv->m_plant + $rv->m_aclim + $rv->m_endur + $rv->m_selec) }}</td>
                        <td class="text-success fw-bold">{{ number_format($rv->m_recuperada) }}</td>
                        <td class="bg-success bg-opacity-10 fw-bold pe-4 text-end text-success">{{ number_format($rv->saldo_neto) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA 1: ANÁLISIS GLOBAL DETALLADO --}}
    <div class="mb-3 mt-4">
        <h4 class="fw-bold text-dark"><i class="bi bi-list-check me-2"></i>Detalle Individual por Lotes</h4>
    </div>
    <div class="card shadow-sm border-0 overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center border">
                <thead class="bg-dark text-white small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 text-start">Variedad / Código</th>
                        <th class="bg-primary bg-opacity-10 text-primary">Ingreso</th>
                        <th class="border-start">M. Plant.</th>
                        <th class="border-start">Stock Plant.</th>
                        <th class="border-start">M. Aclim.</th>
                        <th class="border-start">Stock Aclim.</th>
                        <th class="border-start">M. Endur.</th>
                        <th class="border-start">M. Selecc.</th>
                        <th class="bg-success text-white fw-bold pe-4 text-end">Stock Final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reporte->sortByDesc('total_ingreso') as $v)
                    <tr>
                        <td class="ps-4 text-start">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-3 shadow-sm"
                                    style="width: 20px; height: 20px; background-color: {{ $v->color ?? '#cccccc' }}; border: 2px solid #6c757d;">
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $v->variedad }}</div>
                                    <small class="text-muted text-uppercase">{{ $v->codigo }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="bg-primary bg-opacity-10 text-primary fw-bold">{{ number_format($v->total_ingreso) }}</td>
                        <td class="text-danger small">
                            {{ number_format($v->m_plant) }}
                            @if($v->m_recuperada > 0)
                                <div class="text-success fw-bold" style="font-size: 0.65rem;">
                                    +{{ number_format($v->m_recuperada) }} Recup.
                                </div>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ number_format($v->total_sembrado) }}</td>
                        <td class="bg-danger bg-opacity-10 text-danger fw-bold">{{ number_format($v->m_aclim) }}</td>
                        <td class="bg-light fw-bold text-dark">{{ number_format($v->total_sembrado - $v->m_aclim) }}</td>
                        <td class="text-danger small">{{ number_format($v->m_endur) }}</td>
                        <td class="text-danger small">{{ number_format($v->m_selec) }}</td>
                        <td class="bg-success text-white fw-bold pe-4 text-end">
                            {{ number_format($v->saldo_neto) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">No hay datos en el periodo seleccionado.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-dark fw-bold border-top border-white">
                    <tr>
                        <td class="ps-4 text-start">TOTALES DEL PERIODO</td>
                        <td>{{ number_format($totales['ingreso']) }}</td>
                        <td>{{ number_format($totales['m_plant']) }}</td>
                        <td class="text-info">{{ number_format($totales['sembrado']) }}</td>
                        <td>{{ number_format($totales['m_aclim']) }}</td>
                        <td class="text-info">{{ number_format($totales['sembrado'] - $totales['m_aclim']) }}</td>
                        <td>{{ number_format($totales['m_endur']) }}</td>
                        <td>{{ number_format($totales['m_selec']) }}</td>
                        <td class="bg-success text-white fs-5 pe-4 text-end">
                            {{ number_format($totales['saldo']) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection