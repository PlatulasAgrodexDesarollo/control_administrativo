@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detalle de Plantación General</h1>
       
    </div>

    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0 py-1">Balance de Planta del Lote</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-uppercase text-muted d-block">Recibidas</small>
                        <span class="h2 fw-bold">{{ number_format($registro->loteLlegada->Cantidad_Plantas ?? 0) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-uppercase text-muted d-block text-success">Sembradas</small>
                        <span class="h2 fw-bold text-success">{{ number_format($registro->cantidad_sembrada) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-uppercase text-muted d-block text-danger">Pérdida (Diferencia)</small>
                        @php $diferencia = ($registro->loteLlegada->Cantidad_Plantas ?? 0) - $registro->cantidad_sembrada; @endphp
                        <span class="h2 fw-bold text-danger">{{ number_format($diferencia) }}</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 border-end">
                    <h4 class="h5 border-bottom pb-2">Información del Material</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Variedad:</strong> {{ $registro->variedad->nombre ?? 'N/A' }}</li>
                        <li class="mb-2"><strong>Código Variedad:</strong> <span class="badge bg-secondary">{{ $registro->variedad->codigo ?? 'S/C' }}</span></li>
                        <li class="mb-2">
                            <strong>Nombre del Lote:</strong> 
                            @php
                                \Carbon\Carbon::setLocale('es');
                                $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                $nombre_lote = $registro->loteLlegada->nombre_lote_semana ?? 'N/A';
                                $nombre_lote_es = str_replace($meses_en, $meses_es, $nombre_lote);
                            @endphp
                            {{ $nombre_lote_es }}
                        </li>
                        <li class="mb-2"><strong>Fecha de Registro:</strong> {{ \Carbon\Carbon::parse($registro->Fecha_Plantacion)->format('d/m/Y') }}</li>
                    </ul>
                </div>

                <div class="col-md-6 ps-4">
                    <h4 class="h5 border-bottom pb-2">Responsabilidad</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Operador Responsable:</strong> {{ $registro->operadorPlantacion->nombre ?? 'N/A' }}</li>
                        <li class="mb-2"><strong>Operador que Recibió:</strong> {{ $registro->operadorLlegada->nombre ?? 'N/A' }}</li>
                        <li class="mb-2"><strong>Estado del Registro:</strong> 
                            @if($registro->editado) 
                                <span class="badge bg-warning text-dark">MODIFICADO</span> 
                            @else 
                                <span class="badge bg-success">ORIGINAL</span> 
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="{{ route('plantacion.index') }}" class="btn btn-outline-secondary">Volver al Listado</a>
            <a href="{{ route('plantacion.edit', $registro->ID_Plantacion) }}" class="btn btn-warning">Editar Información</a>
        </div>
    </div>
</div>
@endsection