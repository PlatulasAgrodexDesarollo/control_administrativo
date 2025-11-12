@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Gestión de la Etapa de Aclimatación</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif




   
    {{-- SECCIÓN DE TARJETAS DE PROCESO  --}}



    
    <h2 class="h4 mb-3 mt-4">Navegación de Proceso</h2>

    <div class="row g-3 mb-5">
        <div class="row mb-4 g-4 justify-content-center">
            {{-- TARJETA 1: INICIO DE PLANTACIÓN --}}
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-geo-alt"></i> Módulo Plantación</h5>
                        <p class="card-text small text-muted">Registrar siembra y ver el listado de lotes plantados.</p>
                        {{-- Enlace al índice del módulo Plantación --}}
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
                        {{-- Enlace al formulario de creación --}}
                        <a href="{{ route('aclimatacion.create') }}" class="btn btn-success stretched-link">
                            Registrar
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>



    {{-- TABLA DE GESTIÓN  --}}
   


    <h2 class="h4 mb-3">Etapas de Aclimatación en Curso</h2>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>ID Etapa</th>
                            <th>Fecha Ingreso</th>
                            <th>Plantación Origen</th>
                            <th>Variedad</th>
                            <th>Estado Inicial</th>
                            <th>Duración Estimada</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aclimataciones as $a)
                        <tr>
                            <td>{{ $a->ID_Aclimatacion }}</td>
                            <td>{{ \Carbon\Carbon::parse($a->Fecha_Ingreso)->format('d/m/Y') }}</td>
                            <td>Plantación #{{ $a->ID_Plantacion }}</td>
                            <td>{{ $a->variedad->nombre ?? 'N/A' }}</td>
                            <td>{{ $a->Estado_Inicial }}</td>
                            <td>{{ $a->Duracion_Aclimatacion }} días</td>
                            <td>{{ $a->operadorResponsable->nombre ?? 'N/A' }}</td>

                            <td>
                                <a href="{{ route('aclimatacion.show', $a->ID_Aclimatacion) }}" class="btn btn-sm btn-warning">Gestionar</a>
                            </td>
                        </tr>
                        @endforeach

                        @if($aclimataciones->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center">No hay etapas de aclimatación iniciadas.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection