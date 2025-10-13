@php
use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #333; }
        h2, h3 { text-align: center; margin: 5px 0; font-weight: normal; color: #1a202c; }
        .section-title {
            background-color: #1f2937;
            color: #fff;
            padding: 5px;
            font-size: 12px;
            margin-top: 15px;
            text-align: center;
            border-radius: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: center; }
        th { background-color: #e5e7eb; }
        .totales { font-weight: bold; background-color: #f3f4f6; }
        .header-info { width: 100%; margin-bottom: 10px; font-size: 11px; }
        .header-info td { border: none; text-align: left; padding: 2px 4px; }
        .totals-row td { border-top: 2px solid #000; }
    </style>
</head>
<body>

    {{-- 🔹 ENCABEZADO CENTRALIZADO --}}
    <table class="header-info">
        <tr>
            <td colspan="2" style="text-align:center; font-weight:bold; font-size:14px;">
                INVERSIONES PRAGA - DETALLE DE PRÉSTAMO PRENDARIO SIMPLE
            </td>
        </tr>
        <tr>
            <td><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</td>
            <td><strong>Identidad:</strong> {{ $prestamo->cliente->identificacion ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>N° Préstamo:</strong> {{ $prestamo->id }}</td>
            <td><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</td>
            <td><strong>Plazo:</strong> {{ $prestamo->plazo }} {{ $prestamo->plazo > 1 ? 'meses' : 'mes' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Fecha de emisión:</strong> {{ Carbon::now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    {{-- 🔹 PLAN ORIGINAL --}}
    <div class="section-title">PLAN ORIGINAL</div>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Vence</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotasOriginales as $cuota)
            <tr>
                <td>{{ $cuota['nro'] }}</td>
                <td>{{ $cuota['vence'] ?? '-' }}</td>
                <td>{{ number_format($cuota['capital_original'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['interes_original'] ?? 0, 2) }}</td>
                <td>{{ number_format(($cuota['capital_original'] ?? 0) + ($cuota['interes_original'] ?? 0), 2) }}</td>
            </tr>
            @endforeach
            <tr class="totales totals-row">
                <td colspan="2">Totales</td>
                <td>{{ number_format($totalesOriginal['capital'], 2) }}</td>
                <td>{{ number_format($totalesOriginal['interes'], 2) }}</td>
                <td>{{ number_format($totalesOriginal['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 🔹 CUOTAS PENDIENTES --}}
    <div class="section-title">CUOTAS PENDIENTES</div>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Vence</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Recargos</th>
                <th>Mora</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendientes as $cuota)
            <tr>
                <td>{{ $cuota['nro'] }}</td>
                <td>{{ $cuota['vence'] ?? '-' }}</td>
                <td>{{ number_format($cuota['capital'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['interes'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['recargos'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['mora'] ?? 0, 2) }}</td>
                <td>{{ number_format(($cuota['capital'] ?? 0) + ($cuota['interes'] ?? 0) + ($cuota['recargos'] ?? 0) + ($cuota['mora'] ?? 0), 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="7">No hay cuotas pendientes.</td></tr>
            @endforelse
            <tr class="totales totals-row">
                <td colspan="2">Totales</td>
                <td>{{ number_format($totalesPendientes['capital'], 2) }}</td>
                <td>{{ number_format($totalesPendientes['interes'], 2) }}</td>
                <td>{{ number_format($totalesPendientes['recargos'], 2) }}</td>
                <td>{{ number_format($totalesPendientes['mora'], 2) }}</td>
                <td>{{ number_format($totalesPendientes['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 🔹 PAGOS --}}
    <div class="section-title">PAGOS REALIZADOS</div>
    <table>
        <thead>
            <tr>
                <th>N° Recibo</th>
                <th>Fecha</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>Recargos</th>
                <th>Mora</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagadas as $cuota)
            <tr>
                <td>{{ $cuota['nro'] }}</td>
                <td>{{ $cuota['vence'] ?? '-' }}</td>
                <td>{{ number_format($cuota['capital'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['interes'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['recargos'] ?? 0, 2) }}</td>
                <td>{{ number_format($cuota['mora'] ?? 0, 2) }}</td>
                <td>{{ number_format(($cuota['capital'] ?? 0) + ($cuota['interes'] ?? 0) + ($cuota['recargos'] ?? 0) + ($cuota['mora'] ?? 0), 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="7">No hay cuotas pagadas.</td></tr>
            @endforelse
            <tr class="totales totals-row">
                <td colspan="2">Totales</td>
                <td>{{ number_format($totalesPagadas['capital'], 2) }}</td>
                <td>{{ number_format($totalesPagadas['interes'], 2) }}</td>
                <td>{{ number_format($totalesPagadas['recargos'], 2) }}</td>
                <td>{{ number_format($totalesPagadas['mora'], 2) }}</td>
                <td>{{ number_format($totalesPagadas['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>