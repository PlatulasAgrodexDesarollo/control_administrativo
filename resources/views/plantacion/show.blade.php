 @extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

 @section('content')

 <div class="container mt-4">
     <h1>Detalle de Registro de Plantación</h1>

     <div class="card shadow mb-4">
         <div class="card-header bg-primary text-white">
             <h5 class="mb-0">Métricas de Trazabilidad y Producción</h5>
         </div>
         <div class="card-body">

             <div class="row">
                 {{-- SECCIÓN 1: DATOS DE INVENTARIO Y CANTIDADES --}}
                 <div class="col-md-6">
                     <h4>Detalles de Trazabilidad</h4>
                     <p><strong>Variedad:</strong> {{ $registro->variedad->nombre ?? 'N/A' }} ({{ $registro->variedad->codigo ?? 'N/A' }})</p>
                     <p><strong>Fecha de Plantación:</strong> {{ \Carbon\Carbon::parse($registro->Fecha_Plantacion)->format('d/m/Y') }}</p>
                     <p>
                         <strong>Lote de Inventario (Origen):</strong>
                         {{ $registro->loteLlegada->nombre_lote_semana ?? 'Lote #'.($registro->loteLlegada->ID_Llegada ?? 'N/A') }}
                     </p>

                     <p><strong>Cantidad Inicial Recibida:</strong> <span class="badge bg-secondary">{{ number_format($registro->loteLlegada->Cantidad_Plantas ?? 0, 0) }}</span></p>
                 </div>

                 {{-- SECCIÓN 2: RENDIMIENTO Y PÉRDIDAS --}}
                 <div class="col-md-6">
                     <h4>Métricas de Siembra</h4>
                     <p><strong>Plantas Sembradas:</strong> <span class="badge bg-success">{{ number_format($registro->cantidad_sembrada, 0) }}</span></p>
                     <p><strong>Pérdidas Totales:</strong> <span class="badge bg-danger">{{ number_format($registro->cantidad_perdida, 0) }}</span></p>

                     <hr>
                     <h6>Desglose de la Merma:</h6>
                     <p class="mb-1"><strong>Sin Raíz:</strong> {{ number_format($registro->sin_raiz, 0) }} und.</p>
                     <p class="mb-1"><strong>Pequeña/Mal Formada:</strong> {{ number_format($registro->pequena_o_mal_formada, 0) }} und.</p>
                 </div>
             </div>

             <hr>

             {{-- SECCIÓN 3: RESPONSABILIDAD --}}
             <h4>Responsabilidad del Registro</h4>
             <p><strong>Operador de Plantación:</strong> {{ $registro->operadorPlantacion->nombre ?? 'N/A' }} ({{ $registro->operadorPlantacion->puesto ?? 'N/A' }})</p>
             <p><strong>Operador que Recibió el Lote:</strong> {{ $registro->operadorLlegada->nombre ?? 'N/A' }}</p>

             <h6>Observaciones de la Tarea:</h6>
             <p class="text-muted">{{ $registro->Observaciones ?? 'Sin observaciones.' }}</p>
         </div>
     </div>

     {{-- Botón para volver --}}
     <a href="{{ route('plantacion.index') }}" class="btn btn-secondary mt-3">Volver al Listado de Plantaciones</a>
 </div>

 @endsection