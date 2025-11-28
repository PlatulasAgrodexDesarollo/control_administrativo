@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container mt-4">
    <h1>Historial de Recuperación de Merma</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Botón para registrar una nueva recuperación --}}
    <a href="{{ route('recuperacion.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-arrow-up-circle"></i> Registrar Nueva Recuperación
    </a>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>ID Rec.</th>
                            <th>Lote Origen</th> 
                            <th>Variedad</th>
                            <th>Fecha Recup.</th>
                            <th>Cantidad Recuperada</th>
                            <th>Operador</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recuperaciones as $r)
                        <tr>
                            <td>{{ $r->ID_Recuperacion }}</td>

                     
                            <td >
                                {{ $r->loteLlegada->nombre_lote_semana ?? 'N/A' }}
                            </td>

                            {{-- Trazabilidad a la Variedad --}}
                            <td>{{ $r->loteLlegada->variedad->nombre ?? 'N/A' }}
                                @if ($r->loteLlegada?->variedad?->codigo)
                                <br><span class="badge bg-secondary">{{ $r->loteLlegada->variedad->codigo }}</span>
                                @endif
                            </td>


                            <td>{{ \Carbon\Carbon::parse($r->Fecha_Recuperacion)->format('d/m/Y') }}</td>

                            <td class=>{{ number_format($r->Cantidad_Recuperada, 0) }} und.</td>

                            {{-- Trazabilidad al Operador --}}
                            <td>{{ $r->operadorResponsable->nombre ?? 'N/A' }}</td>

                            <td>{{ $r->Observaciones ?? 'Sin notas' }}</td>
                        </tr>
                        @endforeach

                        @if($recuperaciones->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">No hay registros de merma recuperada.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection