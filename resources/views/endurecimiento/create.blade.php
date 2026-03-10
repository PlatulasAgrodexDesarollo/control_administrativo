@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <h1 class="h2 mb-4">Iniciar Endurecimiento</h1>

            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 text-muted">Traspaso de Variedades Finalizadas</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('endurecimiento.store') }}" method="POST">
                        @csrf

                        {{-- 1. SELECCIÓN DE VARIEDAD LISTA --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small text-primary">1. Seleccionar Variedad Lista para Traspaso</label>
                            
                            <select name="aclimatacion_variedad_id" class="form-select form-select-lg rounded-pill @error('aclimatacion_variedad_id') is-invalid @enderror" required>
                                <option value="" selected disabled>-- Buscar por Lote o Variedad --</option>

                                @foreach($aclimataciones_listas as $acli)
                                    @php
                                        // Usamos las propiedades que definimos en el controlador (stdClass)
                                        // $acli->pivot_cantidad_inicial_lote y $acli->pivot_merma_acumulada_lote
                                        $stock_disponible = $acli->pivot_cantidad_inicial_lote - $acli->pivot_merma_acumulada_lote;
                                    @endphp

                                    <option value="{{ $acli->pivot_id }}">
                                        {{ $acli->nombre_lote_semana }} | {{ $acli->variedad_nombre }} 
                                        [{{ $acli->variedad_codigo }}] 
                                        — Disponible: {{ number_format($stock_disponible) }} und.
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Solo aparecen variedades que ya fueron marcadas como "Cerradas" en el módulo anterior y que no tienen un proceso de endurecimiento activo para su lote.</div>
                        </div>

                        <div class="row mb-4">
                            {{-- 2. FECHA DE INICIO --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">2. Fecha de Inicio</label>
                                <input type="date" name="Fecha_Ingreso" class="form-control rounded-pill" value="{{ date('Y-m-d') }}" required>
                            </div>

                            {{-- 3. RESPONSABLE --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">3. Responsable</label>
                                <select name="Operador_Responsable" class="form-select rounded-pill @error('Operador_Responsable') is-invalid @enderror" required>
                                    <option value="" selected disabled>-- Seleccionar --</option>
                                    @foreach($operadores as $op)
                                        <option value="{{ $op->ID_Operador }}">{{ $op->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('Operador_Responsable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                <i class="bi bi-check-circle-fill"></i> Iniciar Proceso de Endurecimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($aclimataciones_listas) == 0)
            <div class="alert alert-warning mt-4 rounded-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div>
                        <strong>Sin inventario disponible:</strong> No hay variedades cerradas individualmente en Aclimatación o los lotes ya se encuentran en endurecimiento.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection