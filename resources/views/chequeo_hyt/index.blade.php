@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <div class="container mt-4">
        <h1>Historial de Chequeos Ambientales (H/T)</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        {{-- Encabezado que indica la etapa padre --}}
        <p class="lead">Etapa: Aclimatación N°{{ $aclimatacion->ID_Aclimatacion }} | Variedad: {{ $aclimatacion->variedad->nombre ?? 'N/A' }}</p>

        {{-- Botón para registrar un nuevo chequeo para esta etapa --}}
        <a href="{{ route('chequeo_hyt.create', ['aclimatacion_id' => $aclimatacion->ID_Aclimatacion]) }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Registrar Nuevo Chequeo
        </a>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                
                                <th>Fecha/Hora</th>
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
                               
                                <td>{{ \Carbon\Carbon::parse($c->Fecha_Chequeo)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($c->Hora_Chequeo)->format('H:i') }}</td>
                                <td>{{ $c->Temperatura }}</td>
                                <td>{{ $c->Hr }}%</td>
                                <td>{{ $c->Lux }}</td>
                                <td>{{ $c->Actividades }}</td>
                                <td>{{ $c->operadorResponsable->nombre ?? 'N/A' }}</td>
                               
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
    </div>
@endsection