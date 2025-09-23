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
        .logo {
            width: 120px;
            margin: 0 auto 20px;
            display: block;
        }
        .firma {
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- 🔹 Logo institucional centrado --}}
    <div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA" style="width: 120px;">
</div>

    {{-- 🔹 Título principal --}}
    <p class="center"><strong>PAGARÉ POR: L. {{ number_format($prestamo->valor_prestamo, 2) }}</strong></p>

    {{-- 🔹 Texto legal --}}
    <p>
        YO, <strong>{{ $prestamo->cliente->nombre_completo }}</strong>, comerciante, hondureño con  
        Documento Nacional de Identificación No. <strong>{{ $prestamo->cliente->identificacion }}</strong>, con domicilio  
        en <strong>{{ $prestamo->cliente->direccion }}</strong>, de la ciudad de <strong>{{ $prestamo->cliente->ciudad }}</strong>, departamento de <strong>{{ $prestamo->cliente->departamento }}</strong>, declaro que debo y  
        PAGARÉ incondicionalmente la suma de <strong>{{ $letras }}</strong>  
        (LPS. <strong>{{ number_format($prestamo->valor_prestamo, 2) }}</strong>) a la orden de Inversiones Praga S.A. con registro tributario número  
        <strong>06119025150113</strong>, el día <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->format('d') }}</strong> del mes de <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->translatedFormat('F') }}</strong> del año <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->format('Y') }}</strong>.  

        En caso de mora el deudor reconocerá el pago del interés moratorio conforme a la tasa de interés máxima establecida.  
        Este PAGARÉ se libra SIN PROTESTO y los gastos que ocasione el cobro extrajudicial o la ejecución de este título serán por cuenta del deudor,  
        quien renuncia a toda diligencia, protesto, requerimiento judicial o extrajudicial, y toda otra notificación, así como a su domicilio,  
        para cualquier acción o procedimiento legal relacionado con este pagaré, sometiéndose a la competencia del Juzgado o Tribunal que destine el acreedor.
    </p>

    {{-- 🔹 Firma --}}
    <p>
        Firmo el presente pagaré en la ciudad de <strong>{{ $prestamo->cliente->ciudad }}</strong>, Departamento de <strong>{{ $prestamo->cliente->departamento }}</strong>,  
        a los <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->format('d') }}</strong> días del mes de <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->translatedFormat('F') }}</strong> del año <strong>{{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->format('Y') }}</strong>.
    </p>

    <div class="firma">
    <p>_____________________________</p>
    <p><strong>{{ $prestamo->cliente->nombre_completo }}</strong></p>
    <p>DNI: {{ $prestamo->cliente->identificacion }}</p>
</div>

</body>
</html>