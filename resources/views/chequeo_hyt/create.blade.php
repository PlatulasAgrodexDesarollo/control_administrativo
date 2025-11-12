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
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        Registro de Parámetros Ambientales
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">Fecha</th>
                                        <th style="width: 10%;">Hora</th>
                                        <th style="width: 15%;">Temp(T)</th>
                                        <th style="width: 15%;">HumRe(Hr)</th>
                                        <th style="width: 15%;">Luz(Lux)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        {{-- Fecha --}}
                                        <td><input type="date" name="Fecha_Chequeo" class="form-control" required value="{{ old('Fecha_Chequeo', date('Y-m-d')) }}"></td>

                                        {{-- Hora --}}
                                        <td> <input type="time" name="Hora_Chequeo" class="form-control" required value="{{ old('Hora_Chequeo', date('H:i')) }}"> </td>

                                        {{-- Temperatura --}}
                                        <td><input type="number" step="0.1" name="Temperatura" class="form-control" required value="{{ old('Temperatura') }}"></td>

                                        {{-- Humedad Relativa (Hr) --}}
                                        <td><input type="number" step="0.1" name="Hr" class="form-control" value="{{ old('Hr') }}" min="0" max="100"></td>

                                        {{-- Intensidad de Luz (Lux) --}}
                                        <td><input type="number" name="Lux" class="form-control" value="{{ old('Lux') }}" min="0"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="mt-4">Actividades y Observaciones</h5>

        {{-- Actividades--}}
        <div class="mb-3">
            <label for="Actividades" class="form-label">Actividaes:</label>
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
            @error('Operador_Responsable_ID') <div class="text-danger">{{ $message }}</div> @enderror
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