@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4 text-start">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark">Inventario Inicial (Plantas Recibidas)</h1>
        <a href="{{ route('llegada_planta.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-box-seam me-1"></i> Registrar Nueva Recepción
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Fecha Llegada</th>
                            <th>Identificador Semanal</th>
                            <th>Variedad</th>
                            <th>Código</th>
                            <th class="text-center">Cantidad</th>
                            <th>Operador</th>
                            <th class="text-center">Pre-Aclimatación</th>
                            <th class="text-center pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lotes_llegada as $lote)
                        @php
                            // LÓGICA DE TRADUCCIÓN MANUAL Y CÁLCULO DE SEMANA
                            $fecha = \Carbon\Carbon::parse($lote->Fecha_Llegada);
                            $meses_es = [
                                1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun',
                                7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'
                            ];
                            $mes_es = $meses_es[$fecha->month];
                            
                            // Cálculo de la semana del mes (1 a 5)
                            $semana_del_mes = ceil($fecha->day / 7);
                            
                            // FORMATO SOLICITADO: Lote 2 (Dic 2025)
                            $identificador_espanol = "Lote " . $semana_del_mes . " (" . $mes_es . " " . $fecha->year . ")";
                        @endphp
                        <tr>
                            <td class="ps-3">{{ $fecha->format('d/m/Y') }}</td>
                            
                            {{-- IDENTIFICADOR CORREGIDO: Lote 2 (Dic 2025) --}}
                            <td class="fw-bold text-primary">{{ $identificador_espanol }}</td>
                            
                            <td>
                                <div class="fw-bold">{{ $lote->variedad->nombre ?? 'ERROR' }}</div>
                                @if ($lote->variedad && $lote->variedad->color)
                                    <span class="text-muted small"><i class="bi bi-palette me-1"></i>{{ $lote->variedad->color }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $lote->variedad->codigo ?? 'N/A' }}</span></td>
                            <td class="text-center fw-bold">{{ number_format($lote->Cantidad_Plantas, 0) }}</td>
                            <td>{{ $lote->operadorLlegada->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if ($lote->Pre_Aclimatacion)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Sí</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-shield-x me-1"></i>No</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('llegada_planta.show', $lote->ID_Llegada) }}" class="btn btn-sm btn-info text-white shadow-sm">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                No hay registros de inventario inicial.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection