@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registro de Recepción de Planta</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('llegada_planta.store') }}" method="POST">
        @csrf

        {{-- 1. CAMPO FECHA --}}
        <div class="mb-3">
            <label for="Fecha_Llegada" class="form-label">Fecha de Recepción:</label>
            <input type="date" name="Fecha_Llegada" class="form-control" required value="{{ old('Fecha_Llegada', date('Y-m-d')) }}">
            @error('Fecha_Llegada') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 2. SELECCIÓN DE VARIEDAD --}}
    <div class="mb-3">
                <label for="ID_Variedad" class="form-label">Variedad de Planta:</label>
                <select name="ID_Variedad" class="form-control" required>
                    <option value="">Seleccione una variedad</option>
                    @foreach ($variedades as $variedad)
                    <option value="{{ $variedad->ID_Variedad }}" {{ old('ID_Variedad') == $variedad->ID_Variedad ? 'selected' : '' }}>
                        {{-- Muestra el NOMBRE y el CÓDIGO --}}
                       {{ $variedad->nombre }} (Cód. {{ $variedad->codigo ?? 'N/A' }}) - Color: {{ $variedad->color ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
                @error('ID_Variedad') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

        {{-- 3. CAMPO CANTIDAD --}}
        <div class="mb-3">
            <label for="Cantidad_Plantas" class="form-label">Cantidad de Plantas Recibidas:</label>
            <input type="number" name="Cantidad_Plantas" class="form-control" required value="{{ old('Cantidad_Plantas') }}" min="1">
            @error('Cantidad_Plantas') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 4. CAMPO PRE-ACLIMATACION (Booleano) --}}
        <div class="mb-3 form-check">
            <input type="checkbox" name="Pre_Aclimatacion" id="Pre_Aclimatacion" class="form-check-input" value="1" {{ old('Pre_Aclimatacion') ? 'checked' : '' }}>
            <label for="Pre_Aclimatacion" class="form-check-label">Requiere Pre-Aclimatación?</label>
            @error('Pre_Aclimatacion') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 5. SELECCIÓN DE OPERADOR --}}
        <div class="mb-3">
            <label for="Operador_Llegada_ID" class="form-label">Operador:</label>
            {{-- CRUCIAL: El name debe ser Operador_Llegada_ID --}}
            <select name="Operador_Llegada" class="form-control" required>
                <option value="">Seleccione un operador</option>
                @foreach ($operadores as $operador)
                <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Llegada') == $operador->ID_Operador ? 'selected' : '' }}>
                    {{ $operador->nombre }}
                </option>
                @endforeach
            </select>
            {{-- El @error debe coincidir --}}
            @error('Operador_Llegada') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

     
      

        <button type="submit" class="btn btn-success">Registrar Recepción</button>
    </form>
</div>

@endsection