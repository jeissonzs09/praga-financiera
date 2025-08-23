<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PRAGA | Panel Institucional</title>

    <!-- Fuente institucional -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: #f9fafb;
        }

        .nav-praga {
            background-color: #34495e;
            color: #ecf0f1;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-left img {
            height: 32px;
        }

        .hamburger {
            display: inline-block;
            cursor: pointer;
            margin-right: 1rem;
        }

        .hamburger div {
            width: 22px;
            height: 3px;
            background-color: #ecf0f1;
            margin: 4px 0;
            transition: 0.4s;
        }

        .sidebar {
    width: 220px;
    background-color: #2c3e50;
    color: #ecf0f1;
    position: fixed;
    top: 60px;
    bottom: 0;
    left: 0;
    padding: 1rem;
    z-index: 1000;
    transform: translateX(0);
    opacity: 1;
    transition: transform 0.5s ease, opacity 0.5s ease;
    pointer-events: auto;
}

    .sidebar.hidden {
    transform: translateX(-100%);
    opacity: 0;
    pointer-events: none;
}

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ecf0f1;
            margin-bottom: 1rem;
            font-weight: 500;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease-in-out;
        }

        .sidebar a:hover {
            background-color: #3b4b5a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transform: translateX(4px);
        }

        .sidebar svg {
            width: 18px;
            height: 18px;
            fill: #ecf0f1;
        }

       .main-content {
    margin-left: 220px;
    padding: 2rem;
    margin-top: 60px;
    transition: margin-left 0.5s ease;
}

    .main-content.expanded {
    margin-left: 0;
}

        .logout-button {
            background-color: #e74c3c;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

    <!-- 🔷 Barra superior -->
    <div class="nav-praga">
        <div class="nav-left">
            <div class="hamburger" onclick="toggleSidebar()">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <img src="{{ asset('images/logo-praga.png') }}" alt="Logo PRAGA">
            <strong>PRAGA</strong> | Inicio
        </div>
        <div>
            @auth
                <span class="mr-4">Usuario: <strong>{{ Auth::user()->username }}</strong></span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="logout-button">Cerrar sesión</button>
                </form>
            @endauth
        </div>
    </div>

    <!-- 🔷 Sidebar izquierda -->
    <div id="sidebar" class="sidebar">
        <a href="{{ route('dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8v-10h-8v10zm0-18v6h8V3h-8z"/></svg>
            Panel principal
        </a>
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 3v18h18V3H3zm16 16H5V5h14v14z"/></svg>
            Estado financiero
        </a>
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            Cronograma de pagos
        </a>
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z"/></svg>
            Reportes
        </a>
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 4h16v2H4zm0 4h10v2H4zm0 4h16v2H4zm0 4h10v2H4zm0 4h16v2H4z"/></svg>
            Inventario
        </a>
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            Usuarios
        </a>
    </div>

    <!-- 🔷 Contenido principal -->
    <main id="mainContent" class="main-content">
        @yield('content')
    </main>

    <!-- 🔧 Script para toggle -->
    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('mainContent');
        sidebar.classList.toggle('hidden');
        main.classList.toggle('expanded');
    }
</script>

</body>
</html>