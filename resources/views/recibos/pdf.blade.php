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
            <div>CAI:</div>
            <div>Rango autorizado: {{ $rangoAutorizado }}</div>
            <div>Fecha límite de emisión: {{ $fechaLimite }}</div>
        </div>
        <div class="header-info">
            <div class="recibo-num">RECIBO DE ABONO No. {{ sprintf('000-000-00-%08d', $recibo->id) }}</div>
            <div>Original</div>
        </div>
    </div>

    {{-- Datos del cliente y pago --}}
    <div class="section">
        <table class="no-border">
            <tr>
                <td><strong>Cliente:</strong> {{ $recibo->prestamo->cliente->nombre_completo }}</td>
                <td><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($recibo->fecha_pago)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Préstamo:</strong> {{ 'PRG-' . str_pad($recibo->prestamo->id, 6, '0', STR_PAD_LEFT) }}</td>
               <td><strong>Forma de pago:</strong> {{ ucfirst($recibo->metodo_pago ?? 'Efectivo') }}</td>
            </tr>
        </table>
    </div>

    {{-- Totales generales --}}
    <div class="section">
        <table class="no-border">
            <tr>
                <td><strong>Saldo anterior:</strong> L. {{ number_format($saldoAnterior, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Capital abonado:</strong> L. {{ number_format($capitalAbonado, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Saldo actual:</strong> L. {{ number_format($saldoActual, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Monto en letras --}}
    <div class="monto-letras">
        {{ $montoLetras }}
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
                    <th>Mora</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recibo->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cuota_numero }}</td>
                    <td>{{ number_format($detalle->interes, 2) }}</td>
                    <td>{{ number_format($detalle->capital, 2) }}</td>
                    <td>{{ number_format($detalle->recargo, 2) }}</td>
                    <td>{{ number_format($detalle->mora, 2) }}</td>
                    <td>{{ number_format($detalle->total, 2) }}</td>
                </tr>
                @endforeach
                <tr class="totales">
                    <td>Total</td>
                    <td>{{ number_format($recibo->detalles->sum('interes'), 2) }}</td>
                    <td>{{ number_format($recibo->detalles->sum('capital'), 2) }}</td>
                    <td>{{ number_format($recibo->detalles->sum('recargo'), 2) }}</td>
                    <td>{{ number_format($recibo->detalles->sum('mora'), 2) }}</td>
                    <td>{{ number_format($recibo->detalles->sum('total'), 2) }}</td>
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