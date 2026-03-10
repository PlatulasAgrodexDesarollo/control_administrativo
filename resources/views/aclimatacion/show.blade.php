@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container py-4 text-start">
    <h1 class="h3 mb-4">Gestión de Etapa: Aclimatación</h1>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
    @endif

    @php
        use Carbon\Carbon;
        use Illuminate\Support\Facades\DB;

        $lotes_aclimatados = $aclimatacion->lotesAclimatados;

        // Status Global para el Header
        $clase_badge_global = $aclimatacion->fecha_cierre ? 'bg-success' : 'bg-info';
        $texto_status_global = $aclimatacion->fecha_cierre ? 'Finalizada' : 'Activa';

        $stock_inicial_aclimatacion = $lotes_aclimatados->sum('pivot.cantidad_inicial_lote');
        $merma_aclimatacion_acumulada = $aclimatacion->merma_etapa ?? 0;
        $inventario_pasante_calculado = $stock_inicial_aclimatacion - $merma_aclimatacion_acumulada;

        $meses_ing = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $meses_esp = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    @endphp

    {{-- CARD DE RESUMEN --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Resumen de la Etapa</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 text-start">
                    <p><strong>Fecha de Ingreso a Etapa:</strong> {{ Carbon::parse($aclimatacion->Fecha_Ingreso)->format('d/m/Y') }}</p>
                    <p><strong>Responsable:</strong> {{ $aclimatacion->operadorResponsable->nombre ?? 'N/A' }}</p>

                    <h6 class="mt-4 mb-2">Lotes y Variedades en Proceso</h6>
                    <ul class="list-unstyled border p-2 rounded text-start">
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

                            $fecha_inicio_plantacion = DB::table('plantacion')
                                ->where('ID_Llegada', $lote->ID_Llegada)
                                ->where('ID_Variedad', $lote->ID_Variedad)
                                ->min('Fecha_Plantacion');
                            
                            $is_finalizado = (bool) $lote->pivot->fecha_finalizado_variedad;
                        @endphp
                        <li class="small mb-1 {{ $is_finalizado ? 'text-decoration-line-through text-muted' : '' }}">
                            <i class="bi bi-tag"></i>
                            <strong>{{ $nombre_lote_traducido }}</strong>
                            <span class="text-muted ms-1 me-2" style="font-size: 0.85em;">
                                <i class="bi bi-calendar3"></i>
                                {{ $fecha_inicio_plantacion ? Carbon::parse($fecha_inicio_plantacion)->format('d/m/Y') : 'S/F' }}
                            </span>
                            <span class="badge {{ $clase_estado }} me-2">{{ $estado_lote }}</span>
                            Var: {{ $lote->variedad->nombre }}
                            <span class="badge bg-light text-dark">Cód: {{ $lote->variedad->codigo }}</span>
                            <span class="badge bg-light text-dark">{{ number_format($lote->pivot->cantidad_plantas) }} und.</span>
                            @if($is_finalizado) <span class="badge bg-success">LISTO</span> @endif
                        </li>
                        @if (!$loop->last) <hr class="my-1"> @endif
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-6 border-start ps-4 text-center d-flex flex-column justify-content-center">
                    <p class="h5"><strong>Estado Global de Etapa</strong></p>
                 
                    <hr class="w-75 mx-auto">
                    <p class="mb-1 text-start ps-5">Ingreso Global: <strong>{{ number_format($stock_inicial_aclimatacion) }}</strong></p>
                    <p class="text-danger text-start ps-5">Merma Aclimatación: <strong>{{ number_format($merma_aclimatacion_acumulada) }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mt-4 mb-3 text-dark text-start"><i class="bi bi-box-arrow-right me-2"></i>Control de Salidas e Historial de Días</h2>

<div class="card shadow-sm border-0">
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover align-middle mb-0 text-start" style="font-size: 0.82rem;">
            <thead class="table-dark sticky-top">
                <tr class="text-center">
                    <th class="ps-3 text-start">Variedad [Cód]</th>
                    <th>Lote</th>
                    <th>Días en Etapa</th>
                    <th>Inicial</th>
                    <th class="text-danger">Merma</th>
                    <th class="text-primary">Stock Real</th>
                    <th style="width: 140px;">Registrar Merma</th>
                    <th>Estado</th>
                    <th class="pe-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lotes_aclimatados as $lote)
                @php
                    $lid = $lote->ID_Llegada;
                    $vid = $lote->ID_Variedad;
                    
                    $pivotData = DB::table('aclimatacion_variedad')
                        ->where('aclimatacion_id', $aclimatacion->ID_Aclimatacion)
                        ->where('ID_llegada', $lid)
                        ->where('variedad_id', $vid)
                        ->first();

                    $is_finalizado = !is_null($pivotData->fecha_finalizado_variedad);
                    
                    // LÓGICA DEL CONTADOR INDIVIDUAL
                    $fecha_inicio_conteo = Carbon::parse($aclimatacion->Fecha_Ingreso)->startOfDay();
                    // Si la variedad está finalizada, usamos su fecha_finalizado_variedad, si no, usamos el día de hoy
                    $fecha_fin_conteo = $is_finalizado ? Carbon::parse($pivotData->fecha_finalizado_variedad)->startOfDay() : Carbon::now()->startOfDay();
                    $dias_individuales = (int) $fecha_inicio_conteo->diffInDays($fecha_fin_conteo);

                    $cantidad_inicial_v = $pivotData->cantidad_plantas; 
                    $merma_aclim_v = $pivotData->merma_acumulada_lote ?? 0; 
                    $stock_real_v = $cantidad_inicial_v - $merma_aclim_v;
                    
                    $nombre_lote_es = str_replace($meses_ing, $meses_esp, $lote->nombre_lote_semana ?? 'N/A');
                @endphp
                
                <tr class="{{ $is_finalizado ? 'table-success' : '' }} text-center">
                    <td class="ps-3 text-start">
                        <span class="fw-bold {{ $is_finalizado ? 'text-success' : 'text-dark' }}">{{ $lote->variedad->nombre }}</span><br>
                        <small class="text-muted">[{{ $lote->variedad->codigo }}]</small>
                    </td>
                    <td><small class="text-muted">{{ $nombre_lote_es }}</small></td>
                    
                    {{-- CONTADOR INDIVIDUAL --}}
                    <td>
                        <span class="badge {{ $is_finalizado ? 'bg-secondary' : 'bg-dark' }} rounded-pill">
                            {{ $dias_individuales }} días
                        </span>
                    </td>

                    <td class="fw-bold">{{ number_format($cantidad_inicial_v) }}</td>
                    <td class="text-danger fw-bold">{{ $merma_aclim_v > 0 ? '-' . number_format($merma_aclim_v) : '0' }}</td>
                    <td class="text-primary fw-bold bg-light">{{ number_format($stock_real_v) }}</td>
                    
                    <td>
                        @if(!$is_finalizado)
                            <form action="{{ route('aclimatacion.registrar_merma_lote', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                                @csrf
                                <input type="hidden" name="lote_id" value="{{ $lid }}">
                                <input type="hidden" name="variedad_id" value="{{ $vid }}">
                                <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                    <input type="number" name="cantidad_merma" class="form-control" placeholder="Cant." required max="{{ $stock_real_v }}">
                                    <button class="btn btn-danger rounded-pill" type="submit"><i class="bi bi-dash"></i></button>
                                </div>
                            </form>
                        @else
                            <small class="text-muted">Cerrado</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $is_finalizado ? 'bg-success' : 'bg-info' }}" style="font-size: 0.7rem;">
                            {{ $is_finalizado ? 'LISTO' : 'EN PROCESO' }}
                        </span>
                    </td>
                    <td class="pe-3 text-end">
                        @if(!$is_finalizado)
                            <form action="{{ route('aclimatacion.finalizar_variedad', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                                @csrf
                                <input type="hidden" name="lote_id" value="{{ $lid }}">
                                <input type="hidden" name="variedad_id" value="{{ $vid }}">
                                <button type="submit" class="btn btn-sm btn-success p-1" onclick="return confirm('¿Finalizar variedad? El contador de días se detendrá.')">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @else
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

    {{-- CIERRE DE ETAPA GLOBAL --}}
    @php
        $todas_finalizadas = $lotes_aclimatados->every(fn($l) => (bool)$l->pivot->fecha_finalizado_variedad);
    @endphp

    @if($todas_finalizadas && !$aclimatacion->fecha_cierre)
        <div class="card border-danger shadow-sm p-4 text-center mt-4">
            <h5>Todas las variedades están listas</h5>
            <form action="{{ route('aclimatacion.cerrar', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-danger btn-lg px-5 shadow">CERRAR ETAPA COMPLETA</button>
            </form>
        </div>
    @endif

    @if($aclimatacion->fecha_cierre)
        <div class="alert alert-success mt-4 text-start shadow-sm">
            <h5>Etapa Cerrada el {{ Carbon::parse($aclimatacion->fecha_cierre)->format('d/m/Y') }}</h5>
            <p class="mb-0 text-primary h5">Stock Pasante Final: <strong>{{ number_format($inventario_pasante_calculado) }} unidades</strong></p>
        </div>
    @endif
</div>

@endsection