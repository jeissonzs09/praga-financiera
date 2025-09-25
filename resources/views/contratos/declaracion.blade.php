<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 60px;
            text-align: justify;
        }
        .logo {
            width: 120px;
            margin: 0 auto 20px;
            display: block;
        }
        .center {
            text-align: center;
        }
        .firma {
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- 🔹 Logo institucional --}}
    <div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA" style="width: 120px;">
</div>


    {{-- 🔹 Título --}}
    <p class="center"><strong>INVERSIONES PRAGA S.A</strong></p>
    <p class="center"><strong>DECLARACIÓN JURADA DE GARANTÍAS</strong></p>

    {{-- 🔹 Contenido legal --}}
    <p>
        Yo, <strong>{{ $prestamo->cliente->nombre_completo }}</strong>, número de identidad <strong>{{ $prestamo->cliente->identificacion }}</strong>, declaro bajo juramento que los artículos descritos a continuación son de mi legítima propiedad. Así como:
    </p>

@php
    $items = json_decode($prestamo->cliente->garantia);
@endphp

@if(!empty($items) && is_array($items))
<ol>
    @foreach($items as $item)
        @if(!is_null($item) && trim($item) !== '')
            <li>{{ $item }}</li>
        @endif
    @endforeach
</ol>
@else
<p><em>No se han registrado garantías para este cliente.</em></p>
@endif

    <p>
        En caso de comprobarse lo contrario, autorizo expresamente a la empresa a iniciar las acciones legales que estime pertinentes en mi contra, asumiendo las consecuencias civiles, penales o administrativas que de ello se deriven.
    </p>

Firmo la presente en la ciudad de <strong>{{ $ciudadDeclaracion }}</strong>, 
departamento de <strong>{{ $departamentoDeclaracion }}</strong>, a los 
<strong>{{ \Carbon\Carbon::parse($fechaDeclaracion)->format('d') }}</strong> días del mes de 
<strong>{{ \Carbon\Carbon::parse($fechaDeclaracion)->translatedFormat('F') }}</strong> del año 
<strong>{{ \Carbon\Carbon::parse($fechaDeclaracion)->format('Y') }}</strong>.


    {{-- 🔹 Firma --}}
    <div class="firma">
        <p>_____________________________</p>
        <p><strong>{{ $prestamo->cliente->nombre_completo }}</strong></p>
        <p>DNI: {{ $prestamo->cliente->identificacion }}</p>
    </div>

</body>
</html>