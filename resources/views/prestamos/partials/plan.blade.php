<div>
    {{-- 🔹 Encabezado con datos del préstamo centrado --}}
    <div class="mb-4 p-4 bg-gray-100 rounded-lg shadow-sm text-center">
        <h3 class="text-lg font-semibold mb-2">INVERSIONES PRAGA - DETALLE DE PRÉSTAMO</h3>
        <p><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</p>
        <p><strong>Identidad:</strong> {{ $prestamo->cliente->identidad }}</p>
        <p><strong>N° Préstamo:</strong> {{ $prestamo->id }}</p>
        <p><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
        <p><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</p>
        <p><strong>Plazo:</strong> {{ $prestamo->plazo }} meses</p>
    </div>

    {{-- 🔹 Filtro por estado --}}
    <div class="mb-2 flex justify-end items-center gap-2">
        <label for="filtroEstado" class="text-sm font-semibold">Mostrar:</label>
        <select id="filtroEstado" class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="Todas" {{ ($estadoSeleccionado ?? '') === 'Todas' ? 'selected' : '' }}>Todas</option>
            <option value="Pendientes" {{ ($estadoSeleccionado ?? '') === 'Pendientes' ? 'selected' : '' }}>Pendientes</option>
            <option value="Pagadas" {{ ($estadoSeleccionado ?? '') === 'Pagadas' ? 'selected' : '' }}>Pagadas</option>
        </select>
    </div>

    <div class="mb-4 flex justify-end gap-2">
    <a href="{{ route('pagos.plan.original.pdf', $prestamo->id) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
        🖨️ Descargar Plan Original
    </a>

    <a href="{{ route('pagos.estado.cuenta.pdf', $prestamo->id) }}"
   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
    🖨️ Descargar Estado de Cuenta
</a>

</div>

    {{-- 🔹 Tabla del plan de pagos --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-700 border">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="px-3 py-2 text-left">Cuota</th>
                    <th class="px-3 py-2 text-left">Vence</th>
                    <th class="px-3 py-2 text-left">Capital</th>
                    <th class="px-3 py-2 text-left">Interés</th>
                    <th class="px-3 py-2 text-left">Recargos</th>
                    <th class="px-3 py-2 text-left">Mora</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    <th class="px-3 py-2 text-left">Saldo Capital</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCapital = 0;
                    $totalInteres = 0;
                    $totalRecargos = 0;
                    $totalMora = 0;
                    $totalTotal = 0;
                @endphp

                @foreach ($cuotas as $cuota)
                    @php
                        $mostrar = false;

                        if(($estadoSeleccionado ?? 'Todas') === 'Todas') {
                            $mostrar = true;
                        } elseif($estadoSeleccionado === 'Pendientes' && in_array($cuota['estado'], ['Pendiente','Parcial','Vencida'])) {
                            $mostrar = true;
                        } elseif($estadoSeleccionado === 'Pagadas' && $cuota['estado'] === 'Pagada') {
                            $mostrar = true;
                        }

                        // Valores a mostrar según el estado
                        $cap = $cuota['capital'];
                        $int = $cuota['interes'];

                        if($estadoSeleccionado === 'Pagadas') {
                            $cap = $cuota['capital_original'] ?? $cuota['capital'];
                            $int = $cuota['interes_original'] ?? $cuota['interes'];
                        }

                        $totalCuota = $cap + $int + $cuota['recargos'] + $cuota['mora'];

                        $totalCapital += $cap;
                        $totalInteres += $int;
                        $totalRecargos += $cuota['recargos'];
                        $totalMora += $cuota['mora'];
                        $totalTotal += $totalCuota;

                        // Clase según estado
                        if ($cuota['estado'] === 'Pagada' && $cuota['es_tardio'] ?? false) {
                            $clase = 'bg-red-100 text-red-800';
                        } elseif ($cuota['estado'] === 'Pagada') {
                            $clase = 'bg-green-100 text-green-800';
                        } elseif ($cuota['estado'] === 'Parcial') {
                            $clase = 'bg-yellow-100 text-yellow-800';
                        } elseif (in_array($cuota['estado'], ['Pendiente', 'Vencida']) && \Carbon\Carbon::parse($cuota['vence'])->isPast()) {
                            $clase = 'bg-red-100 text-red-800';
                        } else {
                            $clase = '';
                        }
                    @endphp

                    @if($mostrar)
                        <tr class="border-b hover:bg-gray-50 {{ $clase }}">
                            <td class="px-3 py-1 text-center">{{ $cuota['nro'] }}</td>
                            <td class="px-3 py-1 text-center">{{ \Carbon\Carbon::parse($cuota['vence'])->format('d/m/Y') }}</td>
                            <td class="px-3 py-1 text-right">L. {{ number_format($cap, 2) }}</td>
                            <td class="px-3 py-1 text-right">L. {{ number_format($int, 2) }}</td>
                            <td class="px-3 py-1 text-right">L. {{ number_format($cuota['recargos'], 2) }}</td>
                            <td class="px-3 py-1 text-right">L. {{ number_format($cuota['mora'], 2) }}</td>
                            <td class="px-3 py-1 text-right font-bold">L. {{ number_format($totalCuota, 2) }}</td>
                            <td class="px-3 py-1 text-right">L. {{ number_format($cuota['saldo'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>

            {{-- 🔹 Totales --}}
            <tfoot class="bg-gray-50 text-sm font-semibold">
                <tr>
                    <td class="px-4 py-2 text-right" colspan="2">Totales</td>
                    <td class="px-4 py-2 text-right">L. {{ number_format($totalCapital, 2) }}</td>
                    <td class="px-4 py-2 text-right">L. {{ number_format($totalInteres, 2) }}</td>
                    <td class="px-4 py-2 text-right">L. {{ number_format($totalRecargos, 2) }}</td>
                    <td class="px-4 py-2 text-right">L. {{ number_format($totalMora, 2) }}</td>
                    <td class="px-4 py-2 text-right">L. {{ number_format($totalTotal, 2) }}</td>
                    <td class="px-4 py-2 text-right">—</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- 🔹 Script para filtrar cuotas en la tabla --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filtro = document.getElementById('filtroEstado');
    filtro.addEventListener('change', async () => {
        const estado = filtro.value;
        const response = await fetch(`{{ url("/prestamos/{$prestamo->id}/plan") }}?estado=${estado}`);
        const html = await response.text();
        document.getElementById('planContainer').innerHTML = html;
    });
});
</script>