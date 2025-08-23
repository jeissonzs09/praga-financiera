<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PRAGA</title>

    <!-- Estilos compilados con Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: url('{{ asset('images/fondo-praga.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Inter', sans-serif;
        }
    </style>

    
</head>

<body class="min-h-screen relative">
    <!-- Fondo translúcido -->
    <div class="absolute inset-0 bg-black/30 z-0"></div>

    <!-- Contenido centrado -->
    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        @yield('content')
    </div>
</body>

</html>
