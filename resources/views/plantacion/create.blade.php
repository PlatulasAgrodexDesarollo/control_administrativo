@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h1 class="h4 mb-0">Registro de Plantación General</h1>
        </div>
        <div class="card-body">

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('plantacion.store') }}" method="POST" id="form-plantacion">
                @csrf

                {{-- 1. CAMPO FECHA --}}
                <div class="mb-3">
                    <label for="Fecha_Plantacion" class="form-label fw-bold">Fecha de Plantación:</label>
                    <input type="date" name="Fecha_Plantacion" class="form-control form-control-lg" required value="{{ old('Fecha_Plantacion', date('Y-m-d')) }}">
                </div>

                {{-- 2. SELECCIÓN DE LOTE --}}
                <div class="mb-3">
                    <label for="ID_Llegada" class="form-label fw-bold">Lote de Inventario (Origen):</label>
                    <select name="ID_Llegada" id="lote-origen" class="form-select form-select-lg" required>
                        <option value="">Seleccione el lote recibido</option>
                        @foreach ($lotes_disponibles as $lote)
                        @php
                            $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $nombre_lote_espanol = str_replace($meses_en, $meses_es, $lote->nombre_lote_semana ?? 'N/A');
                        @endphp
                        <option value="{{ $lote->ID_Llegada }}"
                            data-cantidad="{{ $lote->Cantidad_Plantas }}"
                            {{ old('ID_Llegada') == $lote->ID_Llegada ? 'selected' : '' }}>
                            {{ $nombre_lote_espanol }} - {{ $lote->variedad->nombre ?? 'N/A' }} 
                            [{{ $lote->variedad->codigo ?? 'S/C' }}]
                            (Stock: {{ number_format($lote->Cantidad_Plantas, 0) }} und)
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- FEEDBACK VISUAL DE STOCK --}}
                <div class="alert alert-info py-2">
                    <i class="bi bi-info-circle-fill"></i> Planta disponible en este lote: 
                    <strong id="max-stock text-primary">0</strong> unidades.
                </div>

                <hr>

                {{-- 3. CAMPO CANTIDAD SEMBRADA --}}
                <div class="mb-4">
                    <label for="cantidad_sembrada" class="form-label fw-bold">Cantidad de Plantas Sembradas:</label>
                    <input type="number" name="cantidad_sembrada" id="cantidad-sembrada" 
                           class="form-control form-control-lg border-success" 
                           placeholder="Ingrese el total de plantas puestas en charola"
                           required value="{{ old('cantidad_sembrada') }}" min="0">
                    <div class="form-text text-muted">La diferencia con el stock del lote se registrará automáticamente como pérdida.</div>
                </div>

                {{-- 4. SELECCIÓN DE OPERADOR --}}
                <div class="mb-4">
                    <label for="Operador_Plantacion" class="form-label fw-bold">Operador Responsable:</label>
                    <select name="Operador_Plantacion" class="form-select" required>
                        <option value="">Seleccione el responsable de la plantación</option>
                        @foreach ($operadores as $operador)
                        <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Plantacion') == $operador->ID_Operador ? 'selected' : '' }}>
                            {{ $operador->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- CAMPOS OCULTOS PARA COMPATIBILIDAD CON BASE DE DATOS --}}
                {{-- (Enviamos 0 en mermas para no romper el esquema actual de la DB) --}}
                <input type="hidden" name="sin_raiz" value="0">
                <input type="hidden" name="pequena_o_mal_formada" value="0">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg shadow" id="btn-guardar">
                        <i class="bi bi-check-circle"></i> Guardar Registro General
                    </button>
                    <a href="{{ route('plantacion.index') }}" class="btn btn-light text-muted">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectLote = document.getElementById('lote-origen');
        const maxStockElement = document.getElementById('max-stock');
        const inputSembrada = document.getElementById('cantidad-sembrada');
        const btnGuardar = document.getElementById('btn-guardar');

        function updateMaxStock() {
            const selectedOption = selectLote.options[selectLote.selectedIndex];
            const maxCantidad = selectedOption.getAttribute('data-cantidad');
            maxStockElement.textContent = maxCantidad ? new Intl.NumberFormat().format(maxCantidad) : '0';
        }

        document.getElementById('form-plantacion').addEventListener('submit', function(e) {
            // Eliminamos la validación de error para permitir excedentes (números negativos en diferencia)
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...';
        });

        selectLote.addEventListener('change', updateMaxStock);
        updateMaxStock();
    });
</script>
@endsection