@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton])

@section('content')
<div class="container mt-4 text-start">
    <h1 class="fw-bold">Historial de Recuperación de Merma</h1>

    @if (session('success'))
    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- Botón para registrar una nueva recuperación --}}
    <a href="{{ route('recuperacion.create') }}" class="btn btn-primary mb-3 shadow-sm">
        <i class="bi bi-arrow-up-circle"></i> Registrar Nueva Recuperación
    </a>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-3">Lote Origen</th> 
                            <th>Variedad / Color</th>
                            <th class="text-center">Fecha Recup.</th>
                            <th class="text-center">Cantidad Recuperada</th>
                            <th>Operador Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recuperaciones as $r)
                        @php
                            // Traducción de meses
                            $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            $meses_es = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $nombre_lote_es = str_ireplace($meses_en, $meses_es, $r->loteLlegada->nombre_lote_semana ?? 'N/A');

                            // Lógica de colores CSS (Idéntica a tu reporte mensual)
                            $coloresCss = [
                                'ROJO' => 'red', 'AZUL' => 'blue', 'VERDE' => 'green', 'AMARILLO' => 'yellow',
                                'NARANJA' => 'orange', 'ROSA' => 'pink', 'MORADO' => 'purple', 'FUCSIA' => '#FF00FF',
                                'CORAL' => '#FF7F50', 'BLANCO' => '#ffffff', 'NEGRO' => '#000000', 'GRIS' => 'gray',
                                'CAFE' => 'brown', 'CAFÉ' => 'brown'  
                            ];
                            $nombreColor = strtoupper(trim($r->loteLlegada->variedad->color ?? ''));
                            $colorFinal = $coloresCss[$nombreColor] ?? ($r->loteLlegada->variedad->color ?? '#cccccc');
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                {{ $nombre_lote_es }}
                            </td>

                            {{-- Variedad con Círculo de Color y Código --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-2 shadow-sm" 
                                         style="width: 15px; height: 15px; background-color: {{ $colorFinal }}; border: 1px solid #dee2e6;">
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block ">{{ $r->loteLlegada->variedad->nombre ?? 'N/A' }}</span>
                                        @if ($r->loteLlegada?->variedad?->codigo)
                                            <small class="text-muted text-uppercase">Cod: {{ $r->loteLlegada->variedad->codigo }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="text-center ">
                                {{ \Carbon\Carbon::parse($r->Fecha_Recuperacion)->format('d/m/Y') }}
                            </td>

                            <td class="text-center fw-bold text-success">
                                + {{ number_format($r->Cantidad_Recuperada, 0) }} und.
                            </td>

                            <td>
                                <span class="text-center">
                                   {{ $r->operadorResponsable->nombre ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-muted">
                                No hay registros de merma recuperada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection