<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ Setting::getValue('app_name') ?? 'Sistema de Ventas' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --secondary: #ec4899;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-main);
        }

        /* Animated background elements */
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: move 20s infinite alternate;
        }

        .blob-1 { top: -10%; left: -10%; background: rgba(79, 70, 229, 0.3); }
        .blob-2 { bottom: -10%; right: -10%; background: rgba(236, 72, 153, 0.3); }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(50px, 50px) scale(1.1); }
        }

        .glass-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease;
        }

        .glass-container:hover {
            transform: translateY(-5px);
        }

        .logo-container {
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .logo-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            background: white;
            padding: 10px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #ffffff;
            color: #4f46e5;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background: #f8fafc;
            transform: scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: #ffffff;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #ffffff;
        }

        .footer {
            margin-top: 3rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Float animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="glass-container">
        <div class="logo-container floating">
            @php $logo = Setting::getValue('app_logo'); @endphp
            @if($logo)
                <img src="{{ asset($logo) }}" alt="Logo" class="logo-img">
            @else
                <div class="logo-img" style="display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #4f46e5; font-weight: bold;">
                    {{ substr(Setting::getValue('app_name') ?? 'SV', 0, 2) }}
                </div>
            @endif
        </div>

        <h1>{{ Setting::getValue('app_name') ?? 'Sistema de Ventas' }}</h1>
        <p>Bienvenido al sistema de gestión de ventas más avanzado para su negocio. Gestione inventarios, créditos y finanzas en un solo lugar.</p>

        <div class="btn-group">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al Panel de Control</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Iniciar Sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-outline">Crear Cuenta</a>
                @endif
            @endauth
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ Setting::getValue('empresa_nombre') ?? 'SISNOMIA' }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
