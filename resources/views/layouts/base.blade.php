<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Panel Plantas Agrodex' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @vite('resources/js/app.js')
    @yield('custom_scripts')
</head>

<body>
    <div class="contenedor-pagina">
        <header>
            <div class="encabezado d-flex align-items-center">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{-- Usar la función asset() para rutas de imágenes --}}
                    <img src="{{ asset('assets/img/logoplantulas.png') }}" alt="Logo" width="130" height="124">
                </a>
                <div>
                    <h2>{{ $encabezado ?? 'Panel de Control' }}</h2>
                    <p>{{ $subtitulo ?? '' }}</p>
                </div>
            </div>

            <div class="barra-navegacion">
                <nav class="navbar bg-body-tertiary">
                    <div class="container-fluid d-flex justify-content-end">
                        <div class="d-flex gap-2">
                            {{-- Convertir la lógica de PHP puro a sintaxis Blade --}}
                            @isset($opciones_menu)
                            <div class="dropdown">
                                <button class="save-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Opciones
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach ($opciones_menu as $opcion)
                                    <li><a class="dropdown-item" href="{{ $opcion['ruta'] }}">{{ $opcion['texto'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            @endisset

                            @isset($texto_boton)
                            <div class="Opciones-barra">
                                <button class="save-button" onclick="window.location.href='{{ $ruta }}'">
                                    <i class="bi bi-arrow-left"></i> {{ $texto_boton }}
                                </button>
                            </div>
                            @endisset
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <main class="py-4">

            @yield('content')
        </main>
    </div>

    {{-- Script de Bootstrap necesario para el dropdown --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>