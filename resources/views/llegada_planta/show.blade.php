@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

   <div class="container mt-4">
        <h1>Detalle de planta Recibida </h1>

        <div class="card shadow">
            
           
            <div class="card-header">
                <h5 class="mb-0">Información General</h5>
               
                <p class="text-muted mb-0">
                 <span class="fw-bold text-info">{{ $lote->nombre_lote_semana }}</span>
                </p>
            </div>
            <div class="card-body">
                
                <p><strong>Variedad:</strong> {{ $lote->variedad->nombre ?? 'N/A' }}</p>
                <p><strong>Fecha de Recepción:</strong> {{ \Carbon\Carbon::parse($lote->Fecha_Llegada)->format('d/m/Y') }}</p>
                <p><strong>Cantidad Recibida:</strong> {{ number_format($lote->Cantidad_Plantas, 0) }} unidades</p>
                <p><strong>Operador Receptor:</strong> {{ $lote->operadorLlegada->nombre ?? 'N/A' }}</p>
                <p><strong>Requiere Pre-Aclimatación:</strong> 
                    @if ($lote->Pre_Aclimatacion)
                        <span class="badge bg-warning">Sí</span>
                    @else
                        <span class="badge bg-success">No</span>
                    @endif
                </p>
                
                <hr>
                <h6>Observaciones:</h6>
                <p class="text-muted">{{ $lote->Observaciones ?? 'Sin observaciones.' }}</p>
            </div>
        </div>
        
        {{-- Botón para volver --}}
        <a href="{{ route('llegada_planta.index') }}" class="btn btn-secondary mt-3">Volver al Inventario</a>
    </div>

@endsection