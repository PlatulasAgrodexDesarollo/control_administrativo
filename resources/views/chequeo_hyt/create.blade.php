@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registro de Chequeo Ambiental (H/T)</h1>

    <p class="lead">Etapa: Aclimatación N°{{ $aclimatacion->ID_Aclimatacion }} | Variedad: {{ $aclimatacion->variedad->nombre ?? 'N/A' }}</p>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('chequeo_hyt.store') }}" method="POST">
        @csrf

        {{-- CAMPO OCULTO: ID de la etapa de Aclimatación (Trazabilidad) --}}
        <input type="hidden" name="ID_Aclimatacion" value="{{ $aclimatacion->ID_Aclimatacion }}">
        <div class="row">
            {{-- Usamos col-12 para que la tarjeta ocupe todo el ancho disponible --}}
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        Registro de Parámetros Ambientales
                    </div>
                    <div class="card-body">

                        
                        <div class="row g-3">

                            {{-- FECHA: --}}
                            <div class="col-md-6">
                                <label for="Fecha_Chequeo" class="form-label">Fecha</label>
                                <input type="date" name="Fecha_Chequeo" class="form-control" required value="{{ old('Fecha_Chequeo', date('Y-m-d')) }}">
                            </div>

                            {{-- HORA: --}}
                            <div class="col-md-6">
                                <label for="Hora_Chequeo" class="form-label">Hora</label>
                                <input type="time" name="Hora_Chequeo" class="form-control" required value="{{ old('Hora_Chequeo', date('H:i')) }}">
                            </div>


                            {{-- TEMPERATURA: --}}
                            <div class="col-md-4">
                                <label for="Temperatura" class="form-label">Temp (°C)</label>
                                <input type="number" step="0.1" name="Temperatura" class="form-control" required value="{{ old('Temperatura') }}">
                            </div>

                            {{-- HUMEDAD:--}}
                            <div class="col-md-4">
                                <label for="Hr" class="form-label">Humedad (Hr)</label>
                                <input type="number" step="0.1" name="Hr" class="form-control" value="{{ old('Hr') }}" min="0" max="100">
                            </div>

                            {{-- LUZ:  --}}
                            <div class="col-md-4">
                                <label for="Lux" class="form-label">Luz (Lux)</label>
                                <input type="number" name="Lux" class="form-control" value="{{ old('Lux') }}" min="0">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <h5 class="mt-4">Actividades y Observaciones</h5>

        {{-- Actividades--}}
        <div class="mb-3">
            <label for="Actividades" class="form-label">Actividades:</label>
            <textarea name="Actividades" class="form-control" required>{{ old('Actividades') }}</textarea>
            @error('Actividades') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Observaciones --}}
        <div class="mb-3">
            <label for="Observaciones" class="form-label">Observaciones:</label>
            <textarea name="Observaciones" class="form-control">{{ old('Observaciones') }}</textarea>
        </div>

        <h5 class="mt-4">Operador Responsable</h5>
        <div class="mb-3">
            <select name="Operador_Responsable" class="form-control" required>
                <option value="">Seleccione un operador</option>
                @foreach ($operadores as $operador)
                <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Responsable') == $operador->ID_Operador ? 'selected' : '' }}>
                    {{ $operador->nombre }} ({{ $operador->puesto ?? 'Sin Puesto' }})
                </option>
                @endforeach
            </select>
            @error('Operador_Responsable') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-success mt-3">Guardar Chequeo</button>
    </form>
</div>

{{-- SCRIPT DE TIEMPO REAL --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const horaInput = document.getElementById('hora-actual');

        function actualizarHora() {
            const ahora = new Date();
            // Formatea la hora 
            const hora = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');

            const horaFormateada = `${hora}:${minutos}`;


            if (document.activeElement !== horaInput) {
                horaInput.value = horaFormateada;
            }
        }


        actualizarHora();
        setInterval(actualizarHora, 1000);
    });
</script>
@endsection