<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Veterinaria Uncaus')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f6fb;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }

        .navbar-veterinaria {
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            padding: 0.75rem 0;
        }

        .brand-logo {
            width: 34px;
            height: 34px;
            background-color: #2f6fed;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-right: 0.5rem;
        }

        .brand-text {
            font-weight: 700;
            color: #1f2430;
            font-size: 1.1rem;
        }

        .nav-pill {
            font-size: 0.9rem;
            color: #4a5063;
            text-decoration: none;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            margin-right: 0.4rem;
        }

        .nav-pill:hover {
            background-color: #f0f2f8;
            color: #1f2430;
        }

        .nav-pill.active {
            background-color: #2f6fed;
            color: #fff;
        }

        .btn-add-paciente {
            background-color: #16b871;
            border-color: #16b871;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.4rem 0.9rem;
        }

        .btn-add-paciente:hover {
            background-color: #129c60;
            color: #fff;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background-color: #16b871;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .user-label {
            font-size: 0.85rem;
            color: #4a5063;
        }

        .page-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            padding: 1.75rem;
        }

        .page-title {
            font-weight: 700;
            font-size: 1.3rem;
            color: #1f2430;
            margin-bottom: 0.1rem;
        }

        .page-subtitle {
            font-size: 0.85rem;
            color: #8a90a2;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1f2430;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dfe2ea;
            padding: 0.55rem 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2f6fed;
            box-shadow: 0 0 0 0.2rem rgba(47, 111, 237, 0.15);
        }

        .btn-guardar {
            background-color: #16b871;
            border-color: #16b871;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.55rem 1.5rem;
        }

        .btn-guardar:hover {
            background-color: #129c60;
        }

        .btn-cancelar {
            background-color: #fff;
            border: 1px solid #dfe2ea;
            color: #4a5063;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.55rem 1.5rem;
        }

        .table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            color: #8a90a2;
            border-bottom: 1px solid #eceef4;
            font-weight: 700;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
    </style>
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
                <span class="user-label me-2 d-none d-sm-inline">Veterinario {{ Auth::user()->name }}</span>
                <span class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                <form method="POST" action="{{ route('logout') }}" class="ms-3">
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

</body>
</html>
