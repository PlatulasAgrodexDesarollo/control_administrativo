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

    // --- LÓGICA DEL CONTADOR DE DÍAS ---
    $fecha_ingreso = \Carbon\Carbon::parse($endurecimiento->Fecha_Ingreso)->startOfDay();

    if($endurecimiento->Estado_General == 'Finalizado' && $endurecimiento->Fecha_Cierre) {
    $fecha_final = \Carbon\Carbon::parse($endurecimiento->Fecha_Cierre)->startOfDay();
    $dias_transcurridos = (int) $fecha_ingreso->diffInDays($fecha_final);
    } else {
    $dias_transcurridos = (int) $fecha_ingreso->diffInDays(now()->startOfDay());
    }
    @endphp

    {{-- TARJETA DE RESUMEN SUPERIOR --}}
    <div class="row mb-4">
        <div class="col-md-12 text-start">
            <div class="card shadow-sm border-0 {{ $endurecimiento->Estado_General == 'Finalizado' ? 'bg-success' : 'bg-primary' }} text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="fw-bold m-0 text-uppercase" style="font-size: 0.9rem; opacity: 0.9;">
                            <i class="bi bi-clipboard-data me-2"></i>Detalle de Inventario: Endurecimiento
                            @if($endurecimiento->Estado_General == 'Finalizado') (CERRADO) @endif
                        </h5>
                        <h2 class="display-6 fw-bold m-0">{{ number_format($stock_general_planta, 0, '.', ',') }} <small class="fs-6 text-white-50">und. @if($endurecimiento->Estado_General == 'Finalizado') finales @else en nave @endif</small></h2>
                    </div>
                    <div class="text-end">
                        <div class="btn-group mb-2 shadow-sm">
                            {{-- BOTON TRAZABILIDAD ELIMINADO SEGUN PETICION --}}
                            @if($endurecimiento->Estado_General != 'Finalizado')
                            <button type="button" class="btn btn-outline-light fw-bold" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                                <i class="bi bi-check-all me-1"></i> FINALIZAR ETAPA
                            </button>
                            @endif
                        </div>

                        <span class="badge bg-white {{ $endurecimiento->Estado_General == 'Finalizado' ? 'text-success' : 'text-primary' }} px-3 py-2 mb-1 fw-bold d-block shadow-sm">
                            <i class="bi bi-alarm me-1"></i> DURACIÓN: {{ $dias_transcurridos }} DÍAS
                        </span>
                        <div class="small fw-bold text-uppercase d-block" style="opacity: 0.8; font-size: 0.65rem;">
                            @if($endurecimiento->Estado_General == 'Finalizado')
                            Cerrado el: {{ \Carbon\Carbon::parse($endurecimiento->Fecha_Cierre)->translatedFormat('d \d\e F, Y') }}
                            @else
                            Ingreso: {{ $fecha_ingreso->translatedFormat('d \d\e F \d\e Y') }}
                            @endif
                            | Total Recibido: {{ number_format($total_entrada_global, 0, '.', ',') }} und.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 text-start">
        <div class="card-body">
            <h6 class="mb-4 text-uppercase small fw-bold text-secondary border-bottom pb-2">
                Historial de Mermas e Inventario por Lote:
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
                $m_plantacion = (int) DB::table('plantacion')
                ->where('ID_Llegada', $lote->ID_Llegada)
                ->where('ID_Variedad', $lote->pivot->variedad_id)
                ->sum('cantidad_perdida');
                }

                $m_aclimatacion = (int) ($datos_origen->merma_acumulada_lote ?? 0);
                $entrada_neta_etapa = (int) ($datos_origen ? ($datos_origen->cantidad_inicial_lote - $m_aclimatacion) : 0);

                $merma_endurecimiento = (int) ($lote->pivot->merma_acumulada_lote ?? 0);
                $merma_seleccion_final = (int) ($lote->pivot->merma_seleccion_final ?? 0); // Agregado para vista

                // STOCK LOTE ACTUALIZADO CON SELECCIÓN FINAL
                $stock_actual_lote = $entrada_neta_etapa - $merma_endurecimiento - $merma_seleccion_final;
                $stock_color = $stock_actual_lote > 0 ? 'text-success' : 'text-danger';

                $modalId = "modalM_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
                @endphp

                <li class="py-3 px-4 border-bottom hover-bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <strong class="text-dark d-block fs-6 text-uppercase">{{ $nombre_lote_es }}</strong>
                            {{-- LÍNEA AGREGADA: Muestra la fecha de llegada debajo del nombre del lote --}}
                            <div class="small text-muted mb-1">
                <i class="bi bi-calendar3 me-1"></i>
                @php
                    // Obtenemos la fecha más antigua de plantación para este lote y variedad
                    $fecha_inicio_plantacion = DB::table('plantacion')
                        ->where('ID_Llegada', $lote->ID_Llegada)
                        ->where('ID_Variedad', $lote->pivot->variedad_id)
                        ->min('Fecha_Plantacion');
                @endphp
                
                <span class="text-uppercase" style="font-size: 0.75rem;"></span>
                {{ $fecha_inicio_plantacion ? \Carbon\Carbon::parse($fecha_inicio_plantacion)->format('d/m/Y') : 'Sin fecha' }}
            </div>

                            <div class="mt-1">
                                <span class="badge bg-light text-secondary border small">
                                    variedad:{{ $lote->variedad->nombre ?? 'N/A' }}
                                </span>
                                <span class="badge bg-light text-primary border small">
                                    codigo:{{ $lote->variedad->codigo ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-8 text-md-end">
                            {{-- BLOQUE DE TOTALES: Se ajusta de centro (móvil) a derecha (escritorio) --}}
                            <div class="d-flex flex-wrap justify-content-between justify-content-md-end align-items-center mb-3">
                                <div class="text-center px-2 flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">ENTRADA ETAPA</small>
                                    <span class="fw-bold text-primary fs-6">{{ number_format($entrada_neta_etapa, 0) }}</span>
                                </div>

                                <div class="text-center px-2 border-start flex-fill">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">MERMA ENDUREC.</small>
                                    <span class="fw-bold text-danger fs-6">{{ number_format($merma_endurecimiento, 0) }}</span>
                                </div>

                                <div class="text-center px-2 border-start flex-fill" style="min-width: 100px;">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.60rem;">STOCK LOTE</small>
                                    <div class="fw-bold {{ $stock_color }} fs-5">{{ number_format($stock_actual_lote, 0) }}</div>
                                </div>

                                {{-- BOTÓN: En móviles se va al final para no romper la alineación --}}
                                <div class="ms-md-3 ps-md-3 border-md-start mt-2 mt-md-0 w-100 w-md-auto text-center">
                                    @if($endurecimiento->Estado_General != 'Finalizado')
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                        <i class="bi bi-plus-circle me-1"></i>Merma
                                    </button>
                                    @endif
                                </div>
                            </div>

                            {{-- BLOQUE DE MERMAS DETALLADAS: flex-wrap es vital aquí para dispositivos pequeños --}}
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



<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered text-start">
        <form action="{{ route('endurecimiento.finalizar', $endurecimiento->ID_Endurecimiento) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow text-start">
                <div class="modal-header bg-dark text-white border-0 text-start">
                    <h5 class="modal-title fw-bold">Selección Final y Cierre de Etapa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4 small text-start">Ingrese la merma de selección final por cada lote antes de cerrar.</p>
                    <div class="table-responsive text-start">
                        <table class="table table-sm align-middle text-start">
                            <thead class="bg-light text-start small text-uppercase">
                                <tr>
                                    <th>Lote / Variedad / Cód</th>
                                    <th class="text-center">Stock Actual</th>
                                    <th class="text-center" style="width: 150px;">Merma Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lotes_detallados as $lote)
                                @php
                                $datos_o = DB::table('aclimatacion_variedad')->where('ID_llegada', $lote->ID_Llegada)->where('variedad_id', $lote->pivot->variedad_id)->first();
                                $ent = $datos_o ? ($datos_o->cantidad_inicial_lote - $datos_o->merma_acumulada_lote) : 0;
                                $stk = (int) ($ent - ($lote->pivot->merma_acumulada_lote ?? 0));
                                $nombre_f = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold small text-start">{{ $nombre_f }}</div>
                                        <div class="text-muted text-start" style="font-size: 0.7rem;">
                                            {{ $lote->variedad->nombre }} ({{ $lote->variedad->codigo ?? 'N/A' }})
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold text-primary">{{ number_format($stk, 0) }}</td>
                                    <td>
                                        <input type="number" name="merma_final[{{ $lote->ID_Llegada }}][{{ $lote->pivot->variedad_id }}]"
                                            class="form-control form-control-sm text-center fw-bold" value="0" min="0" max="{{ $stk }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow">CONFIRMAR CIERRE</button>
                </div>
            </div>
        </form>
    </div>
</div>

@foreach ($lotes_detallados as $lote)
@php
$datos_o = DB::table('aclimatacion_variedad')->where('ID_llegada', $lote->ID_Llegada)->where('variedad_id', $lote->pivot->variedad_id)->first();
$ent = $datos_o ? ($datos_o->cantidad_inicial_lote - $datos_o->merma_acumulada_lote) : 0;
$stk = (int) ($ent - ($lote->pivot->merma_acumulada_lote ?? 0));
$modalId = "modalM_" . $lote->ID_Llegada . "_" . $lote->pivot->variedad_id;
$nombre_modal = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana);
@endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <form action="{{ route('endurecimiento.registrarMerma', $endurecimiento->ID_Endurecimiento) }}" method="POST">
            @csrf
            <div class="modal-content text-start">
                <div class="modal-header bg-danger text-white border-0 text-start">
                    <h5 class="modal-title fw-bold text-start">Registrar Merma</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <input type="hidden" name="id_llegada" value="{{ $lote->ID_Llegada }}">
                    <input type="hidden" name="id_variedad" value="{{ $lote->pivot->variedad_id }}">

                    <div class="mb-3 text-start">
                        <small class="text-muted text-uppercase d-block text-start">{{ $nombre_modal }}</small>
                        <div class="fw-bold mb-2 text-start">{{ $lote->variedad->nombre }} ({{ $lote->variedad->codigo ?? 'N/A' }})</div>
                        <label class="form-label fw-bold text-start">Cantidad de Plantas Muertas:</label>
                        <input type="number" name="cantidad_merma" class="form-control form-control-lg text-center fw-bold" required min="1" max="{{ $stk }}">
                        <div class="form-text mt-2 text-danger text-start">Máximo disponible: {{ number_format($stk, 0) }} und.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 text-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow">Guardar Merma</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection