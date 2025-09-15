<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan Original - Préstamo {{ $prestamo->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
            color: #333;
        }
        .encabezado {
            margin-bottom: 20px;
        }
        .encabezado-table {
            width: 100%;
        }
        .encabezado-table td {
            vertical-align: top;
        }
        .logo {
            width: 100px;
        }
        .titulo {
            text-align: center;
        }
        .titulo h1 {
            font-size: 22px;
            color: #003366;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .titulo h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .titulo p {
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #003366;
            color: white;
            font-size: 12px;
        }
        tfoot td {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <table class="encabezado-table">
            <tr>
                <td style="width: 120px; text-align: center; vertical-align: middle;">
    <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA" style="width: 100px;">
</td>

                <td class="titulo" style="text-align: center;">
                    <h1>INVERSIONES PRAGA</h1>
                    <h2>PLAN DE PAGO ORIGINAL</h2>
                    <p><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</p>
                    <p><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
                    <p><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</p>
                    <p><strong>Plazo:</strong> {{ $prestamo->plazo }} meses</p>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cuota</th>
                <th>Vence</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Total</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotas as $cuota)
                <tr>
                    <td>{{ $cuota['nro'] }}</td>
                    <td>{{ $cuota['vence'] }}</td>
                    <td>L. {{ number_format($cuota['capital'], 2) }}</td>
                    <td>L. {{ number_format($cuota['interes'], 2) }}</td>
                    <td>L. {{ number_format($cuota['total'], 2) }}</td>
                    <td>L. {{ number_format($cuota['saldo'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totalCapital = array_sum(array_column($cuotas, 'capital'));
                $totalInteres = array_sum(array_column($cuotas, 'interes'));
                $totalPago    = array_sum(array_column($cuotas, 'total'));
            @endphp
            <tr>
                <td colspan="2">Totales</td>
                <td>L. {{ number_format($totalCapital, 2) }}</td>
                <td>L. {{ number_format($totalInteres, 2) }}</td>
                <td>L. {{ number_format($totalPago, 2) }}</td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>