<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Créditos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        h2 {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #1a73e8; /* Azul más visible */
            margin-bottom: 10px;
        }
        p {
            text-align: center;
            margin: 5px 0 15px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px 6px;
            text-align: center;
        }
        th {
            background-color: #1a73e8; /* Azul */
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f8ff; /* Azul muy suave */
        }
        tfoot th {
            background-color: #d4edda; /* Verde suave para totales */
        }
        .number {
            text-align: right;
        }
        td:first-child {
            width: 30px;
        }
        td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Inversiones PRAGA S.A.</h1>
    <h2>Reporte de Créditos</h2>
    <p>Desde: {{ $inicio }} | Hasta: {{ $fin }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Capital Pendiente</th>
                <th>Interés Pendiente</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach ($creditos as $row)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $row['cliente'] }}</td>
                    <td class="number">L {{ number_format($row['capital'], 2) }}</td>
                    <td class="number">L {{ number_format($row['interes'], 2) }}</td>
                    <td class="number">L {{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
    <tr>
        <th colspan="2" style="background-color: #a8d5ba;">Totales</th>
        <th class="number" style="background-color: #a8d5ba;">L {{ number_format($totalCapital, 2) }}</th>
        <th class="number" style="background-color: #a8d5ba;">L {{ number_format($totalInteres, 2) }}</th>
        <th class="number" style="background-color: #a8d5ba;">L {{ number_format($totalGeneral, 2) }}</th>
    </tr>
</tfoot>
    </table>
</body>
</html>