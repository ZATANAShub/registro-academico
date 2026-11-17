<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Registro Académico') · AUNAR Villavicencio</title>
    {{-- Bootstrap 5.3.3 copiado en public/bootstrap para que funcione sin internet --}}
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">
                Registro Académico
                <small class="d-none d-sm-inline fs-6 fw-normal opacity-75">· AUNAR Villavicencio</small>
            </span>
            @auth
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white small d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container mt-4 mb-5">
        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('contenido')
    </main>

    <footer class="text-center text-muted small pb-4">
        Programación Web II · Corporación Universitaria Autónoma de Nariño
    </footer>

    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
