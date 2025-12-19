@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])


@php
use Carbon\Carbon;
// Definición de meses en español (Mantenida fuera del foreach, pero disponible)
$meses_espanol_abr = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
@endphp

@section('content')

<div class="container mt-4">
    <h1 class="mb-4">Gestión de la Etapa de Aclimatación</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- NAVEGACIÓN DE PROCESO (Mantener la navegación sin cambios) --}}
    <h2 class="h4 mb-3 mt-4">Navegación de Proceso</h2>
    <div class="row g-3 mb-5">
        <div class="row mb-4 g-4 justify-content-center">
            {{-- TARJETA 1: MÓDULO PLANTACIÓN --}}
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-play-circle"></i> Módulo Plantación</h5>
                        <p class="card-text small text-muted">Registrar siembra y ver el listado de lotes plantados.</p>
                        <a href="{{ route('plantacion.index') }}" class="btn btn-outline-primary stretched-link">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>

            {{-- TARJETA 2: INICIAR ACLIMATACIÓN --}}
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-play-circle"></i> Iniciar Nueva Etapa</h5>
                        <p class="card-text small text-muted">Registrar un lote para comenzar su proceso de aclimatación.</p>
                        <a href="{{ route('aclimatacion.create') }}" class="btn btn-success stretched-link">
                            Registrar
                        </a>
                    </div>
                </div>
            </div>

            {{-- TARJETA 3: RECUPERACION DE MERMA --}}
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-play-circle"></i> Iniciar recuperación </h5>
                        <p class="card-text small text-muted">Registrar la merma recupeda del lote.</p>
                        <a href="{{ route('recuperacion.index') }}" class="btn btn-success stretched-link">
                            Registrar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- TABLA DE GESTIÓN --}}
        <h2 class="h4 mb-3">Etapas de Aclimatación en Curso</h2>


        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Fecha Ingreso</th>
                                <th>Origen y Variedades</th>
                                <th>Total Und.</th>
                                <th>Estado</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aclimataciones as $a)
                            <tr>
                                {{-- Fecha Ingreso Global --}}
                                <td>{{ Carbon::parse($a->Fecha_Ingreso)->format('d/m/Y') }}</td>

                                {{-- COLUMNA CONSOLIDADA DE LOTES Y VARIEDADES (Consolida la información) --}}
                                <td>
                                    @php
                                        // **CORRECCIÓN DE ERROR:** Definición de la variable aquí (Línea 1 del bloque @php)
                                        $total_unidades_iniciales = $a->lotesAclimatados->sum('pivot.cantidad_plantas');


                                        $lote_referencia = $a->lotesAclimatados->first();

                                        $variedades_resumen = $a->lotesAclimatados
                                        ->groupBy('variedad.nombre') 
                                        ->map(function ($lotes) {
                                            return [
                                            'nombre' => $lotes->first()->variedad->nombre ?? 'N/A',
                                            'cantidad_total' => $lotes->sum('pivot.cantidad_plantas'),
                                            ];
                                        });

                                        $nombre_lote_traducido = 'N/A';

                                        if ($lote_referencia) {
                                        // Lógica de Traducción de Fecha 
                                        $fecha_carbon = Carbon::parse($lote_referencia->Fecha_Ingreso);
                                        $abr_espanol = $meses_espanol_abr[$fecha_carbon->month];
                                        $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon->year;

                                        // TRADUCIR EL NOMBRE DEL LOTE DE REFERENCIA
                                        $nombre_lote_traducido = $lote_referencia->nombre_lote_semana ?? 'N/A';
                                        $patron_mes_anio = '/\b[A-Za-z]{3,}\s\d{4}\b/';

                                        $nombre_lote_traducido = preg_replace(
                                        $patron_mes_anio,
                                        $fecha_espanol_manual,
                                        $nombre_lote_traducido
                                        );
                                        }
                                    @endphp

                                    @if ($lote_referencia)
                                    <div class="d-flex flex-column">
                                        {{-- LOTE DE REFERENCIA (Nombre y Fecha) --}}
                                        <strong class=" small mb-1">{{ $nombre_lote_traducido }}</strong>

                                        {{-- RESUMEN DE VARIEDADES (Lista concisa) --}}
                                        <ul class="list-unstyled small mb-0">
                                            @foreach ($variedades_resumen as $v)
                                            <li class="d-flex justify-content-between">
                                                <span class="text-muted">Var. {{ $v['nombre'] }}:</span>
                                                <span class="fw-bold">{{ number_format($v['cantidad_total']) }} und.</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @else
                                    <span class="text-danger small">Lotes no asignados</span>
                                    @endif
                                </td>



                                {{-- TOTAL GLOBAL (Ahora la variable está definida) --}}
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($total_unidades_iniciales) }}</span>
                                </td>


                                <td>
                                    @php
                                    // Obtener los estados únicos de todos los lotes de la colección pivot
                                    $estados = $a->lotesAclimatados->pluck('pivot.Estado_Inicial_Lote')->unique();
                                    @endphp

                                    @if ($estados->isEmpty())
                                    <span class="badge bg-danger">Sin estado</span>
                                    @elseif ($estados->count() === 1)

                                    @php
                                    $estado_unico = $estados->first();
                                    // Asignación de color de badge basado en el estado
                                    $badge_class = match ($estado_unico) {
                                    'Normal' => 'bg-primary',
                                    'Contaminada' => 'bg-danger',
                                    'Debil' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                    };
                                    @endphp
                                    <span class="badge {{ $badge_class }}">
                                        {{ $estado_unico }}
                                    </span>
                                    @else

                                    <span class="badge bg-secondary"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Estados: {{ $estados->implode(', ') }}">
                                        Múltiple ({{ $estados->count() }})
                                    </span>
                                    @endif
                                </td>

                                {{-- CELDA ELIMINADA: <td>{{ $a->Duracion_Aclimatacion }} días</td> --}}
                                
                                {{-- Responsable --}}
                                <td>{{ $a->operadorResponsable->nombre ?? 'N/A' }}</td>

                                {{-- Acciones --}}
                                <td>
                                    <a href="{{ route('aclimatacion.show', $a->ID_Aclimatacion) }}" class="btn btn-sm btn-warning">Gestionar</a>
                                </td>
                            </tr>
                            @endforeach

                            @if($aclimataciones->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center">No hay etapas de aclimatación iniciadas.</td>
                                {{-- NOTA: colspan debe ser 7 si quitamos una columna --}}
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @endsection