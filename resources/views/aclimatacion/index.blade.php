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
                        <th>Total Und. Sembradas</th>
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

                        <td>
                            @php
                                // 1. OBTENER LAS UNIDADES REALES DESDE PLANTACIÓN
                                // Filtramos por los lotes y variedades que pertenecen a esta aclimatación
                                $total_unidades_reales = 0;
                                
                                $variedades_resumen = $a->lotesAclimatados
                                    ->groupBy('variedad.ID_Variedad') 
                                    ->map(function ($lotes) use (&$total_unidades_reales) {
                                        $primero = $lotes->first();
                                        $ids_llegada = $lotes->pluck('ID_Llegada')->toArray();

                                        // CONSULTA DIRECTA A PLANTACIÓN PARA TRAER LO SEMBRADO
                                        $unidades_sembradas = DB::table('plantacion')
                                            ->whereIn('ID_Llegada', $ids_llegada)
                                            ->where('ID_Variedad', $primero->variedad->ID_Variedad)
                                            ->sum('cantidad_sembrada');

                                        $total_unidades_reales += $unidades_sembradas;

                                        return [
                                            'nombre' => $primero->variedad->nombre ?? 'N/A',
                                            'codigo' => $primero->variedad->codigo ?? 'N/A',
                                            'cantidad_real' => $unidades_sembradas,
                                        ];
                                    });

                                $lote_referencia = $a->lotesAclimatados->first();
                                $nombre_lote_traducido = 'N/A';
                                $fecha_llegada_formateada = null;

                                if ($lote_referencia) {
                                    $fecha_llegada = DB::table('llegada_planta')->where('ID_Llegada', $lote_referencia->ID_Llegada)->value('Fecha_Llegada');
                                    $fecha_carbon = Carbon::parse($fecha_llegada ?? $lote_referencia->Fecha_Ingreso);
                                    $fecha_llegada_formateada = $fecha_carbon->format('d/m/Y');
                                    
                                    $buscar = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    $reemplazar = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                    $nombre_lote_traducido = str_replace($buscar, $reemplazar, $lote_referencia->nombre_lote_semana ?? 'N/A');
                                }
                            @endphp

                            @if ($lote_referencia)
                            <div class="d-flex flex-column">
                                <strong class="small mb-0">{{ $nombre_lote_traducido }}</strong>
                                @if($fecha_llegada_formateada)
                                    <small class="text-muted mb-1"><i class="bi bi-calendar3 me-1"></i>{{ $fecha_llegada_formateada }}</small>
                                @endif

                                <ul class="list-unstyled small mb-0">
                                    @foreach ($variedades_resumen as $v)
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted">Var: {{ $v['nombre'] }} ({{ $v['codigo'] }}):</span>
                                        <span class="fw-bold text-end ms-2">{{ number_format($v['cantidad_real']) }} und.</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </td>

                        {{-- TOTAL GLOBAL BASADO EN LO SEMBRADO --}}
                        <td class="text-center">
                            <span class="fw-bold text-primary fs-5">{{ number_format($total_unidades_reales) }}</span>
                        </td>

                        {{-- ESTADO, RESPONSABLE Y ACCIONES (Se mantienen igual) --}}
                        <td>
                            @php $estados = $a->lotesAclimatados->pluck('pivot.Estado_Inicial_Lote')->unique(); @endphp
                            @if ($estados->count() === 1)
                                <span class="badge bg-primary">{{ $estados->first() }}</span>
                            @else
                                <span class="badge bg-secondary">Múltiple</span>
                            @endif
                        </td>
                        <td>{{ $a->operadorResponsable->nombre ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('aclimatacion.show', $a->ID_Aclimatacion) }}" class="btn btn-sm btn-warning">Gestionar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>  
    @endsection