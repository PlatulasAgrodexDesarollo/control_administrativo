@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h1 class="h4 mb-0">Editar Registro de Plantación General</h1>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('plantacion.update', $plantacion->ID_Plantacion) }}" method="POST">
                @csrf 
                @method('PUT')
                
                <div class="row">
                    {{-- 1. FECHA --}}
                    <div class="col-md-6 mb-3">
                        <label for="Fecha_Plantacion" class="form-label fw-bold">Fecha de Plantación:</label>
                        <input type="date" name="Fecha_Plantacion" class="form-control" required 
                               value="{{ old('Fecha_Plantacion', $plantacion->Fecha_Plantacion) }}">
                    </div>

                    {{-- 2. LOTE --}}
                    <div class="col-md-6 mb-3">
                        <label for="ID_Llegada" class="form-label fw-bold">Lote de Inventario (Origen):</label>
                        <select name="ID_Llegada" class="form-select" required>
                            @foreach ($lotes_disponibles as $lote) 
                                @php
                                    $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                    $nombre_lote_es = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana ?? 'Lote #'.$lote->ID_Llegada);
                                @endphp
                                <option value="{{ $lote->ID_Llegada }}" 
                                    {{ old('ID_Llegada', $plantacion->ID_Llegada) == $lote->ID_Llegada ? 'selected' : '' }}>
                                    {{ $nombre_lote_es }} - {{ $lote->variedad->nombre ?? 'N/A' }} [{{ $lote->variedad->codigo ?? 'S/C' }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. CANTIDAD SEMBRADA --}}
                <div class="mb-3">
                    <label for="cantidad_sembrada" class="form-label fw-bold">Plantas Sembradas:</label>
                    <input type="number" name="cantidad_sembrada" class="form-control form-control-lg border-primary" required 
                           value="{{ old('cantidad_sembrada', $plantacion->cantidad_sembrada) }}" min="0">
                    <small class="text-muted">Al modificar este dato, la diferencia con el lote se recalculará en los reportes.</small>
                </div>

                {{-- 4. OPERADOR --}}
                <div class="mb-3">
                    <label for="Operador_Plantacion" class="form-label fw-bold">Operador Responsable:</label>
                    <select name="Operador_Plantacion" class="form-select" required>
                        @foreach ($operadores as $operador)
                            <option value="{{ $operador->ID_Operador }}" 
                                {{ old('Operador_Plantacion', $plantacion->Operador_Plantacion) == $operador->ID_Operador ? 'selected' : '' }}>
                                {{ $operador->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- VALORES OCULTOS (Para mantener compatibilidad con la DB) --}}
                <input type="hidden" name="sin_raiz" value="0">
                <input type="hidden" name="pequena_o_mal_formada" value="0">

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('plantacion.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning px-5 fw-bold">Actualizar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection