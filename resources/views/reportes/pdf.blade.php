<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Cuotas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; }
        .header h2 { font-size: 16px; margin-top: 5px; }
        .header p { font-size: 14px; margin-top: 5px; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #007bff; color: #fff; text-align: center; }
        td { text-align: right; }
        td.cliente, td.cuota { text-align: center; } /* centramos cliente y nro cuota */
        tfoot td { font-weight: bold; background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inversiones PRAGA S.A.</h1>
        <h2>Reporte de Cuotas</h2>
        <p>Desde: {{ $inicio->format('Y-m-d') }} | Hasta: {{ $fin->format('Y-m-d') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>N° Cuota</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCapital = 0;
                $totalInteres = 0;
                $totalGeneral = 0;
            @endphp

            @foreach($pagos as $pago)
                @php
                    $totalCapital += $pago->capital;
                    $totalInteres += $pago->interes;
                    $totalGeneral += $pago->capital + $pago->interes;
                @endphp
                <tr>
                    <td class="cliente">{{ $pago->prestamo->cliente->nombre_completo }}</td>
                    <td class="cuota">{{ $pago->cuota_numero }}</td>
                    <td>L. {{ number_format($pago->capital, 2) }}</td>
                    <td>L. {{ number_format($pago->interes, 2) }}</td>
                    <td>L. {{ number_format($pago->capital + $pago->interes, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:center">Totales</td>
                <td>L. {{ number_format($totalCapital, 2) }}</td>
                <td>L. {{ number_format($totalInteres, 2) }}</td>
                <td>L. {{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>