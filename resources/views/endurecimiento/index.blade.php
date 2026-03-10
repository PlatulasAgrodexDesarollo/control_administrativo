@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4 text-start">
    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark">Gestión de Endurecimiento</h1>
        <a href="{{ route('endurecimiento.create') }}" class="btn btn-primary shadow-sm fw-bold rounded-pill px-4">
            <i class="bi bi-plus-circle me-1"></i> Nueva Etapa
        </a>
    </div>

    {{-- TABLA DE CONTENIDO --}}
    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3 ps-4">Fecha Ingreso</th>
                            <th>Origen (Lote y Variedades)</th>
                            <th class="text-center">Total Unidades</th>
                            <th class="text-center">Estado</th>
                            <th>Responsable</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($endurecimientos as $e)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">
                                {{ \Carbon\Carbon::parse($e->Fecha_Ingreso)->format('d/m/Y') }}
                            </td>
                            <td>
                                @php 
                                    // Extraemos el primer detalle para el nombre del lote
                                    $primerDetalle = $e->detalles->first();
                                    $nombre_lote_cabecera = "N/A";

                                    if ($primerDetalle) {
                                        $f = \Carbon\Carbon::parse($primerDetalle->Fecha_Llegada);
                                        $meses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
                                        $nombre_lote_cabecera = "Lote " . ceil($f->day / 7) . " (" . $meses[$f->month] . " " . $f->year . ")";
                                    }
                                @endphp

                                <div class="d-flex flex-column">
                                    {{-- Nombre del Lote (Única vez por fila) --}}
                                    <strong class="small mb-2 text-dark text-uppercase" style="letter-spacing: 0.5px;">
                                        <i class="bi bi-box-seam me-1 text-primary"></i> {{ $nombre_lote_cabecera }}
                                    </strong>

                                    {{-- Lista de Variedades --}}
                                    <ul class="list-unstyled small mb-0">
                                        @foreach ($e->detalles as $v)
                                        <li class="d-flex justify-content-between border-bottom border-light py-1" style="max-width: 350px;">
                                            <span class="text-muted">
                                                <i class="bi bi-caret-right-fill text-primary" style="font-size: 0.6rem;"></i> 
                                                Var: {{ $v->var_nombre }} ({{ $v->var_codigo }}):
                                            </span>
                                            <span class="fw-bold text-end ms-3 text-dark">{{ number_format($v->cantidad_plantas) }} und.</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3 fs-6 shadow-sm">
                                    {{ number_format($e->cantidad_inicial) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 {{ trim($e->Estado_General) == 'Finalizado' ? 'bg-success' : 'bg-info text-dark' }}">
                                    {{ strtoupper($e->Estado_General) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                <i class="bi bi-person-circle me-1"></i> {{ $e->responsable_nombre ?? 'N/A' }}
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('endurecimiento.show', $e->ID_Endurecimiento) }}" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-gear-fill me-1"></i> GESTIONAR
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0 fw-bold">No hay procesos de endurecimiento registrados.</p>
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