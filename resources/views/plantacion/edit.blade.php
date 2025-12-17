@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <div class="container mt-4">
        <h1>Editar Registro de Plantación</h1>

        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('plantacion.update', $plantacion->ID_Plantacion) }}" method="POST">
            @csrf 
            @method('PUT') {{-- Usamos PUT para la actualización --}}
            
            {{-- 1. CAMPO FECHA --}}
            <div class="mb-3">
                <label for="Fecha_Plantacion" class="form-label">Fecha de Plantación:</label>
                <input type="date" name="Fecha_Plantacion" class="form-control" required 
                       value="{{ old('Fecha_Plantacion', $plantacion->Fecha_Plantacion) }}">
            </div>
            
            {{-- 2. SELECCIÓN DE LOTE DE INVENTARIO (FK: ID_Llegada) --}}
            <div class="mb-3">
                <label for="ID_Llegada" class="form-label">Lote de Inventario a Plantar (Origen):</label>
                <select name="ID_Llegada" class="form-control" required>
                    @foreach ($lotes_disponibles as $lote) 
                        <option value="{{ $lote->ID_Llegada }}" 
                                {{ old('ID_Llegada', $plantacion->ID_Llegada) == $lote->ID_Llegada ? 'selected' : '' }}>
                            Lote #{{ $lote->ID_Llegada }} ({{ $lote->variedad->nombre ?? 'N/A' }}) - (Stock: {{ $lote->Cantidad_Plantas }} und)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. CAMPO CANTIDAD SEMBRADA --}}
            <div class="mb-3">
                <label for="cantidad_sembrada" class="form-label">Plantas Sembradas:</label>
                <input type="number" name="cantidad_sembrada" class="form-control" required 
                       value="{{ old('cantidad_sembrada', $plantacion->cantidad_sembrada) }}" min="0">
            </div>

            <hr>
            <h4>Detalle de Pérdidas (Merma en Siembra)</h4>

            {{-- 4. CAMPO CANTIDAD SIN RAÍZ --}}
            <div class="mb-3">
                <label for="sin_raiz" class="form-label">Plantas sin Raíz:</label>
                <input type="number" name="sin_raiz" class="form-control" required 
                       value="{{ old('sin_raiz', $plantacion->sin_raiz) }}" min="0">
            </div>

            {{-- 5. CAMPO CANTIDAD PEQUEÑA/MAL FORMADA --}}
            <div class="mb-3">
                <label for="pequena_o_mal_formada" class="form-label">Plantas Pequeñas o Mal Formadas:</label>
                <input type="number" name="pequena_o_mal_formada" class="form-control" required 
                       value="{{ old('pequena_o_mal_formada', $plantacion->pequena_o_mal_formada) }}" min="0">
            </div>

            {{-- 6. SELECCIÓN DE OPERADOR --}}
            <div class="mb-3">
                <label for="Operador_Plantacion" class="form-label">Operador Responsable:</label>
                <select name="Operador_Plantacion" class="form-control" required>
                    @foreach ($operadores as $operador)
                        <option value="{{ $operador->ID_Operador }}" 
                                {{ old('Operador_Plantacion', $plantacion->Operador_Plantacion) == $operador->ID_Operador ? 'selected' : '' }}>
                            {{ $operador->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            

            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>
@endsection