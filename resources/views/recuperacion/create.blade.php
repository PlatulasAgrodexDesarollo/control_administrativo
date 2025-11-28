@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registro de Recuperación de Merma</h1>


    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('recuperacion.store') }}" method="POST">
        @csrf

        <div class="card-body">

            {{-- 1. SELECCIÓN DE LOTE (ORIGEN DE LA MERMA) --}}
            <div class="md-3">
                <label for="ID_Llegada" class="form-label">Lote de Inventario Origen (Lote de Llegada):</label>
                <select name="ID_Llegada" class="form-control" required>
                    <option value="">Seleccione el lote del cual se recupera la merma</option>
                    {{-- $lotes_recuperables se pasa desde el controlador create() --}}
                    @foreach ($lotes_recuperables as $lote)
                    <option value="{{ $lote->ID_Llegada }}" {{ old('ID_Llegada') == $lote->ID_Llegada ? 'selected' : '' }}>
                        Lote #{{ $lote->ID_Llegada }} |
                        Var: {{ $lote->variedad->nombre ?? 'N/A' }} |
                        @if ($lote->variedad && $lote->variedad->codigo)
                        [Cod: {{ $lote->variedad->codigo }}]
                        @endif
                        | Merma: {{ number_format($lote->perdida_acumulada_siembra, 0) }} und.
                    </option>
                    @endforeach
                </select>
                @error('ID_Llegada') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 2. CANTIDAD RECUPERADA --}}
            <div class="md-3">
                <label for="Cantidad_Recuperada" class="form-label">Cantidad de Plantas Recuperadas:</label>
                <input type="number" name="Cantidad_Recuperada" class="form-control" required min="1" value="{{ old('Cantidad_Recuperada') }}">
                @error('Cantidad_Recuperada') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 3. FECHA DE RECUPERACIÓN --}}
            <div class="md-3">
                <label for="Fecha_Recuperacion" class="form-label">Fecha de Recuperación:</label>
                <input type="date" name="Fecha_Recuperacion" class="form-control" required value="{{ old('Fecha_Recuperacion', date('Y-m-d')) }}">
                @error('Fecha_Recuperacion') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 4. OPERADOR RESPONSABLE --}}
            <div class="md-3">
                <label for="Operador_Responsable" class="form-label">Operador Responsable:</label>
                <select name="Operador_Responsable" class="form-control" required>
                    <option value="">Seleccione un operador</option>
                    {{-- $operadores se pasa desde el controlador create() --}}
                    @foreach ($operadores as $operador)
                    <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Responsable') == $operador->ID_Operador ? 'selected' : '' }}>
                        {{ $operador->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('Operador_Responsable') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- 5. OBSERVACIONES --}}
            <div class="col-12">
                <label for="Observaciones" class="form-label">Detalles/Observaciones:</label>
                <textarea name="Observaciones" class="form-control">{{ old('Observaciones') }}</textarea>
                @error('Observaciones') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

        </div>



        <button type="submit" class="btn btn-success mt-3">Guardar Recuperación e Actualizar Inventario</button>
    </form>
</div>
@endsection