@extends('layouts.base', ['ruta' => $ruta, 'texto_boton' => $texto_boton]) 

@section('content')

    <main class="container py-4">
        
        {{-- Encabezado y Usuario --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">{{ $encabezado ?? 'Panel de Módulos' }}</h1>
            <div class="user-info">
                <span class="me-2">{{ $usuario_nombre ?? 'Usuario' }}</span>
                <i class="bi bi-person-circle"></i>
            </div>
        </div>
        
        <p class="lead">{{ $subtitulo ?? '' }}</p>
        
        {{-- Tarjetas de Módulos --}}
        <div class="row g-4">
            
            {{-- MÓDULO 1: OPERADORES (Personal) --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-person-badge text-success fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0">Personal</h3>
                        </div>
                        <p class="card-text">Gestión de personal que realiza tareas en el invernadero.</p>
                        {{-- Usamos route() de Laravel --}}
                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-success stretched-link">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- MÓDULO 2: CATALOGO (Catálogo) --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-flower1 text-info fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0">Catalogo</h3>
                        </div>
                        <p class="card-text">Catálogo de especies de plantas que se cultivan.</p>
                        <a href="{{ route('variedades.index') }}" class="btn btn-outline-info stretched-link">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- MÓDULO 3: LLEGADA DE PLANTA (Inventario) --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-box-seam text-warning fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0">Llegada Plantas</h3>
                        </div>
                        <p class="card-text">Registro de lotes de inventario y trazabilidad inicial.</p>
                        <a href="{{ route('llegada_planta.index') }}" class="btn btn-outline-warning stretched-link">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

 
            {{-- MÓDULO 4: ACLIMATACION (Etapa de Producción)--}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-globe-americas text-danger fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0">Aclimatación</h3>
                        </div>
                        <p class="card-text">registro de el inicio del proceso de adaptación de la planta después de la plantación.</p>
                        <a href="{{ route('aclimatacion.index') }}" class="btn btn-outline-danger stretched-link">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            {{-- MÓDULO 5: ENDURECIMIENETO (Etapa de Producción) --}}
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-globe-americas text-danger fs-2"></i>
                            </div>
                            <h3 class="h5 mb-0">Endurecimiento</h3>
                        </div>
                        <p class="card-text"></p>
                        <a href="{{ route('endurecimiento.index') }}" class="btn btn-outline-danger stretched-link">
                            Acceder <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
           
            
            
            
        </div>
    </main>
@endsection