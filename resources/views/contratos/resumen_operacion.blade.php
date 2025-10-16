<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resumen de Operación - Préstamo {{ $prestamo->id }}</title>
<style>
    body {
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.8;
        margin: 25px 25px;
        text-align: justify;
        position: relative;
    }

    /* 🔷 Logo institucional arriba de todo */
    .logo-superior {
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: auto;
    }

    /* 🔷 Título centrado debajo del logo */
    .titulo {
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
        margin-top: 60px; /* espacio para el logo */
        margin-bottom: 15px;
    }

    .section-title {
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 5px;
    }

    .firma {
        margin-top: 40px;
        text-align: center;
    }

    p {
        margin: 2px 0;
    }
</style>
</head>
<body>

<!-- 🔷 Logo institucional arriba a la derecha -->
<img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA" class="logo-superior">

<!-- 🔷 Título centrado -->
<h1 class="titulo">Resumen de Datos Claves</h1>

<p>Estimado Señor(a): <strong>{{ $prestamo->cliente->nombre_completo }}</strong>,</p>
<p>A continuación, le resumimos los datos claves de las operaciones realizadas el día de hoy:</p>

<div>
    <p><strong>Número de Préstamo:</strong> {{ $prestamo->id }}</p>
    <p><strong>Monto Aprobado:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
    <p><strong>Plazo:</strong> {{ $prestamo->plazo }} {{ $prestamo->plazo > 1 ? 'Meses' : 'Mes' }}</p>
    <p><strong>Fecha Inicial:</strong> {{ $fechaInicial }}</p>
    <p><strong>Fecha Final:</strong> {{ $fechaFinal }}</p>
    <p><strong>Valor de Cuota:</strong> L. {{ $valorCuota }}</p>
    <p><strong>Número de Cuotas:</strong> {{ $numeroCuotas }}</p>
    <p><strong>Días o Fechas de Pago:</strong> {{ $diaPago }}</p>
    <p><strong>Periodicidad:</strong> {{ $periodicidad }}</p>
</div>

<div class="section-title">DEDUCCIONES:</div>
<div>
    <p>Gastos Administrativos del 1%: L. {{ number_format($deducciones['gastosAdministrativos'], 2) }}</p>
    <p>Deducción de Central de Riesgos: L. {{ number_format($deducciones['deduccionCentral'], 2) }}</p>
    <p>Capital Pendiente del Crédito: L. {{ number_format($deducciones['capitalPendiente'], 2) }}</p>
    <p>Intereses Pendientes del Crédito: L. {{ number_format($deducciones['interesPendiente'], 2) }}</p>
    <p>Mora del Crédito: L. {{ number_format($deducciones['mora'], 2) }}</p>
    <p><strong>Total de Deducciones:</strong> L. {{ number_format($deducciones['totalDeducciones'], 2) }}</p>
    <p><strong>Total a Entregar:</strong> L. {{ number_format($deducciones['totalEntregar'], 2) }}</p>
</div>

<div class="firma">
    <p>____________________________</p>
    <p>Firma</p>
</div>

</body>
</html>
