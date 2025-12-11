@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Registro de Plantación en Invernadero</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('plantacion.store') }}" method="POST">
        @csrf

        {{-- 1. CAMPO FECHA --}}
        <div class="mb-3">
            <label for="Fecha_Plantacion" class="form-label">Fecha de Plantación:</label>
            <input type="date" name="Fecha_Plantacion" class="form-control" required value="{{ old('Fecha_Plantacion', date('Y-m-d')) }}">
            @error('Fecha_Plantacion') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 2. SELECCIÓN DE LOTE DE INVENTARIO (FK: ID_Llegada) --}}
        <div class="mb-3">
            <label for="ID_Llegada" class="form-label">Lote de Inventario a Plantar (Origen):</label>
            <select name="ID_Llegada" id="lote-origen" class="form-control" required>
                <option value="">Seleccione el lote recibido</option>
                {{-- $lotes_disponibles se carga en el controlador create() --}}
                @foreach ($lotes_disponibles as $lote)
                {{-- CRÍTICO: Añadimos data-cantidad para la validación visual --}}
                <option value="{{ $lote->ID_Llegada }}"
                    data-cantidad="{{ $lote->Cantidad_Plantas }}"
                    {{ old('ID_Llegada') == $lote->ID_Llegada ? 'selected' : '' }}>
                  LOTE: {{ $lote->nombre_lote_semana ?? 'N/A' }} 
                    - {{ $lote->variedad->nombre ?? 'N/A' }}

                    @if ($lote->variedad)
                    [CÓDIGO: {{ $lote->variedad->codigo ?? 'N/A' }}]
                    @endif

                    (Stock: {{ number_format($lote->Cantidad_Plantas, 0) }} und)
                </option>
                @endforeach
            </select>
            @error('ID_Llegada') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- INDICADOR DE LÍMITE (Feedback Visual) --}}
        <p class="text-info small mt-2">Inventario disponible para sembrar: <strong id="max-stock">0</strong> unidades.</p>
        <hr>


        {{-- 3. CAMPO CANTIDAD SEMBRADA --}}
        <div class="mb-3">
            <label for="cantidad_sembrada" class="form-label">Plantas Sembradas:</label>
            <input type="number" name="cantidad_sembrada" id="cantidad-sembrada" class="form-control" required value="{{ old('cantidad_sembrada') }}" min="0">
            @error('cantidad_sembrada') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <h4>Detalle de Pérdidas (Merma en Siembra)</h4>

        {{-- 4. CAMPO CANTIDAD SIN RAÍZ --}}
        <div class="mb-3">
            <label for="sin_raiz" class="form-label">Plantas sin Raíz:</label>
            <input type="number" name="sin_raiz" id="sin-raiz" class="form-control" required value="{{ old('sin_raiz') }}" min="0">
            @error('sin_raiz') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 5. CAMPO CANTIDAD PEQUEÑA/MAL FORMADA --}}
        <div class="mb-3">
            <label for="pequena_o_mal_formada" class="form-label">Plantas Pequeñas o Mal Formadas:</label>
            <input type="number" name="pequena_o_mal_formada" id="mal-formada" class="form-control" required value="{{ old('pequena_o_mal_formada') }}" min="0">
            @error('pequena_o_mal_formada') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 6. SELECCIÓN DE OPERADOR (FK: Operador_Plantacion) --}}
        <div class="mb-3">
            <label for="Operador_Plantacion" class="form-label">Operador Responsable:</label>
            <select name="Operador_Plantacion" class="form-control" required>
                <option value="">Seleccione un operador</option>
                @foreach ($operadores as $operador)
                <option value="{{ $operador->ID_Operador }}" {{ old('Operador_Plantacion') == $operador->ID_Operador ? 'selected' : '' }}>
                    {{ $operador->nombre }} ({{ $operador->puesto ?? 'Sin Puesto' }})
                </option>
                @endforeach
            </select>
            @error('Operador_Plantacion') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

       

        <button type="submit" class="btn btn-success" id="btn-guardar">Guardar Plantación</button>
    </form>
</div>

{{-- Script JavaScript para la Lógica del Límite de Inventario --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectLote = document.getElementById('lote-origen');
        const maxStockElement = document.getElementById('max-stock');
        const inputSembrada = document.getElementById('cantidad-sembrada');
        const inputSinRaiz = document.getElementById('sin-raiz');
        const inputMalFormada = document.getElementById('mal-formada');
        const form = document.querySelector('form');
        const btnGuardar = document.getElementById('btn-guardar');

        function updateMaxStock() {
            const selectedOption = selectLote.options[selectLote.selectedIndex];
            const maxCantidad = selectedOption.getAttribute('data-cantidad');

            // Muestra el stock disponible
            if (maxCantidad) {
                maxStockElement.textContent = new Intl.NumberFormat().format(maxCantidad);
            } else {
                maxStockElement.textContent = '0';
            }
        }

        // Validación al enviar el formulario (Front-end)
        form.addEventListener('submit', function(e) {
            const maxCantidad = parseInt(selectLote.options[selectLote.selectedIndex].getAttribute('data-cantidad'));
            const sembrada = parseInt(inputSembrada.value) || 0;
            const sinRaiz = parseInt(inputSinRaiz.value) || 0;
            const malFormada = parseInt(inputMalFormada.value) || 0;

            const totalConsumido = sembrada + sinRaiz + malFormada;

            if (totalConsumido > maxCantidad) {
                e.preventDefault();
                alert(`ERROR: La cantidad total consumida (${totalConsumido}) excede el stock disponible del lote (${maxCantidad}).`);
                return false;
            }

            
            btnGuardar.disabled = true;
            this.submit();
        });

        selectLote.addEventListener('change', updateMaxStock);
        updateMaxStock();
    });
</script>
@endsection