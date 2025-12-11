@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@php
use Carbon\Carbon;
@endphp
@section('content')

<div class="container mt-4">
    <h1>Historial de Chequeos Ambientales (H/T)</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif


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
                            <th>Lote Chequeado</th>
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

                           
                            <td>
                                @if($c->loteLlegada)
                                @php
                             
                                $meses_espanol_abr = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];

                                
                                $fecha_carbon = \Carbon\Carbon::parse($c->loteLlegada->Fecha_Ingreso);
                                $abr_espanol = $meses_espanol_abr[$fecha_carbon->month];
                                $fecha_espanol_manual = $abr_espanol . ' ' . $fecha_carbon->year;

                               
                                $nombre_lote_original = $c->loteLlegada->nombre_lote_semana ?? 'N/A';

                                
                                $patron_mes_anio = '/\b[A-Za-z]{3,}\s\d{4}\b/';

                                
                                $nombre_lote_traducido = preg_replace(
                                $patron_mes_anio,
                                $fecha_espanol_manual,
                                $nombre_lote_original
                                );
                                @endphp

                                <strong class="text-primary">{{ $nombre_lote_traducido }}</strong>
                                <br>
                                <span class="small text-muted">Var: {{ $c->loteLlegada->variedad->nombre ?? 'N/A' }}</span>
                                @else
                                <span class="text-danger small">Lote no encontrado</span>
                                @endif
                            </td>

                            {{-- FECHA/HORA del Chequeo --}}
                            <td>{{ \Carbon\Carbon::parse($c->Fecha_Chequeo)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($c->Hora_Chequeo)->format('H:i') }}</td>

                            {{-- VALORES AMBIENTALES --}}
                            <td>{{ $c->Temperatura }}°C</td>
                            <td>{{ $c->Hr }}%</td>
                            <td>{{ $c->Lux }}</td>

                            {{-- ACTIVIDADES Y OPERADOR --}}
                            <td>{{ $c->Actividades }}</td>
                            <td>{{ $c->operadorResponsable->nombre ?? 'N/A' }}</td>

                        </tr>
                        @endforeach

                        @if($chequeos->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">No hay chequeos registrados para esta etapa.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection