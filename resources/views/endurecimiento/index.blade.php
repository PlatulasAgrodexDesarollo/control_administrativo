@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1> Gestión de Endurecimiento</h1>
        <a href="{{ route('endurecimiento.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle"></i> Nueva Etapa
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0"> 
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Fecha Ingreso</th>
                            <th>Días en Etapa</th>
                            <th>Responsable</th>
                            {{-- Esta columna jala el dato exacto de la tabla aclimatacion --}}
                            <th class="text-center">Entrada (Neto Acli)</th>
                            <th class="text-center">Merma Etapa</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($endurecimientos as $e)
                        @php
                            // Buscamos el ID de la aclimatación de origen a través de uno de los lotes
                            $lote_pivot = DB::table('aclimatacion_variedad')
                                ->where('ID_llegada', $e->lotes->first()->ID_Llegada ?? 0)
                                ->first();

                            $dato_maestro_acli = 0;
                            if($lote_pivot) {
                                $dato_maestro_acli = DB::table('aclimatacion')
                                    ->where('ID_Aclimatacion', $lote_pivot->aclimatacion_id)
                                    ->value('cantidad_final'); // Traemos directamente el saldo neto (669)
                            }
                        @endphp

                        <tr>
                            <td class="ps-4">{{ \Carbon\Carbon::parse($e->Fecha_Ingreso)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge rounded-pill bg-light text-primary border border-primary px-3">
                                    {{ $e->dias_en_etapa }} días
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2 text-secondary"></i>
                                    {{ $e->responsable->nombre ?? 'Sin asignar' }}
                                </div>
                            </td>

                            {{-- DATO MAESTRO: Los 669 que vienen de la etapa anterior --}}
                            <td class="text-center fw-bold fs-5 text-dark">
                                {{ number_format($dato_maestro_acli) }}
                            </td>

                            <td class="text-center text-danger fw-bold">
                                <i class="bi bi-graph-down-arrow"></i> {{ number_format($e->merma_total_etapa) }}
                            </td>

                            <td class="text-center">
                                @if($e->Estado_General == 'En Proceso')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> En Proceso</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-check-all"></i> Finalizado</span>
                                @endif
                            </td>
                            
                            <td class="text-center pe-4">
                                <a href="{{ route('endurecimiento.show', $e->ID_Endurecimiento) }}" class="btn btn-sm btn-outline-info shadow-sm">
                                    <i class="bi bi-eye"></i> Gestionar
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($endurecimientos->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted display-4"></i>
                <p class="text-muted mt-2">No hay registros de endurecimiento activos.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection