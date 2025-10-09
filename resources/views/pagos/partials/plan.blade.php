<div>
    <h3 class="text-lg font-semibold mb-3">
        Plan de pago de {{ $prestamo->cliente->nombre_completo }}
    </h3>

    <table class="min-w-full text-sm text-gray-700 border">
        <thead class="bg-blue-900 text-white">
            <tr>
                <th class="px-3 py-2">Cuota</th>
                <th class="px-3 py-2">Vence</th>
                <th class="px-3 py-2">Capital</th>
                <th class="px-3 py-2">Interés</th>
                <th class="px-3 py-2">Recargos</th>
                <th class="px-3 py-2">Mora</th>
                <th class="px-3 py-2">Total</th>
                <th class="px-3 py-2">Saldo Capital</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plan as $cuota)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-1">{{ $cuota['nro'] }}</td>
                    <td class="px-3 py-1">{{ $cuota['vence'] }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['capital'], 2) }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['interes'], 2) }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['recargos'], 2) }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['mora'], 2) }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['total'], 2) }}</td>
                    <td class="px-3 py-1">L. {{ number_format($cuota['saldo'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>