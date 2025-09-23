<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 2.0;
            margin: 60px;
            text-align: justify;
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

    {{-- 🔹 Logo centrado --}}
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA" style="width: 120px;">
    </div>

    {{-- 🔹 Título --}}
    <p class="center"><strong>AUTORIZACIÓN</strong></p>

    {{-- 🔹 Contenido legal --}}
    <p>
        Yo, <strong>{{ $prestamo->cliente->nombre_completo }}</strong>, cédula No <strong>{{ $prestamo->cliente->identificacion }}</strong>, autorizo a <strong>INVERSIONES PRAGA S.A</strong> a consultar mi información crediticia en cualquier central de riesgo privada del país.
    </p>

    <p>
        Asimismo, si se me otorga una línea de crédito, quedo entendido(a) que mi información pasará a ser reportada a la Central de Riesgo mientras la deuda esté vigente y con saldo.
    </p>

    <p>
    Pespire, Choluteca, a los 
    <strong>{{ \Carbon\Carbon::parse($prestamo->created_at)->format('d') }}</strong> días del mes de 
    <strong>{{ \Carbon\Carbon::parse($prestamo->created_at)->translatedFormat('F') }}</strong> del año 
    <strong>{{ \Carbon\Carbon::parse($prestamo->created_at)->format('Y') }}</strong>.
</p>

    {{-- 🔹 Firma --}}
    <div class="firma" style="margin-top: 120px;">
        <p>____________________________</p>
        <p><strong>{{ $prestamo->cliente->nombre_completo }}</strong></p>
        <p>DNI: {{ $prestamo->cliente->identificacion }}</p>
    </div>

</body>
</html>