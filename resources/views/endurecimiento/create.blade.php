@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="h3 mb-4 text-primary"><i class="bi bi-box-arrow-in-right"></i> Iniciar Endurecimiento</h1>

            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted">Traspaso de Plantas desde Aclimatación</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('endurecimiento.store') }}" method="POST">
                        @csrf

                       
                        <div class="mb-4">
                            <label class="form-label fw-bold text-uppercase small">1. Seleccionar Etapa de Aclimatación</label>

                       
                            <div style="width: 100%; overflow: hidden;">
                                <select name="aclimatacion_id" class="form-select @error('aclimatacion_id') is-invalid @enderror"
                                    style="width: 100% !important; min-width: 0; table-layout: fixed;" required>

                                    <option value="" selected disabled>-- Seleccione el Lote --</option>

                                    @foreach($aclimataciones_listas as $acli)
                                    <option value="{{ $acli->ID_Aclimatacion }}">
                                        {{ $acli->nombre_corto }} | Cant: {{ number_format($acli->cantidad_final) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                           
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-calendar-event"></i> 2. Fecha de Inicio</label>
                                <input type="date" name="Fecha_Ingreso" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-person-badge"></i> 3. Responsable</label>
                                <select name="Operador_Responsable" class="form-select @error('Operador_Responsable') is-invalid @enderror" required>
                                    <option value="" selected disabled>-- Seleccionar --</option>
                                    @foreach($operadores as $op)
                                    <option value="{{ $op->ID_Operador }}">{{ $op->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('Operador_Responsable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        

                        <hr class="my-4">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle-fill"></i> Iniciar Proceso de Endurecimiento
                            </button>

                        </div>
                    </form>
                </div>
            </div>

            @if($aclimataciones_listas->isEmpty())
            <div class="alert alert-warning mt-4">
                <i class="bi bi-exclamation-triangle"></i> **Atención:** No hay etapas de aclimatación cerradas disponibles para traspaso. Primero debes finalizar una etapa de aclimatación.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection