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
    use Illuminate\Support\Facades\DB;

    // 1. Definición de lotes
    $lotes_aclimatados = $aclimatacion->lotesAclimatados;

    // 2. Lógica del Contador
    $fecha_inicio_conteo = Carbon::parse($aclimatacion->Fecha_Ingreso)->startOfDay();
    $fecha_fin = $aclimatacion->fecha_cierre ? Carbon::parse($aclimatacion->fecha_cierre)->startOfDay() : Carbon::now()->startOfDay();
    $dias_reales = (int) $fecha_inicio_conteo->diffInDays($fecha_fin);

    // 3. Variables de Estado
    if ($aclimatacion->fecha_cierre) {
    $clase_badge = 'bg-success';
    $texto_status = 'Finalizada';
    } else {
    $clase_badge = 'bg-info';
    $texto_status = 'Activa';
    }

    // 4. Cálculos de Inventario
    $stock_inicial_aclimatacion = $lotes_aclimatados->sum('pivot.cantidad_inicial_lote');
    $merma_aclimatacion_acumulada = $aclimatacion->merma_etapa ?? 0;
    $inventario_pasante_calculado = $stock_inicial_aclimatacion - $merma_aclimatacion_acumulada;

    // --- LÓGICA DE RECUPERACIÓN DE MERMA DE PLANTACIÓN (RESUMEN GENERAL) ---
    $total_merma_plantacion = 0;
    foreach($lotes_aclimatados as $lote) {
    $m_p = $lote->pivot->merma_inicial_plantacion;
    if($m_p <= 0) {
        $m_p=DB::table('plantacion')
        ->where('ID_Llegada', $lote->ID_Llegada)
        ->where('ID_Variedad', $lote->pivot->variedad_id)
        ->sum('cantidad_perdida');
        }
        $total_merma_plantacion += $m_p;
        }

        $meses_ing = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $meses_esp = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        @endphp

        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Resumen de la Etapa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de Ingreso a Etapa:</strong> {{ \Carbon\Carbon::parse($aclimatacion->Fecha_Ingreso)->format('d/m/Y') }}</p>
                        <p><strong>Responsable:</strong> {{ $aclimatacion->operadorResponsable->nombre ?? 'N/A' }}</p>

                        <h6 class="mt-4 mb-2">Lotes y Variedades </h6>
                        <ul class="list-unstyled border p-2 rounded">
                            @foreach ($lotes_aclimatados as $lote)
                            @php
                            $nombre_lote_traducido = str_replace($meses_ing, $meses_esp, $lote->nombre_lote_semana ?? 'N/A');
                            $estado_lote = $lote->pivot->Estado_Inicial_Lote ?? 'Sin Estado';
                            $clase_estado = match ($estado_lote) {
                            'Normal' => 'bg-primary',
                            'Contaminada' => 'bg-danger',
                            'Debil' => 'bg-warning text-dark',
                            default => 'bg-secondary',
                            };

                            // CONSULTA DE FECHA INICIO (La fecha más antigua de plantación para este lote y variedad)
                            $fecha_inicio_plantacion = DB::table('plantacion')
                            ->where('ID_Llegada', $lote->ID_Llegada)
                            ->where('ID_Variedad', $lote->ID_Variedad) // Usamos el ID del modelo relacionado
                            ->min('Fecha_Plantacion');
                            @endphp
                            <li class="small mb-1">
                                <i class="bi bi-tag"></i>
                                <strong>{{ $nombre_lote_traducido }}</strong>

                                {{-- Muestra la fecha de inicio de plantación --}}
                                <span class="text-muted ms-1 me-2" style="font-size: 0.85em;">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $fecha_inicio_plantacion ? \Carbon\Carbon::parse($fecha_inicio_plantacion)->format('d/m/Y') : 'S/F' }}
                                </span>

                                <span class="badge {{ $clase_estado }} me-2">{{ $estado_lote }}</span>
                                Var:{{ $lote->variedad->nombre ?? 'N/A' }}
                                <span class="badge bg-light text-dark">Cód: {{ $lote->variedad->codigo ?? 'N/A' }}</span>
                                <span class="badge bg-light text-dark">{{ number_format($lote->pivot->cantidad_plantas) }} und.</span>
                            </li>
                            @if (!$loop->last)
                            <hr class="my-1"> @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-6 border-start ps-4">
                        <p><strong>Total Unidades Ingresadas:</strong> {{ number_format($stock_inicial_aclimatacion, 0) }} und.</p>
                        <p><strong>Merma Acumulada de Aclimatación:</strong> <span class="badge bg-danger fs-6">{{ number_format($merma_aclimatacion_acumulada, 0) }} und.</span></p>
                        <hr>
                        <p class="mb-2"><strong>Días Reales en Etapa:</strong> <span class="badge {{ $clase_badge }} fs-6">{{ $dias_reales }} días</span></p>
                        <p class="mt-0 small text-muted">Status: **{{ $texto_status }}**</p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="h4 mt-5">Control de Inventario y Cierre de Etapa</h2>

        @if ($aclimatacion->fecha_cierre)
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-check-circle"></i> Etapa Cerrada</h5>
            </div>
            <div class="card-body">
                <p><strong>Fecha de Cierre:</strong> {{ \Carbon\Carbon::parse($aclimatacion->fecha_cierre)->format('d/m/Y') }}</p>
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
                    {{-- CAMBIO CRÍTICO: Usamos directamente la relación original para asegurar los datos --}}
                    @foreach ($lotes_aclimatados as $lote_orig)
                    @php
                    $m_aclim_lote = $lote_orig->pivot->merma_acumulada_lote ?? 0;
                    $stock_final_lote = ($lote_orig->pivot->cantidad_inicial_lote ?? 0) - $m_aclim_lote;

                    // Búsqueda forzada de Merma Plantación
                    $m_p_lote = $lote_orig->pivot->merma_inicial_plantacion;
                    if($m_p_lote <= 0) {
                        $m_p_lote=DB::table('plantacion')
                        ->where('ID_Llegada', $lote_orig->ID_Llegada)
                        ->where('ID_Variedad', $lote_orig->pivot->variedad_id)
                        ->sum('cantidad_perdida');
                        }

                        $lote_nombre_esp = str_replace($meses_ing, $meses_esp, $lote_orig->nombre_lote_semana ?? 'Lote N/A');
                        @endphp
                        <li class="small py-1 border-bottom d-flex justify-content-between">
                            <div>
                                <strong class="text-dark">{{ $lote_nombre_esp }}</strong>
                                <span class="text-muted fst-italic">({{ $lote_orig->variedad->nombre ?? 'N/A' }})</span>
                            </div>
                            <div class="text-end small">
                                **Merma Plantación:** <span class="fw-bold text-danger me-2">{{ number_format($m_p_lote) }} und.</span>
                                **Merma Aclimatación:** <span class="fw-bold text-danger me-2">{{ number_format($m_aclim_lote) }} und.</span>
                                **Stock Final:** <span class="fw-bold text-success">{{ number_format($stock_final_lote) }} und.</span>
                            </div>
                        </li>
                        @endforeach
                </ul>
            </div>
        </div>
        @else

        <div class="card shadow p-2 mb-4">
            <div class="card-header bg-warning text-dark">
                <h5><i class="bi bi-x-octagon"></i> Registro de Pérdidas por Lote (Merma)</h5>
            </div>

            <div class="card-body">
                <div class="lotes-merma-scroll" style="max-height: 350px; overflow-y: auto; padding-right: 15px;">
                    @foreach ($lotes_detallados as $lote)
                    @php
                    $vid = $lote['variedad_id'] ?? ($lote['pivot_variedad_id'] ?? null);
                    $lid = $lote['ID_Llegada'] ?? ($lote['pivot_ID_llegada'] ?? null);

                    $m_p_lote = $lote['merma_inicial_plantacion'] ?? 0;
                    if($m_p_lote <= 0 && $vid && $lid) {
                        $m_p_lote=DB::table('plantacion')->where('ID_Llegada', $lid)->where('ID_Variedad', $vid)->sum('cantidad_perdida');
                        }

                        $stock_restante_lote = ($lote['cantidad_ingresada'] ?? 0) - ($lote['merma_acumulada_lote'] ?? 0);
                        $nombre_lote_traducido = str_replace($meses_ing, $meses_esp, $lote['nombre'] ?? 'Lote N/A');
                        @endphp
                        <div class="border p-3 mb-3 rounded">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h6 class="mb-1">{{ $nombre_lote_traducido }}</h6>
                                    <p class="small text-muted mb-0">Var: {{ $lote['variedad_nombre'] ?? 'N/A' }} / Inicial: {{ number_format($lote['cantidad_ingresada'] ?? 0) }} und.</p>
                                    <p class="small mb-1 text-danger">Merma Inicial (Plantación): <strong>{{ number_format($m_p_lote) }} und.</strong></p>
                                    <p class="small mb-0 text-primary">Stock Restante Lote: <strong>{{ number_format($stock_restante_lote) }} und.</strong></p>
                                </div>
                                <div class="col-md-7">
                                    <form action="{{ route('aclimatacion.registrar_merma_lote', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="lote_id" value="{{ $lid }}">
                                        <div class="input-group">
                                            <input type="number" name="cantidad_merma" class="form-control" placeholder="Cant. perdida" required min="1" max="{{ $stock_restante_lote }}">
                                            <button type="submit" class="btn btn-warning">Registrar Merma</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-box-arrow-right"></i> Cierre y Finalización de Etapa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('aclimatacion.cerrar', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Desea cerrar la etapa?');">CERRAR ETAPA Y PASAR INVENTARIO</button>
                </form>
            </div>
        </div>
        @endif
        @endsection