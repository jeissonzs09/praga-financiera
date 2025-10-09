@php
use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        h2, h3 { text-align: center; margin: 2px 0; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 2px 3px; text-align: center; font-size: 10px; }
        th { background-color: #eaeaea; }
        .totales { font-weight: bold; background: #f8f8f8; }
        .seccion { margin-top: 10px; }
        .header-table td { border: none; text-align: left; padding: 2px 5px; }
        .header-table { margin-bottom: 5px; }
    </style>
</head>
<body>
    
{{-- 🔹 ENCABEZADO CENTRALIZADO --}}
<div style="text-align:center; margin-bottom:15px;">
    <div style="font-size:14px; font-weight:bold;">INVERSIONES PRAGA - DETALLE DE PRÉSTAMO PRENDARIO SIMPLE</div>
    <div style="font-size:12px; margin-top:5px;">
        <span><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</span> |
        <span><strong>Identidad:</strong> {{ $prestamo->cliente->identificacion ?? 'N/A' }}</span>
    </div>
    <div style="font-size:12px; margin-top:2px;">
        <span><strong>N° Préstamo:</strong> {{ $prestamo->id }}</span> |
        <span><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</span> |
        <span><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</span> |
        <span><strong>Plazo:</strong> {{ $prestamo->plazo }} {{ $prestamo->plazo > 1 ? 'meses' : 'mes' }}</span>
    </div>
    <div style="font-size:12px; margin-top:2px;">
        <strong>Fecha de emisión:</strong> {{ Carbon::now()->format('d/m/Y') }}
    </div>
</div>

    {{-- 🔹 PLAN ORIGINAL --}}
    <div class="seccion">
        <h3>Plan Original</h3>
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
                <tr class="totales">
                    <td colspan="2">Totales</td>
                    <td>{{ number_format($totalesOriginal['capital'], 2) }}</td>
                    <td>{{ number_format($totalesOriginal['interes'], 2) }}</td>
                    <td>{{ number_format($totalesOriginal['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 🔹 CUOTAS PENDIENTES --}}
    <div class="seccion">
        <h3>Cuotas Pendientes</h3>
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
                <tr class="totales">
                    <td colspan="2">Totales</td>
                    <td>{{ number_format($totalesPendientes['capital'], 2) }}</td>
                    <td>{{ number_format($totalesPendientes['interes'], 2) }}</td>
                    <td>{{ number_format($totalesPendientes['recargos'], 2) }}</td>
                    <td>{{ number_format($totalesPendientes['mora'], 2) }}</td>
                    <td>{{ number_format($totalesPendientes['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 🔹 CUOTAS PAGADAS --}}
    <div class="seccion">
        <h3>Cuotas Pagadas</h3>
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
                <tr class="totales">
                    <td colspan="2">Totales</td>
                    <td>{{ number_format($totalesPagadas['capital'], 2) }}</td>
                    <td>{{ number_format($totalesPagadas['interes'], 2) }}</td>
                    <td>{{ number_format($totalesPagadas['recargos'], 2) }}</td>
                    <td>{{ number_format($totalesPagadas['mora'], 2) }}</td>
                    <td>{{ number_format($totalesPagadas['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>