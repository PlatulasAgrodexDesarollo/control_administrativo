@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')

<div class="container mt-4">
    <h1>Inventario Inicial (Plantas Recibidas)</h1>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('llegada_planta.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-box-seam"></i> Registrar Nueva Recepción
    </a>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th> ID</th>
                            <th>Fecha Llegada</th>
                            <th>Variedad</th>
                            <th>CÓDIGO</th>
                            <th>Cantidad</th>
                            <th>Operador</th>
                            <th>Pre-Aclimatación</th>
                            <th>Acciones</th>
                        </tr>
                    <tbody>
                        @foreach ($lotes_llegada as $lote)
                        <tr>
                            <td>{{ $lote->ID_Llegada }}</td>
                            <td>{{ \Carbon\Carbon::parse($lote->Fecha_Llegada)->format('d/m/Y') }}</td>

                            {{-- Nombre de Variedad --}}
                            <td>{{ $lote->variedad->nombre ?? 'ERROR' }}</td>

                            {{-- CÓDIGO (Accede a la Variedad a través de la relación) --}}
                            <td>{{ $lote->variedad->codigo ?? 'N/A' }}</td>

                            <td>{{ number_format($lote->Cantidad_Plantas, 0) }}</td>
                            <td>{{ $lote->operadorLlegada->nombre ?? 'N/A' }}</td>
                            <td>
                                @if ($lote->Pre_Aclimatacion)
                                <span class="badge bg-warning">Sí</span>
                                @else
                                <span class="badge bg-success">No</span>
                                @endif
                            </td>
                            <td class="acciones-cell">

                                <a href="{{ route('llegada_planta.show', $lote->ID_Llegada) }}" class="btn btn-sm btn-info">Ver</a>
                            </td>
                        </tr>
                        @endforeach

                        </thead>


                        @if($lotes_llegada->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">No hay registros de inventario inicial.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection