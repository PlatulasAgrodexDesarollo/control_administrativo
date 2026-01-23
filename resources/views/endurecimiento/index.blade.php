@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4 text-start">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark">Gestión de Endurecimiento</h1>
        <a href="{{ route('endurecimiento.create') }}" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Nueva Etapa
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3">Fecha Ingreso</th>
                            <th>Origen y Variedades</th>
                            <th class="text-center">Total Und.</th>
                            <th class="text-center">Estado</th>
                            <th>Responsable</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($endurecimientos as $e)
                        @php
                            $lote_referencia = $e->lotes->first();
                            $identificador_espanol = 'N/A';
                            $fecha_llegada_formateada = null;

                            if ($lote_referencia) {
                                $fecha_llegada = DB::table('llegada_planta')
                                    ->where('ID_Llegada', $lote_referencia->ID_Llegada)
                                    ->value('Fecha_Llegada');

                                if ($fecha_llegada) {
                                    $carbon_fecha = \Carbon\Carbon::parse($fecha_llegada);
                                    $fecha_llegada_formateada = $carbon_fecha->format('d/m/Y');
                                    $meses_es = [
                                        1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun',
                                        7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'
                                    ];
                                    $mes_es = $meses_es[$carbon_fecha->month];
                                    $semana_del_mes = ceil($carbon_fecha->day / 7);
                                    $identificador_espanol = "Lote " . $semana_del_mes . " (" . $mes_es . " " . $carbon_fecha->year . ")";
                                }
                            }

                            $variedades_resumen = $e->lotes->groupBy('variedad.nombre')->map(function ($lotes) {
                                $total_neto_procedente = $lotes->sum(function($lote) {
                                    $datos_origen = DB::table('aclimatacion_variedad')
                                        ->where('ID_llegada', $lote->ID_Llegada)
                                        ->where('variedad_id', $lote->pivot->variedad_id)
                                        ->first();
                                    return $datos_origen ? ($datos_origen->cantidad_inicial_lote - $datos_origen->merma_acumulada_lote) : 0;
                                });

                                return [
                                    'nombre' => $lotes->first()->variedad->nombre ?? 'N/A',
                                    'codigo' => $lotes->first()->variedad->codigo ?? 'N/A',
                                    'cantidad' => $total_neto_procedente,
                                ];
                            });

                            $dato_maestro_acli = 0;
                            if($lote_referencia) {
                                $pivot_acli = DB::table('aclimatacion_variedad')->where('ID_llegada', $lote_referencia->ID_Llegada)->first();
                                if($pivot_acli) {
                                    $dato_maestro_acli = DB::table('aclimatacion')->where('ID_Aclimatacion', $pivot_acli->aclimatacion_id)->value('cantidad_final') ?? 0;
                                }
                            }
                        @endphp

                        <tr>
                            <td class="ps-3">{{ \Carbon\Carbon::parse($e->Fecha_Ingreso)->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex flex-column text-start">
                                    <strong class="text-dark">{{ $identificador_espanol }}</strong>
                                    
                                    @if($fecha_llegada_formateada)
                                        <small class="text-muted mb-1" >
                                              <i class="bi bi-calendar3 me-1"></i>{{ $fecha_llegada_formateada }}
                                        </small>
                                    @endif
                                    
                                    <ul class="list-unstyled small mb-0 mt-1">
                                        @foreach ($variedades_resumen as $v)
                                        <li class="d-flex justify-content-between border-bottom border-light py-1">
                                            {{-- LÍNEA MODIFICADA: Nombre (Código) en una sola etiqueta --}}
                                            <span class="text-muted">Var: {{ $v['nombre'] }} ({{ $v['codigo'] }}):</span>
                                            <span class="fw-bold ms-3 text-dark">{{ number_format($v['cantidad']) }} und.</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-primary fs-5">{{ number_format($dato_maestro_acli) }}</span>
                            </td>
                            
                            <td class="text-center">
                                @if(trim($e->Estado_General) == 'Finalizado')
                                    <span class="badge bg-success shadow-sm px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> FINALIZADO
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark shadow-sm px-3 py-2">
                                        <i class="bi bi-clock-history me-1"></i> EN PROCESO
                                    </span>
                                @endif
                            </td>

                            <td class="text-dark">{{ $e->responsable->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('endurecimiento.show', $e->ID_Endurecimiento) }}" 
                                   class="btn btn-sm btn-outline-dark fw-bold px-3">
                                    <i class="bi bi-search me-1"></i> GESTIONAR
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection