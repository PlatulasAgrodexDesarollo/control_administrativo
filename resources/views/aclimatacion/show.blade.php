@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container py-4">
    <h1 class="h3 mb-4">Gestión de Etapa: Aclimatación </h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
    use Carbon\Carbon;

   
    $lotes_aclimatados = $aclimatacion->lotesAclimatados;
    


   
    $fecha_primer_registro = $aclimatacion->fecha_primer_registro_curso ?? $aclimatacion->Fecha_Ingreso;

    $fecha_inicio_conteo = Carbon::parse($fecha_primer_registro);
    $fecha_fin = $aclimatacion->fecha_cierre ? Carbon::parse($aclimatacion->fecha_cierre) : Carbon::now();
    

    $dias_reales = floor($fecha_inicio_conteo->diffInDays($fecha_fin));
    
  
    if (!$aclimatacion->fecha_cierre && $fecha_inicio_conteo->isToday()) {
        $dias_reales = 0;
    }

    // Clasificación de Status (Simplificada: Solo Activa/Finalizada)
    if ($aclimatacion->fecha_cierre) {
        $clase_badge = 'bg-success';
        $texto_status = 'Finalizada';
    } else {
        $clase_badge = 'bg-info';
        $texto_status = 'Activa';
    }
    
    // --- CÁLCULOS DE INVENTARIO ---
    // El stock inicial se suma del pivot
    $stock_inicial_aclimatacion = $aclimatacion->lotesAclimatados->sum('pivot.cantidad_inicial_lote');

    $merma_aclimatacion_acumulada = $aclimatacion->merma_etapa ?? 0;

    $inventario_pasante_calculado = $stock_inicial_aclimatacion - $merma_aclimatacion_acumulada;
    
   
  
    $total_merma_plantacion = $lotes_detallados->sum('merma_inicial_plantacion'); 
    // --- FIN CÁLCULOS DE INVENTARIO ---

    $meses_espanol_abr = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    ];
    @endphp

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0">Resumen de la Etapa</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha de Ingreso a Etapa:</strong> {{ \Carbon\Carbon::parse($aclimatacion->Fecha_Ingreso)->format('d/m/Y') }}</p>
                    <p><strong>Fecha de Inicio de Conteo:</strong> {{ $fecha_inicio_conteo->format('d/m/Y') }}</p>
                    <p><strong>Responsable:</strong> {{ $aclimatacion->operadorResponsable->nombre ?? 'N/A' }}</p>

                    <h6 class="mt-4 mb-2">Lotes y Variedades </h6>
                    <ul class="list-unstyled border p-2 rounded">

                        @foreach ($lotes_aclimatados as $lote)
                        @php
                       
                        $fecha_carbon = Carbon::parse($lote->Fecha_Ingreso);
                        $abr_espanol = $meses_espanol_abr[$fecha_carbon->month];
                        $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon->year;

                        $nombre_lote_traducido = $lote->nombre_lote_semana ?? 'N/A';
                        $patron_mes_anio = '/\b[A-Za-z]{3,}\s\d{4}\b/';

                        $nombre_lote_traducido = preg_replace(
                            $patron_mes_anio,
                            $fecha_espanol_manual,
                            $nombre_lote_traducido
                        );

                        $estado_lote = $lote->pivot->Estado_Inicial_Lote ?? 'Sin Estado';

                        $clase_estado = match ($estado_lote) {
                            'Normal' => 'bg-primary',
                            'Contaminada' => 'bg-danger',
                            'Debil' => 'bg-warning text-dark',
                            default => 'bg-secondary',
                        };
                        @endphp

                        <li class="small mb-1">
                            <i class="bi bi-tag"></i>
                            <strong>{{ $nombre_lote_traducido }}</strong>
                            <span class="badge {{ $clase_estado }} me-2">{{ $estado_lote }}</span>


                            Var:{{ $lote->variedad->nombre ?? 'N/A' }}
                            <span class="badge bg-light text-dark">Cód: {{ $lote->variedad->codigo ?? 'N/A' }}</span>
                            <span class="badge bg-light text-dark">{{ number_format($lote->pivot->cantidad_plantas) }} und.</span>

                        </li>
                        @if (!$loop->last)
                        <hr class="my-1">
                        @endif
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-6 border-start ps-4">
                    {{-- Esta línea usa el cálculo corregido: la suma de la cantidad inicial de lotes --}}
                    <p><strong>Total Unidades Ingresadas:</strong> {{ number_format($stock_inicial_aclimatacion, 0) }} und.</p>
                    <p><strong>Merma Acumulada de Aclimatación:</strong> <span class="badge bg-danger fs-6">{{ number_format($merma_aclimatacion_acumulada, 0) }} und.</span></p>

                    <hr>

                    <p class="mb-2"><strong>Días Reales en Etapa:</strong> <span class="badge {{ $clase_badge }} fs-6">{{ $dias_reales }} días</span></p>
                    <p class="mt-0 small text-muted">Status: **{{ $texto_status }}**</p>
                </div>
            </div>
        </div>
    </div>

    
    {{--- 2. BLOQUE DE MERMA Y CIERRE DE ETAPA ---}}
