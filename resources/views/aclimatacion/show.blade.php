@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container py-4">
    <h1 class="h3 mb-4">Gestión de Etapa: Aclimatación #{{ $aclimatacion->ID_Aclimatacion }}</h1>

    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Resumen de la Etapa</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Plantación de Origen:</strong> ID #{{ $aclimatacion->ID_Plantacion }}</p>
                    <p><strong>Variedad:</strong> {{ $aclimatacion->variedad->nombre ?? 'N/A' }}</p>
                    <p><strong>Fecha de Ingreso:</strong> {{ \Carbon\Carbon::parse($aclimatacion->Fecha_Ingreso)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Estado Inicial:</strong> {{ $aclimatacion->Estado_Inicial }}</p>
                    <p><strong>Duración Esperada:</strong> {{ $aclimatacion->Duracion_Aclimatacion }} días</p>
                    <p><strong>Responsable:</strong> {{ $aclimatacion->operadorResponsable->nombre ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN DE ACTIVIDADES DEPENDIENTES (Chequeos) --}}
    <h2 class="h4 mt-5">Registro de Actividades (Rendimiento)</h2>
    <p class="text-muted">Desde aquí se inician los chequeos de HyT y Agribon asociados a esta etapa.</p>

    <div class="row g-3">

        {{-- TARJETA 1: Chequeo Humedad y Temperatura (CHEQUEO_HYT) --}}
        <div class="col-md-6 col-lg-4">
            <div class="row mb-4 g-4 justify-content-center">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-thermometer-half"></i> Chequeo H/T</h5>
                        <p class="card-text small">Registro de parámetros ambientales.</p>
                        {{-- La ruta de creación del Chequeo debe llevar el ID de la etapa --}}
                        <a href="{{ route('chequeo_hyt.create', ['aclimatacion_id' => $aclimatacion->ID_Aclimatacion]) }}" class="btn btn-sm btn-info">
                            Registrar Nuevo Chequeo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection