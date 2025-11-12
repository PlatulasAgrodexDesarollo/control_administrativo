@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <div class="container mt-4">
        <h1>Registro de Inicio de Aclimatación</h1>

        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('aclimatacion.store') }}" method="POST">
            @csrf 
            
            {{-- 1. SELECCIÓN DE PLANTACIÓN (FK: ID_Plantacion) --}}
            <div class="mb-3">
                <label for="ID_Plantacion" class="form-label">Lote de Plantación Origen:</label>
                <select name="ID_Plantacion" class="form-control" required>
                    <option value="">Seleccione un registro de plantación</option>
                    {{-- $plantaciones se carga en el controlador create() --}}
                    @foreach ($plantaciones as $p) 
                        <option value="{{ $p->ID_Plantacion }}" {{ old('ID_Plantacion') == $p->ID_Plantacion ? 'selected' : '' }}>
                            ID #{{ $p->ID_Plantacion }} ({{ $p->variedad->nombre ?? 'N/A' }} - Sembradas: {{ $p->cantidad_sembrada }})
                        </option>
                    @endforeach
                </select>
                @error('ID_Plantacion') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 2. CAMPO FECHA INGRESO --}}
            <div class="mb-3">
                <label for="Fecha_Ingreso" class="form-label">Fecha de Ingreso a Aclimatación:</label>
                <input type="date" name="Fecha_Ingreso" class="form-control" required value="{{ old('Fecha_Ingreso', date('Y-m-d')) }}">
                @error('Fecha_Ingreso') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            
            {{-- 3. ESTADO INICIAL --}}
            <div class="mb-3">
                <label for="Estado_Inicial" class="form-label">Estado Inicial (Observación Rápida):</label>
                <input type="text" name="Estado_Inicial" class="form-control" required value="{{ old('Estado_Inicial') }}">
                @error('Estado_Inicial') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 4. DURACIÓN (Días) --}}
            <div class="mb-3">
                <label for="Duracion_Aclimatacion" class="form-label">Duración Esperada (Días):</label>
                <input type="number" name="Duracion_Aclimatacion" class="form-control" required value="{{ old('Duracion_Aclimatacion') }}" min="1">
                @error('Duracion_Aclimatacion') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 5. SELECCIÓN DE OPERADOR (FK) --}}
            <div class="mb-3">
                <label for="Operador_Responsable" class="form-label">Operador Responsable:</label>
                <select name="Operador_Responsable" class="form-control" required>
                    <option value="">Seleccione un operador</option>
                    @foreach ($operadores as $operador)
                        <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Responsable') == $operador->ID_Operador ? 'selected' : '' }}>
                            {{ $operador->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('Operador_Responsable') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 6. OBSERVACIONES --}}
            <div class="mb-3">
                <label for="Observaciones" class="form-label">Observaciones:</label>
                <textarea name="Observaciones" class="form-control">{{ old('Observaciones') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Iniciar Aclimatación</button>
        </form>
    </div>
@endsection