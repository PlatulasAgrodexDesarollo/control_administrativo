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

    // Fecha de inicio de la etapa
    $fecha_inicio = Carbon::parse($aclimatacion->Fecha_Ingreso);

    // Determinar la fecha de fin
    $fecha_fin = $aclimatacion->fecha_cierre
    ? Carbon::parse($aclimatacion->fecha_cierre)
    : Carbon::now();

    // Días reales transcurridos
    $dias_reales = floor($fecha_inicio->diffInDays($fecha_fin));

    // Duración esperada
    $duracion_esperada=$aclimatacion->Duracion_Aclimatacion;

    // Variables para el badge de estado
    $clase_badge = 'bg-secondary';
    $texto_status = '';

    // Cálculos de diferencia
    $dias_diferencia = abs($dias_reales - $duracion_esperada);
    $dias_ahorro = floor($duracion_esperada - $dias_reales);
    $dias_retraso = floor($dias_reales - $duracion_esperada);


    if ($aclimatacion->fecha_cierre) {
    // Lógica de estado si la etapa está CERRADA
    if ($dias_reales < $duracion_esperada) {
        $clase_badge='bg-success' ;
        $texto_status="Cerrado: Terminó antes (" . $dias_ahorro . " días restantes)" ;
        } elseif ($dias_reales==$duracion_esperada) {
        $clase_badge='bg-primary' ;
        $texto_status="Cerrado: Justo a tiempo" ;
        } else {
        $clase_badge='bg-warning text-dark' ;
        $texto_status="Cerrado: Retraso de " . $dias_retraso . " días" ;
        }
        } else {
        // Lógica de estado si la etapa está ABIERTA (en curso)
        if ($dias_reales> $duracion_esperada) {
        $clase_badge = 'bg-danger';
        $texto_status = "¡Tiempo excedido! (" . $dias_retraso . " días extra)";
        } else {
        $clase_badge = 'bg-info';
        $texto_status = "En curso (Faltan " . ($duracion_esperada - $dias_reales) . " días)";
        }
        }
        @endphp

        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Resumen de la Plantación y Etapa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Variedad:</strong> {{ $aclimatacion->variedad->nombre ?? 'N/A' }} (Cód. {{ $aclimatacion->variedad->codigo ?? 'N/A' }})</p>
                        <p><strong>Fecha de Ingreso:</strong> {{ \Carbon\Carbon::parse($aclimatacion->Fecha_Ingreso)->format('d/m/Y') }}</p>
                        <p><strong>Estado Inicial:</strong> {{ $aclimatacion->Estado_Inicial }}</p>

                        {{-- Lote de Inventario Original --}}
                        <p>
                            <strong>Lote de Llegada (Origen):</strong>
                            {{ $aclimatacion->loteLlegada->nombre_lote_semana ?? 'Lote #'.($aclimatacion->loteLlegada?->ID_Llegada ?? 'N/A') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Registro de Plantación:</strong> {{ number_format($total_plantas_sembradas ?? 0, 0) }} und.</p>
                        <p><strong>Duración Esperada:</strong> {{ $aclimatacion->Duracion_Aclimatacion }} días</p>
                        <p class="mb-2"><strong>Días Reales en Etapa:</strong><span class="badge {{ $clase_badge }} fs-6">{{ $dias_reales }} días</span></p>
                        <p class="mt-0 small text-muted">Status: {{ $texto_status }}</p>
                        <p><strong>Responsable de Aclimatación:</strong> {{ $aclimatacion->operadorResponsable->nombre ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="h4 mt-5">Historial de Chequeos Ambientales (H/T)</h2>


        @if (!$aclimatacion->fecha_cierre)
        <div class="mb-4">
            <a href="{{ route('chequeo_hyt.create', ['aclimatacion_id' => $aclimatacion->ID_Aclimatacion]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Registrar Nuevo Chequeo
            </a>
        </div>
        @endif

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Temp (°C)</th>
                                <th>Humedad (Hr)</th>
                                <th>Luz (Lux)</th>
                                <th>Acciones Registradas</th>
                                <th>Operador</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chequeos as $c)
                            <tr>
                                
                                <td>{{ \Carbon\Carbon::parse($c->Fecha_Chequeo)->format('d/m/Y') }} </td>
                                <td>{{ \Carbon\Carbon::parse($c->Hora_Chequeo)->format('H:i A') }}</td>
                                <td>{{ $c->Temperatura }}°C</td>
                                <td>{{ $c->Hr }}%</td>
                                <td>{{ $c->Lux }}</td>
                                <td>{{ $c->Actividades }}</td>
                                <td>{{ $c->operadorResponsable->nombre ?? 'N/A' }}</td>

                            </tr>
                            @endforeach

                            @if($chequeos->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center">No hay chequeos registrados para esta etapa.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <h2 class="h4 mt-5">Control de Inventario y Cierre de Etapa</h2>

        @php
        // La merma acumulada de Aclimatación se lee directamente del campo del modelo.
        // Esto es lo que el método registrarMerma debe actualizar.
        $merma_aclimatacion_acumulada = $aclimatacion->merma_etapa ?? 0;

        // Stock inicial real es el total de plantas sembradas (viene del controlador)
        $stock_inicial_aclimatacion = $total_plantas_sembradas ?? 0;

        // Merma de siembra (Viene del controlador)
        $merma_siembra = $merma_historica_lote ?? 0;

        // Si la etapa está cerrada, usamos la merma ya guardada en el cierre. Si no, usamos la acumulada.
        $merma_final_cierre = $aclimatacion->fecha_cierre ? $aclimatacion->merma_etapa : $merma_aclimatacion_acumulada;

        // Cálculo del Inventario Pasante (Stock Sembrado - Merma de Aclimatación)
        $inventario_pasante_calculado = $stock_inicial_aclimatacion - $merma_final_cierre;

        // Merma total del Lote hasta este punto (Siembra + Aclimatación)
        $merma_total_acumulada = $merma_siembra + $merma_final_cierre;

        // --- Cálculo de Tiempo (Se mantiene igual) ---
        $fecha_inicio = \Carbon\Carbon::parse($aclimatacion->Fecha_Ingreso);
        $fecha_fin = $aclimatacion->fecha_cierre ? \Carbon\Carbon::parse($aclimatacion->fecha_cierre) : \Carbon\Carbon::now();
        $dias_reales = floor($fecha_inicio->diffInDays($fecha_fin));
        $duracion_esperada = $aclimatacion->Duracion_Aclimatacion;
        $diferencia = $duracion_esperada - $dias_reales;
        $dias_restantes = max(0, $diferencia);
        $dias_retraso = max(0, -$diferencia);

        if ($aclimatacion->fecha_cierre) {
        if ($dias_reales < $duracion_esperada) {
            $clase_badge='bg-success' ;
            $texto_status="Cerrado: Terminó antes (" . $dias_restantes . " días de ahorro)" ;
            } elseif ($dias_reales==$duracion_esperada) {
            $clase_badge='bg-primary' ;
            $texto_status="Cerrado: Justo a tiempo" ;
            } else {
            $clase_badge='bg-warning text-dark' ;
            $texto_status="Cerrado: Retraso de " . $dias_retraso . " días" ;
            }
            }
            @endphp

            @if ($aclimatacion->fecha_cierre)
            {{-- 1. BLOQUE CUANDO LA ETAPA ESTÁ CERRADA --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> ETAPA CERRADA (Auditoría Final)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td style="width: 30%;"><strong>Stock Inicial (Sembrado):</strong></td>
                                <td><span class="badge bg-success fs-6">{{ number_format($stock_inicial_aclimatacion, 0) }} und.</span></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; color: orange;"><strong>Primera merma acumulada (plantación):</strong></td>
                                <td><span class="badge bg-warning fs-6">{{ number_format($merma_siembra, 0) }} und.</span></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; color:gray"><strong>Segunda merma Acumulada (Aclimatación):</strong></td>
                                <td><span class="badge bg-secondary fs-6">{{ number_format($merma_final_cierre, 0) }} und.</span></td>
                            </tr>
                            <tr>
                                <td style="width: 30%;"><strong>Inventario Final Pasante:</strong></td>
                                <td><span class="badge bg-primary fs-6">{{ number_format($inventario_pasante_calculado, 0) }} und.</span></td>
                            </tr>
                            <tr>
                                <td style="width: 30%;"><strong>Merma Total Lote:</strong></td>
                                <td><span class="badge bg-info fs-6">{{ number_format($merma_total_acumulada, 0) }} und.</span></td>
                            </tr>
                            <tr>
                                <td style="width: 30%;"><strong>Fecha de Cierre:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($aclimatacion->fecha_cierre)->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <p class="mt-3 mb-0 small text-muted">Este inventario final está listo para ser transferido a la siguiente etapa.</p>
                </div>
            </div>
            @else
            {{-- 2. BLOQUE CUANDO LA ETAPA ESTÁ ABIERTA --}}
            <div class="card shadow p-2 mb-4">
                <div class="card-header bg-light">
                    <h5><i class="bi bi-x-octagon"></i> Registro de Pérdidas</h5>
                </div>
                {{-- INFO ACUMULADA --}}
                <p class="small text-muted"> planata sembrada:<strong>{{ number_format($stock_inicial_aclimatacion ?? 0, 0) }}</strong> unidades.</p>
                <p class="small text-muted">Merma nueva:<strong class="text-danger">{{ number_format($merma_aclimatacion_acumulada ?? 0, 0) }}</strong> unidades.</p>

                {{-- REGISTRO DE MERMA INDEPENDIENTE --}}

                <div class="card-body">
                    <form action="{{ route('aclimatacion.registrar_merma', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-12 mb-4">
                                <label for="cantidad_merma" class="form-label">Plantas perdidas:</label>
                                <input type="number" name="cantidad_merma" class="form-control" required min="1" value="{{ old('cantidad_merma') }}">
                                @error('cantidad_merma') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-2">
                                <button type="submit" class="btn btn-warning w-100">Registrar Merma</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>


            {{-- CIERRE DE ETAPA --}}
            <div class="card shadow p-4 mb-4">
                <div class="card-header bg-light">
                    <h5><i class="bi bi-x-octagon"></i> Cierre y Finalización de Etapa</h5>
                </div>

                <div class="card-body">
                    <p class="small text-muted">Al cerrar, se utiliza la merma acumulada actual ({{ number_format($merma_aclimatacion_acumulada ?? 0, 0) }} und.) para calcular el inventario final pasante.</p>

                    <form action="{{ route('aclimatacion.cerrar', $aclimatacion->ID_Aclimatacion) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" class="btn btn-danger" onclick="return confirm('ATENCIÓN: ¿Desea cerrar la etapa y auditar la merma? Esta acción es definitiva.');">
                            CERRAR ETAPA
                        </button>
                    </form>
                </div>
            </div>

            @endif

            @endsection 