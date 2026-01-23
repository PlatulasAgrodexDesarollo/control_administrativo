@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <main class="container py-4 text-start">
        
        {{-- Encabezado y Usuario --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 fw-bold text-dark">{{ $encabezado ?? 'Panel de Módulos' }}</h1>
            <div class="user-info bg-white px-3 py-2 rounded-pill shadow-sm border">
                <span class="me-2 fw-bold text-secondary">{{ $usuario_nombre ?? 'Usuario' }}</span>
                <i class="bi bi-person-circle text-primary"></i>
            </div>
        </div>
        
        <p class="lead text-muted">{{ $subtitulo ?? 'Seleccione un módulo para gestionar la producción.' }}</p>
        
        {{-- Tarjetas de Módulos --}}
        <div class="row g-4">
            
            {{-- MÓDULO 1: OPERADORES --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-person-badge text-success fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Personal</h3>
                        </div>
                        <p class="card-text text-secondary">Gestión de personal que realiza tareas en el invernadero.</p>
                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-success stretched-link border-2 fw-bold">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- MÓDULO 2: CATALOGO --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-flower1 text-info fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Catálogo</h3>
                        </div>
                        <p class="card-text text-secondary">Catálogo de especies de plantas que se cultivan.</p>
                        <a href="{{ route('variedades.index') }}" class="btn btn-outline-info stretched-link border-2 fw-bold">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- MÓDULO 3: LLEGADA DE PLANTA --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-box-seam text-warning fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Llegada Plantas</h3>
                        </div>
                        <p class="card-text text-secondary">Registro de lotes de inventario y trazabilidad inicial.</p>
                        <a href="{{ route('llegada_planta.index') }}" class="btn btn-outline-warning stretched-link border-2 fw-bold">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- MÓDULO 4: ACLIMATACIÓN --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-globe-americas text-danger fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Aclimatación</h3>
                        </div>
                        <p class="card-text text-secondary">Inicio del proceso de adaptación de la planta tras la plantación.</p>
                        <a href="{{ route('aclimatacion.index') }}" class="btn btn-outline-danger stretched-link border-2 fw-bold">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- MÓDULO 5: ENDURECIMIENTO --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-moisture text-primary fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Endurecimiento</h3>
                        </div>
                        <p class="card-text text-secondary">Etapa final de fortalecimiento previa al despacho de planta.</p>
                        <a href="{{ route('endurecimiento.index') }}" class="btn btn-outline-primary stretched-link border-2 fw-bold">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- NUEVO MÓDULO 6: ANÁLISIS GLOBAL MENSUAL --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 border-top border-5 border-dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-dark bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-bar-chart-line-fill text-dark fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0 fw-bold">Análisis Mensual</h3>
                        </div>
                        <p class="card-text text-secondary">Reporte estadístico de ingresos, mermas y eficiencia por variedad.</p>
                        <a href="{{ route('reporte.mensual') }}" class="btn btn-dark stretched-link fw-bold">
                            Ver Reporte <i class="bi bi-graph-up-arrow ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection