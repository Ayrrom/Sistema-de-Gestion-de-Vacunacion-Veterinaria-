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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }

        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            padding: 2.5rem 2.25rem;
            width: 100%;
            max-width: 380px;
        }

        .auth-logo {
            width: 64px;
            height: 64px;
            background-color: #2f6fed;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            font-size: 28px;
        }

        .auth-title {
            text-align: center;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 0.15rem;
            color: #1f2430;
        }

        .auth-subtitle {
            text-align: center;
            font-size: 0.8rem;
            color: #8a90a2;
            margin-bottom: 1.75rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1f2430;
        }

        .input-group-text {
            background-color: #fff;
            border-right: none;
            color: #9aa0b1;
        }

        .form-control {
            border-left: none;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(47, 111, 237, 0.15);
            border-radius: 0.375rem;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #2f6fed;
        }

        .btn-primary {
            background-color: #2f6fed;
            border-color: #2f6fed;
            font-weight: 600;
            padding: 0.6rem 0;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #2559c9;
            border-color: #2559c9;
        }

        .forgot-link {
            font-size: 0.8rem;
            text-decoration: none;
        }

        .auth-footer {
            text-align: center;
            font-size: 0.75rem;
            color: #9aa0b1;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-logo">🐾</div>
        <div class="auth-title">Veterinaria Uncaus</div>
        <div class="auth-subtitle">Universidad Nacional del Chaco Austral</div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <div class="auth-footer">
        Sistema de Gestión Veterinaria - UNCAUS &copy; {{ date('Y') }}
    </div>

</body>
</html>
