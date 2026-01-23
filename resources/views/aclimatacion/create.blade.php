@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

@php
$lote_options_js = $lote_options_js ?? '<option value="">Error al cargar Lotes</option>';
@endphp

<div class="container mt-4">
    <h1>Registro de Inicio de Aclimatación</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('aclimatacion.store') }}" method="POST">
        @csrf

        <div id="variedad-list-container">
            {{-- Contenedor de Filas Dinámicas --}}
            <div id="variedad-table-body"></div>
        </div>

        <button type="button" class="btn btn-info btn-sm mb-4" id="add-variedad-row">
            <i class="fas fa-plus"></i> Agregar Otro Lote
        </button>
        <hr class="my-4">

        {{-- 2. CAMPO FECHA INGRESO --}}
        <div class="mb-3">
            <label for="Fecha_Ingreso" class="form-label">Fecha de Ingreso a Aclimatación:</label>
            <input type="date" name="Fecha_Ingreso" class="form-control" required value="{{ old('Fecha_Ingreso', date('Y-m-d')) }}">
            @error('Fecha_Ingreso') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- 5. SELECCIÓN DE OPERADOR (FK) --}}
        <div class="mb-3">
            <label for="Operador_Responsable" class="form-label">Operador Responsable:</label>
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

        <button type="submit" class="btn btn-success">Iniciar Aclimatación</button>
    </form>
</div>

<script>
    const LOTE_OPTIONS_HTML = `{!! $lote_options_js !!}`;

    const addVariedadBtn = document.getElementById('add-variedad-row');
    const variedadTableBody = document.getElementById('variedad-table-body');

    function reIndexRows() {
        const rows = variedadTableBody.querySelectorAll('.variedad-row');
        rows.forEach((row, index) => {

            row.id = `row-${index}`;
            row.setAttribute('data-index', index);

            row.querySelectorAll('[name^="lotes_a_mover"]').forEach(input => {
                const oldName = input.name;

                const newName = oldName.replace(/\[\d+\]/g, `[${index}]`);
                input.name = newName;
            });

            const removeButton = row.querySelector('.remove-variedad-row');
            if (removeButton) {
                removeButton.setAttribute('data-row-index', index);
            }
        });
    }

    function addVariedadRow(isMandatory = false) {

        const newIndex = variedadTableBody.querySelectorAll('.variedad-row').length;

        const newRow = document.createElement('div');
        newRow.classList.add('mb-4', 'variedad-row');
        newRow.id = `row-${newIndex}`;

        const newRowContent = `
            <div class="mb-3">
                <label class="form-label">variedad del lote de Plantación a Mover</label>
                <select name="lotes_a_mover[${newIndex}][id_lote]" class="form-control lote-select" required>
                    ${LOTE_OPTIONS_HTML}
                </select>
                <input type="hidden" name="lotes_a_mover[${newIndex}][id_variedad]" class="variedad-hidden-input"> 
            </div>
            
            <div class="mb-3">
                <label class="form-label">Cantidad a Aclimatar</label>
                <input type="number" name="lotes_a_mover[${newIndex}][cantidad]" 
                        class="form-control cantidad-input" placeholder="Cantidad" min="1" required
                        value="" readonly> 
            </div>
            
            <div class="mb-3">
                <label class="form-label">Estado Inicial</label>
                <select name="lotes_a_mover[${newIndex}][estado_inicial]" class="form-control" required>
                    <option value="Normal">Normal</option>
                    <option value="Contaminada">Contaminada</option>
                    <option value="Debil">Débil</option>
                </select>
            </div>

            <div class="text-end">
                <button type="button" class="btn btn-danger btn-sm remove-variedad-row" 
                        data-row-index="${newIndex}"
                        ${isMandatory ? 'disabled' : ''} >
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        `;

        newRow.innerHTML = newRowContent;
        variedadTableBody.appendChild(newRow);

    }

    variedadTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('lote-select')) {
            const selectedLoteId = e.target.value;
            const currentSelect = e.target;
            const selectedOption = currentSelect.options[currentSelect.selectedIndex];
            const parentRow = currentSelect.closest('.variedad-row');

            const cantidadInput = parentRow.querySelector('.cantidad-input');
            const variedadHiddenInput = parentRow.querySelector('.variedad-hidden-input');

            if (!selectedLoteId) {
                cantidadInput.value = '';
                variedadHiddenInput.value = '';
                return;
            }

            const allSelects = variedadTableBody.querySelectorAll('.lote-select');
            let duplicateCount = 0;

            allSelects.forEach(select => {
                if (select.value === selectedLoteId && select !== currentSelect) {
                    duplicateCount++;
                }
            });

            if (duplicateCount > 0) {
                alert('¡Advertencia! Este Lote de Plantación ya fue seleccionado para aclimatación.');
                currentSelect.value = '';
                cantidadInput.value = '';
                variedadHiddenInput.value = '';
                return;
            }

            const totalSembrado = selectedOption.getAttribute('data-total-sembrado') || 0;
            const variedadId = selectedOption.getAttribute('data-variedad-id');

            if (!variedadId || isNaN(parseInt(variedadId)) || parseInt(variedadId) === 0) {
                alert('Error: La ID de la variedad para este lote no es válida. No se puede proceder.');
                currentSelect.value = '';
                cantidadInput.value = '';
                variedadHiddenInput.value = '';
                return;
            }

            cantidadInput.value = totalSembrado;
            cantidadInput.max = totalSembrado;
            variedadHiddenInput.value = variedadId;
        }
    });

    addVariedadBtn.addEventListener('click', () => {
        addVariedadRow(false);
    });

    variedadTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-variedad-row')) {
            const button = e.target.closest('.remove-variedad-row');
            const rowToRemove = button.closest('.variedad-row');

            if (variedadTableBody.querySelectorAll('.variedad-row').length > 1 && !button.disabled) {
                rowToRemove.remove();
                reIndexRows();
            } else if (button.disabled) {
                alert('Debe haber al menos un lote para iniciar la aclimatación.');
            }
        }
    });

    addVariedadRow(true);
</script>
@endsection