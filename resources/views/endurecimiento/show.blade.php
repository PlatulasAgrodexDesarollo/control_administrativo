@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4 text-start">
    @php
    \Carbon\Carbon::setLocale('es');
    $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    // 1. CÁLCULO TOTAL GLOBAL
    $total_entrada_global = (int) $lotes_detallados->sum(function($lote) {
        $datos_acli = DB::table('aclimatacion_variedad')
            ->where('ID_llegada', $lote->ID_Llegada)
            ->where('variedad_id', $lote->pivot->variedad_id)
            ->first();
        return $datos_acli ? ($datos_acli->cantidad_inicial_lote - $datos_acli->merma_acumulada_lote) : 0;
    });

    // 2. CÁLCULO DE STOCK GENERAL ACTUAL
    $stock_general_planta = 0;
    foreach($lotes_detallados as $lote) {
        $datos_acli = DB::table('aclimatacion_variedad')
            ->where('ID_llegada', $lote->ID_Llegada)
            ->where('variedad_id', $lote->pivot->variedad_id)
            ->first();
        $entrada_neta = $datos_acli ? ($datos_acli->cantidad_inicial_lote - $datos_acli->merma_acumulada_lote) : 0;
        $merma_actual = $lote->pivot->merma_acumulada_lote ?? 0;
        $merma_seleccion_final = $lote->pivot->merma_seleccion_final ?? 0;

        $stock_general_planta += ($entrada_neta - $merma_actual - $merma_seleccion_final);
    }
    @endphp

    <div class="row mb-4">
        <div class="col-md-12 text-start">
            <div class="card shadow-sm border-0 {{ $endurecimiento->Estado_General == 'Finalizado' ? 'bg-success' : 'bg-primary' }} text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="fw-bold m-0 text-uppercase" style="font-size: 0.9rem; opacity: 0.9;">
                            <i class="bi bi-clipboard-data me-2"></i>Detalle de Inventario: Endurecimiento
                            @if($endurecimiento->Estado_General == 'Finalizado') (CERRADO) @endif
                        </h5>
                        <h2 class="display-6 fw-bold m-0">{{ number_format($stock_general_planta, 0, '.', ',') }} <small class="fs-6 text-white-50">und. actuales</small></h2>
                    </div>
                    <div class="text-end">
                        <div class="btn-group mb-2 shadow-sm">
                            @if($endurecimiento->Estado_General != 'Finalizado')
                            <button type="button" class="btn btn-outline-light fw-bold" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                                <i class="bi bi-check-all me-1"></i> FINALIZAR ETAPA
                            </button>
                            @endif
                        </div>
                        <div class="small fw-bold text-uppercase d-block" style="opacity: 0.8; font-size: 0.65rem;">
                            Ingreso General: {{ \Carbon\Carbon::parse($endurecimiento->Fecha_Ingreso)->translatedFormat('d \d\e F \d\e Y') }}
                            | Recibido: {{ number_format($total_entrada_global, 0, '.', ',') }} und.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 text-start">
        <div class="card-body">
            <h6 class="mb-4 text-uppercase small fw-bold text-secondary border-bottom pb-2">
                Historial de Mermas e Inventario por Variedad:
            </h6>

            <ul class="list-unstyled border rounded bg-white overflow-hidden text-start">
                @foreach ($lotes_detallados as $lote)
                @php
                $datos_origen = DB::table('aclimatacion_variedad')
                    ->where('ID_llegada', $lote->ID_Llegada)
                    ->where('variedad_id', $lote->pivot->variedad_id)
                    ->first();

                $nombre_lote_es = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana);
                
                $m_plantacion = (int) ($datos_origen->merma_inicial_plantacion ?? $lote->pivot->merma_inicial_plantacion ?? 0);
                if($m_plantacion == 0) {
                    $m_plantacion = (int) DB::table('plantacion')->where('ID_Llegada', $lote->ID_Llegada)->where('ID_Variedad', $lote->pivot->variedad_id)->sum('cantidad_perdida');
                }
                
                $m_aclimatacion = (int) ($datos_origen->merma_acumulada_lote ?? 0);
                $entrada_neta_etapa = (int) ($datos_origen ? ($datos_origen->cantidad_inicial_lote - $m_aclimatacion) : 0);
                
                $merma_endurecimiento = (int) ($lote->pivot->merma_acumulada_lote ?? 0);
                $merma_seleccion_final = (int) ($lote->pivot->merma_seleccion_final ?? 0);
                
                $stock_actual_lote = $entrada_neta_etapa - $merma_endurecimiento - $merma_seleccion_final;

                // --- CÁLCULO DE PORCENTAJE DE PÉRDIDA ---
                $porcentaje_v = ($entrada_neta_etapa > 0) ? ($merma_endurecimiento * 100) / $entrada_neta_etapa : 0;

                $is_cerrado_variedad = ($lote->pivot->Estado_Lote == 'Finalizado');
                
                // RED DE SEGURIDAD PARA CONTADOR: Si created_at es null, usa fecha de ingreso general
                $fecha_inicio_real = $lote->pivot->created_at 
                    ? \Carbon\Carbon::parse($lote->pivot->created_at) 
                    : \Carbon\Carbon::parse($endurecimiento->Fecha_Ingreso);
                
                $fecha_final_conteo = $lote->pivot->fecha_finalizado 
                    ? \Carbon\Carbon::parse($lote->pivot->fecha_finalizado) 
                    : \Carbon\Carbon::now();
                
                $dias_variedad = (int) $fecha_inicio_real->startOfDay()->diffInDays($fecha_final_conteo->startOfDay());

                $modalId = "modalM_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
                $modalCInd = "modalCInd_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
                @endphp

                <li class="py-3 px-4 border-bottom {{ $is_cerrado_variedad ? 'bg-light' : 'hover-bg-light' }}">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-start">
                            <strong class="text-dark d-block fs-6 text-uppercase">{{ $nombre_lote_es }}</strong>
                            <div class="mt-1">
                                <span class="badge bg-light text-secondary border small">variedad:{{ $lote->variedad->nombre ?? 'N/A' }}</span>
                                <span class="badge bg-light text-primary border small">codigo:{{ $lote->variedad->codigo ?? 'N/A' }}</span>
                                <span class="badge {{ $is_cerrado_variedad ? 'bg-secondary' : 'bg-dark' }} small">
                                    <i class="bi bi-stopwatch me-1"></i>{{ $dias_variedad }} días
                                </span>
                                @if($is_cerrado_variedad)
                                    <span class="badge bg-success small"><i class="bi bi-check-circle me-1"></i>LISTO</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8 text-md-end">
                            <div class="d-flex flex-wrap justify-content-between justify-content-md-end align-items-center mb-3">
                                <div class="text-center px-2 flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">ENTRADA</small>
                                    <span class="fw-bold text-primary fs-6">{{ number_format($entrada_neta_etapa, 0) }}</span>
                                </div>

                                <div class="text-center px-2 border-start flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">MERMA ENDUREC.</small>
                                    <span class="fw-bold text-danger fs-6">{{ number_format($merma_endurecimiento, 0) }}</span>
                                </div>

                                <div class="text-center px-2 border-start flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">% PÉRDIDA</small>
                                    <span class="fw-bold {{ $porcentaje_v > 6 ? 'text-danger' : 'text-success' }} fs-6">
                                        {{ number_format($porcentaje_v, 2) }}%
                                    </span>
                                </div>

                                <div class="text-center px-2 border-start flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">STOCK ACTUAL</small>
                                    <div class="fw-bold {{ $stock_actual_lote > 0 ? 'text-success' : 'text-danger' }} fs-5">{{ number_format($stock_actual_lote, 0) }}</div>
                                </div>

                                <div class="ms-md-3 d-flex gap-2 mt-2 mt-md-0 w-100 w-md-auto justify-content-center">
                                    @if($endurecimiento->Estado_General != 'Finalizado' && !$is_cerrado_variedad)
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                        <i class="bi bi-plus-circle me-1"></i>Merma
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#{{ $modalCInd }}">
                                        <i class="bi bi-door-closed me-1"></i>Cerrar
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-1 border-top pt-2">
                                <span class="badge bg-light text-dark border py-2">
                                    <span class="text-muted fw-normal small">M. Plantación:</span>
                                    <span class="text-danger fw-bold">{{ number_format($m_plantacion, 0) }}</span>
                                </span>
                                <span class="badge bg-light text-dark border py-2">
                                    <span class="text-muted fw-normal small">M. Aclimatación:</span>
                                    <span class="text-danger fw-bold">{{ number_format($m_aclimatacion, 0) }}</span>
                                </span>
                                <span class="badge bg-light text-dark border py-2">
                                    <span class="text-muted fw-normal small">M. Selección:</span>
                                    <span class="text-warning fw-bold">{{ number_format($merma_seleccion_final, 0) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

{{-- MODAL CIERRE COMPLETO SIMPLIFICADO --}}
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <form action="{{ route('endurecimiento.finalizar', $endurecimiento->ID_Endurecimiento) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow text-start">
                <div class="modal-header bg-dark text-white border-0 text-start">
                    <h5 class="modal-title fw-bold">Confirmar Cierre de Etapa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
                    <p class="mb-0">¿Estás seguro de que deseas finalizar la etapa de endurecimiento?</p>
                    <p class="text-muted small">Esta acción cerrará todos los registros y guardará el stock final de <strong>{{ number_format($stock_general_planta) }}</strong> unidades.</p>
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow">CONFIRMAR Y FINALIZAR</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODALES INDIVIDUALES --}}
@foreach ($lotes_detallados as $lote)
@php
$datos_o = DB::table('aclimatacion_variedad')->where('ID_llegada', $lote->ID_Llegada)->where('variedad_id', $lote->pivot->variedad_id)->first();
$ent = $datos_o ? ($datos_o->cantidad_inicial_lote - $datos_o->merma_acumulada_lote) : 0;
$stk = (int) ($ent - ($lote->pivot->merma_acumulada_lote ?? 0));
$modalId = "modalM_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
$modalCInd = "modalCInd_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <form action="{{ route('endurecimiento.registrarMerma', $endurecimiento->ID_Endurecimiento) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Registrar Merma</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-start">
                    <input type="hidden" name="id_llegada" value="{{ $lote->ID_Llegada }}">
                    <input type="hidden" name="id_variedad" value="{{ $lote->pivot->variedad_id }}">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Plantas Muertas ({{ $lote->variedad->nombre }}):</label>
                        <input type="number" name="cantidad_merma" class="form-control form-control-lg text-center fw-bold text-danger" required min="1" max="{{ $stk }}">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Guardar Merma</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="{{ $modalCInd }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <form action="{{ route('endurecimiento.finalizarVariedad', $endurecimiento->ID_Endurecimiento) }}" method="POST">
            @csrf
            <input type="hidden" name="id_llegada" value="{{ $lote->ID_Llegada }}">
            <input type="hidden" name="id_variedad" value="{{ $lote->pivot->variedad_id }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Cerrar Variedad Individual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <label class="form-label fw-bold">Merma de Selección Final:</label>
                    <input type="number" name="merma_final_individual" class="form-control form-control-lg text-center fw-bold" value="0" min="0" max="{{ $stk }}">
                    <small class="text-muted mt-2 d-block">Al cerrar, el contador de días para esta variedad se detendrá.</small>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow">FINALIZAR VARIEDAD</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection