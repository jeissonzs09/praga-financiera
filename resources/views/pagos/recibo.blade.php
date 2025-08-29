<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Abono</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; margin: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .header img { max-height: 70px; }
        .header-info { text-align: right; }
        .recibo-num { font-weight: bold; font-size: 16px; margin-top: 5px; }
        .section { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f0f0f0; }
        .no-border td { border: none; }
        .totales { font-weight: bold; }
        .monto-letras { font-style: italic; margin-top: 10px; }
        .footer { margin-top: 40px; font-size: 12px; text-align: center; border-top: 1px solid #000; padding-top: 8px; }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <div>
            <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo PRAGA">
            <div>INVERSIONES PRAGA</div>
            <div>Tegucigalpa, D.C.</div>
            <div>Registro Tributario: 08011998123917</div>
            <div>Fecha límite de emisión: 31/12/2050</div>
        </div>
        <div class="header-info">
            <div class="recibo-num">RECIBO DE ABONO No. {{ sprintf('000-000-00-%08d', $pago->id_pago) }}</div>
            <div>Original</div>
        </div>
    </div>

    {{-- Datos del cliente y pago --}}
    <div class="section">
        <table class="no-border">
            <tr>
                <td><strong>Cliente:</strong> {{ $cliente->nombre_completo }}</td>
                <td><strong>Fecha:</strong> {{ $pago->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Préstamo:</strong> {{ $prestamo->codigo ?? $prestamo->id }}</td>
                <td><strong>Forma de pago:</strong> {{ $pago->forma_pago ?? 'Efectivo' }}</td>
            </tr>
        </table>
    </div>

    {{-- Totales generales --}}
    <div class="section">
        <table class="no-border">
            <tr>
                <td><strong>Saldo anterior:</strong> L. {{ number_format($saldo_anterior, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Abono capital:</strong> L. {{ number_format($abono_capital, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Saldo actual:</strong> L. {{ number_format($saldo_actual, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Monto en letras --}}
    <div class="monto-letras">
        *** {{ $monto_letras }} ***
    </div>

    {{-- Tabla de detalle por cuota --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Cuota</th>
                    <th>Interés</th>
                    <th>Capital</th>
                    <th>Recargo</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalle_cuotas as $detalle)
                <tr>
                    <td>{{ $detalle['cuota'] }}</td>
                    <td>{{ number_format($detalle['interes'], 2) }}</td>
                    <td>{{ number_format($detalle['capital'], 2) }}</td>
                    <td>{{ number_format($detalle['recargo'], 2) }}</td>
                    <td>{{ number_format($detalle['total'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="totales">
                    <td>Total</td>
                    <td>{{ number_format(collect($detalle_cuotas)->sum('interes'), 2) }}</td>
                    <td>{{ number_format(collect($detalle_cuotas)->sum('capital'), 2) }}</td>
                    <td>{{ number_format(collect($detalle_cuotas)->sum('recargo'), 2) }}</td>
                    <td>{{ number_format(collect($detalle_cuotas)->sum('total'), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pie --}}
    <div class="footer">
        Recibo generado por el Sistema PRAGA — {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>