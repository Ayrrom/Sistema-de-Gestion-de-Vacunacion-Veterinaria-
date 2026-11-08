<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Veterinaria Uncaus')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>

    <nav class="navbar navbar-veterinaria">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                    <span class="brand-logo">🐾</span>
                    <span class="brand-text">Veterinaria Uncaus</span>
                </a>

                <div class="ms-4 d-none d-md-flex align-items-center">
                    <a href="{{ route('mascotas.index') }}" class="nav-pill {{ request()->routeIs('mascotas.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-pulse"></i> Pacientes
                    </a>
                    <a href="{{ route('propietarios.index') }}" class="nav-pill {{ request()->routeIs('propietarios.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Propietarios
                    </a>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <a href="{{ route('mascotas.create') }}" class="btn btn-add-paciente me-3">
                    <i class="bi bi-plus-lg"></i> Añadir Paciente
                </a>
                <span class="user-label me-2 d-none d-sm-inline">{{ Auth::user()->nombre_rol }} {{ Auth::user()->name }}</span>
                <span class="user-avatar">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 2)) }}</span>
                @if (Auth::user()->esAdmin())
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary ms-3" title="Usuarios">
                        <i class="bi bi-people-fill"></i>
                    </a>
                @endif
                <a href="{{ route('password.edit') }}" class="btn btn-sm btn-outline-secondary ms-2" title="Cambiar contraseña">
                    <i class="bi bi-key"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="{{ asset('js/solo-numeros.js') }}?v={{ filemtime(public_path('js/solo-numeros.js')) }}" defer></script>
    <script src="{{ asset('js/sugerencias-dni.js') }}?v={{ filemtime(public_path('js/sugerencias-dni.js')) }}" defer></script>
</body>
</html>