<h2 class="h4 mt-5">Control de Inventario y Cierre de Etapa</h2>

@if ($aclimatacion->fecha_cierre)
    {{-- 1. BLOQUE CUANDO LA ETAPA ESTÁ CERRADA --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5><i class="bi bi-check-circle"></i> Etapa Cerrada</h5>
        </div>
        <div class="card-body">
            
            {{-- RESUMEN GENERAL --}}
            <p><strong>Fecha de Cierre:</strong> {{ \Carbon\Carbon::parse($aclimatacion->fecha_cierre)->format('d/m/Y H:i') }}</p>
            <p><strong>Stock Inicial Global (Aclimatación):</strong> {{ number_format($stock_inicial_aclimatacion) }} unidades</p>
            
            <hr class="my-2">
            
          
            <p class="text-danger small mb-1">
                **Merma Acumulada (Plantación):** {{ number_format($total_merma_plantacion) }} unidades
            </p>
            <p class="text-danger fs-6">
                **Merma Acumulada (Aclimatación):** {{ number_format($merma_aclimatacion_acumulada) }} unidades
            </p>
            
            <hr class="my-2">
            
            <p class="text-primary fs-5"><strong>Stock Pasante Final:</strong> {{ number_format($inventario_pasante_calculado) }} unidades</p>

            <hr class="my-3">

            
            <h6 class="mt-4 mb-2">Inventario Final por Lote:</h6>
            
            <ul class="list-unstyled border p-3 rounded bg-light">
                @php
                    $lotes_activos = $lotes_detallados->filter(fn($lote) => ($lote['cantidad_ingresada'] ?? 0) > 0);
                @endphp

                @if ($lotes_activos->isEmpty())
                    <li class="text-danger small">No se encontraron lotes activos con inventario en esta etapa.</li>
                @else
                    @foreach ($lotes_activos as $lote)
                        @php
                            $stock_final_lote = ($lote['cantidad_ingresada'] ?? 0) - ($lote['merma_acumulada_lote'] ?? 0);
                            $merma_aclimatacion = $lote['merma_acumulada_lote'] ?? 0;
                            $merma_plantacion = $lote['merma_inicial_plantacion'] ?? 0; // Usando el nuevo campo
                            $stock_color = $stock_final_lote > 0 ? 'text-success' : 'text-danger';
                        @endphp
                        <li class="small py-1 border-bottom d-flex justify-content-between">
                            <div>
                                <strong class="text-dark">{{ $lote['nombre'] }}</strong> 
                                <span class="text-muted fst-italic">({{ $lote['variedad_nombre'] }})</span>
                            </div>
                            <div class="text-end small">
                                **Merma Plantación:** <span class="fw-bold text-danger me-2">{{ number_format($merma_plantacion) }} und.</span> 
                                **Merma Aclimatación:** <span class="fw-bold text-danger me-2">{{ number_format($merma_aclimatacion) }} und.</span>
                                **Stock Final:** <span class="fw-bold {{ $stock_color }}">{{ number_format($stock_final_lote) }} und.</span>
                            </div>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

@else
    {{-- 2. BLOQUE CUANDO LA ETAPA ESTÁ ABIERTA --}}

  
    <div class="card shadow p-2 mb-4">
        <div class="card-header bg-warning text-dark">
            <h5><i class="bi bi-x-octagon"></i> Registro de Pérdidas por Lote (Merma)</h5>
        </div>

        @if ($lotes_detallados->isEmpty())
            <p class="p-3 mb-0 text-danger">Error: No se encontraron lotes para esta etapa.</p>
        @else

        <div class="card-body">
            <p class="small text-muted mb-4">
                El stock total inicial de la etapa fue de <strong>{{ number_format($stock_inicial_aclimatacion, 0) }}</strong> unidades.
                <br>Merma Total Acumulada en la Etapa: <strong class="text-danger">{{ number_format($merma_aclimatacion_acumulada ?? 0, 0) }}</strong> unidades.
            </p>

            {{-- INICIO DEL CONTENEDOR CON SCROLL --}}
            <div class="lotes-merma-scroll" style="max-height: 350px; overflow-y: auto; padding-right: 15px;"> 

                @foreach ($lotes_detallados as $lote)
                @php
                   
                    $cantidad_ingresada_lote = $lote['cantidad_ingresada'] ?? 0;
                    $merma_acumulada_aclimatacion = $lote['merma_acumulada_lote'] ?? 0; // Merma de aclimatación
                    $merma_plantacion = $lote['merma_inicial_plantacion'] ?? 0; // Merma de plantación
                    $stock_restante_lote = $cantidad_ingresada_lote - $merma_acumulada_aclimatacion;
                    
                  
                    $fecha_a_usar = $lote['Fecha_Ingreso'] ?? $aclimatacion->Fecha_Ingreso;
                    $fecha_carbon_lote = \Carbon\Carbon::parse($fecha_a_usar);
                    $abr_espanol = $meses_espanol_abr[$fecha_carbon_lote->month];
                    $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon_lote->year;
                    $nombre_lote_traducido = $lote['nombre'] ?? 'Lote N/A';
                    $patron_mes_anio = '/\b[A-Za-z]{3,}\s\d{4}\b/';
                    $nombre_lote_traducido = preg_replace($patron_mes_anio, $fecha_espanol_manual, $nombre_lote_traducido);
                @endphp
                <div class="border p-3 mb-3 rounded">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h6 class="mb-1">{{ $nombre_lote_traducido }}</h6> 
                            <p class="small text-muted mb-0">
                                Var: {{ $lote['variedad_nombre'] }} /
                                <span>Cód: {{ $lote['variedad_codigo'] ?? 'N/A' }}</span>
                                /Inicial: {{ number_format($cantidad_ingresada_lote) }} und.
                            </p>
                            
                           
                            <p class="small mb-1 text-danger">
                                Merma Inicial (Plantación): <strong>{{ number_format($merma_plantacion) }} und.</strong>
                            </p>

                            <p class="small mb-0 
                                @if ($merma_acumulada_aclimatacion > 0) text-danger @else text-muted @endif">
                                Merma Acumulada Aclimatación: <strong>{{ number_format($merma_acumulada_aclimatacion) }} und.</strong>
                            </p>
                            <p class="small mb-0 text-primary">
                                Stock Restante Lote: <strong>{{ number_format($stock_restante_lote) }} und.</strong>
                            </p>

                        </div>

                        <div class="col-md-7">
                            <form action="{{ route('aclimatacion.registrar_merma_lote', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                                @csrf
                                <input type="hidden" name="lote_id" value="{{ $lote['ID_Llegada'] }}">

                                <div class="input-group">
                                    <input type="number" name="cantidad_merma" class="form-control" placeholder="Cant. perdida" required min="1" max="{{ $stock_restante_lote }}">
                                    <button type="submit" class="btn btn-warning">Registrar Merma</button>
                                </div>
                                @error('cantidad_merma') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            {{-- FIN DEL CONTENEDOR CON SCROLL --}}

        </div>
        @endif
    </div>

    {{-- CIERRE DE ETAPA (General) --}}
    <div class="card shadow p-4 mb-4">
        <div class="card-header bg-danger text-white">
            <h5><i class="bi bi-box-arrow-right"></i> Cierre y Finalización de Etapa (General)</h5>
        </div>
        <div class="card-body">
            <p class="small text-muted">Stock actual estimado a pasar a la siguiente etapa: <strong class="text-primary">{{ number_format($inventario_pasante_calculado ?? 0, 0) }}</strong> unidades.</p>
            <form action="{{ route('aclimatacion.cerrar', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-danger" onclick="return confirm('ATENCIÓN: ¿Desea cerrar la etapa? Esta acción es definitiva y finaliza el registro de merma.');">
                    CERRAR ETAPA Y PASAR INVENTARIO
                </button>
            </form>
        </div>
    </div>
@endif 
@endsection