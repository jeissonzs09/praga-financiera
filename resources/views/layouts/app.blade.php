<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PRAGA | {{ $titulo ?? 'Inicio' }}</title>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fuente institucional -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100;
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
<div class="nav-praga flex justify-between items-center">
    <div class="nav-left flex items-center gap-3">
        <div class="hamburger" onclick="toggleSidebar()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <img src="{{ asset('images/logo-praga.png') }}" alt="Logo PRAGA" class="h-8">
        <strong>PRAGA</strong> | {{ $titulo ?? 'Inicio' }}
    </div>

    <div class="flex items-center gap-4">
        @auth
            <!-- Icono de notificaciones -->
            <div class="relative">
                <button id="btnNotificaciones" class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C10.343 2 9 3.343 9 5v1.07C6.165 6.563 4 9.064 4 12v5l-1 1v1h18v-1l-1-1v-5c0-2.936-2.165-5.437-5-5.93V5c0-1.657-1.343-3-3-3zm0 20c1.104 0 2-.896 2-2h-4c0 1.104.896 2 2 2z"/>
                    </svg>
                    <!-- Badge -->
                    <span id="badgeNotificaciones" class="absolute top-0 right-0 inline-block w-4 h-4 bg-red-500 text-white text-xs font-bold rounded-full text-center leading-4">0</span>
                </button>
            </div>

            <!-- Nombre de usuario -->
            <span>Usuario: <strong>{{ Auth::user()->username }}</strong></span>

            <!-- Logout -->
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
    <a href="{{ route('clientes.index') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
        </svg>
        Clientes
    </a>

    <a href="{{ route('prestamos.index') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-blue-100 rounded transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 1L3 5v6c0 5.25 3.84 9.6 9 10 5.16-.4 9-4.75 9-10V5l-9-4zm0 2.18l6 2.67v4.32c0 4.08-2.97 7.63-6 7.95-3.03-.32-6-3.87-6-7.95V5.85l6-2.67zM11 11h2v4h-2v-4zm0 6h2v2h-2v-2z"/>
        </svg>
        <span>Préstamos</span>
    </a>

    <a href="{{ route('contratos.index') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-green-100 rounded transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
        <path d="M4 4h16v2H4V4zm0 4h16v2H4V8zm0 4h10v2H4v-2zm0 4h10v2H4v-2z"/>
    </svg>
    <span>Contratos</span>
</a>

    <a href="{{ route('pagos.index') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-indigo-600 rounded transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 6H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H4V8h16v8zm-9-6h2v4h-2v-4zm0 6h2v2h-2v-2z"/>
        </svg>
        <span>Registrar Pagos</span>
    </a>

    <a href="{{ route('refinanciamientos.index') }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L6 6v6c0 5.25 3.84 9.6 9 10 5.16-.4 9-4.75 9-10V6l-6-4zm0 2.18l4 2.67v4.32c0 4.08-2.97 7.63-6 7.95-3.03-.32-6-3.87-6-7.95V6.85l4-2.67zM11 11h2v4h-2v-4zm0 6h2v2h-2v-2z"/>
        </svg>
        Refinanciamientos
    </a>

<a href="{{ route('reportes.index') }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" viewBox="0 0 24 24" fill="currentColor">
        <path d="M5 3a1 1 0 0 1 1 1v16a1 1 0 0 1-2 0V4a1 1 0 0 1 1-1zm7 5a1 1 0 0 1 1 1v11a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm7 3a1 1 0 0 1 1 1v8a1 1 0 0 1-2 0v-8a1 1 0 0 1 1-1z"/>
    </svg>
    Reportes
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

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
@yield('scripts')

</body>
</html>