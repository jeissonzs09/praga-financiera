<div>
    {{-- 🔹 Encabezado compacto en 3 columnas --}}
    <div class="mb-4 p-4 bg-gray-100 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-center mb-3">INVERSIONES PRAGA - DETALLE DE PRÉSTAMO</h3>

        <div class="grid grid-cols-3 gap-4 text-sm text-gray-800">
            {{-- Columna 1 --}}
            <div>
                <p><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</p>
                <p><strong>Identidad:</strong> {{ $prestamo->cliente->identificacion ?? 'N/A' }}</p>
                <p><strong>Fecha creación:</strong> {{ $prestamo->created_at->format('d/m/Y') }}</p>
            </div>

            {{-- Columna 2 --}}
            <div>
                <p><strong>N° Préstamo:</strong> {{ $prestamo->id }}</p>
                <p><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
                <p><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</p>
            </div>

            {{-- Columna 3 --}}
            <div>
                <p><strong>Plazo:</strong> {{ $prestamo->plazo }} meses</p>
                <p><strong>Estado:</strong> {{ ucfirst($prestamo->estado ?? 'Activo') }}</p>
            </div>
        </div>
    </div>

@if($prestamo->estado === 'Activo')
    <form method="POST" action="{{ route('prestamos.inactivar', $prestamo->id) }}"
          onsubmit="return confirm('¿Estás seguro de que deseas marcar este préstamo como inactivo? Esta acción lo moverá al historial y no aparecerá en la vista principal.');">
        @csrf
        @method('PATCH')
        <button type="submit"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow">
            🛑 Marcar como Inactivo
        </button>
    </form>
@else
    <form method="POST" action="{{ route('prestamos.activar', $prestamo->id) }}"
          onsubmit="return confirm('¿Deseas reactivar este préstamo? Volverá a aparecer en la vista principal.');">
        @csrf
        @method('PATCH')
        <button type="submit"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow">
            🔄 Reactivar Préstamo
        </button>
    </form>
@endif

    {{-- 🔹 Filtro por estado --}}
    <div class="mb-2 flex justify-end items-center gap-2">
        <label for="filtroEstado" class="text-sm font-semibold">Mostrar:</label>
        <select id="filtroEstado"
                class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="Todas" {{ ($estadoSeleccionado ?? '') === 'Todas' ? 'selected' : '' }}>Todas</option>
            <option value="Pendientes" {{ ($estadoSeleccionado ?? '') === 'Pendientes' ? 'selected' : '' }}>Pendientes</option>
            <option value="Pagadas" {{ ($estadoSeleccionado ?? '') === 'Pagadas' ? 'selected' : '' }}>Pagadas</option>
        </select>
    </div>

{{-- 🔹 Botones de descarga y visualización --}}
<div class="mb-4 flex justify-end gap-2">
    <a href="{{ route('pagos.plan.original.pdf', $prestamo->id) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
        🖨️ Descargar Plan Original
    </a>

    {{-- 🔹 Botón para mostrar el Estado de Cuenta --}}
<button 
    onclick="window.open('{{ route('pagos.estado.cuenta.pdf', $prestamo->id) }}', '_blank');"
    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
    👁️ Ver Estado de Cuenta
</button>
</div>

{{-- 🔹 Contenedor del Estado de Cuenta (inicialmente oculto) --}}
<div id="estadoCuentaContainer" class="mt-6 hidden">
    <h3 class="text-lg font-semibold mb-2">Estado de Cuenta</h3>
    <iframe 
        id="iframeEstadoCuenta"
        src="" 
        width="100%" 
        height="800px" 
        style="border:1px solid #ccc;">
    </iframe>
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

                        // Valores
                        $cap = $cuota['capital'];
                        $int = $cuota['interes'];
                        if($estadoSeleccionado === 'Pagadas') {
                            $cap = $cuota['capital_original'] ?? $cap;
                            $int = $cuota['interes_original'] ?? $int;
                        }

                        $totalCuota = $cap + $int + $cuota['recargos'] + $cuota['mora'];
                        $totalCapital += $cap;
                        $totalInteres += $int;
                        $totalRecargos += $cuota['recargos'];
                        $totalMora += $cuota['mora'];
                        $totalTotal += $totalCuota;

                        // 🔹 Solo verde o rojo
// 🔹 Verde si pagada completa, rojo si vencida (incluye parciales)
$fechaVence = \Carbon\Carbon::parse($cuota['vence']);
$saldoPendiente = ($cuota['capital'] + $cuota['interes'] + $cuota['recargos'] + $cuota['mora']) - ($cuota['pagado'] ?? 0);

if ($cuota['estado'] === 'Pagada') {
    $clase = 'bg-green-100 text-green-800';
} elseif ($fechaVence->isPast() && $saldoPendiente > 0) {
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

            {{-- Totales --}}
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

{{-- 🔹 Script para filtrar cuotas --}}
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

{{-- 🔹 Script para mostrar el PDF al hacer clic --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnVerEstadoCuenta');
    const container = document.getElementById('estadoCuentaContainer');
    const iframe = document.getElementById('iframeEstadoCuenta');

    btn.addEventListener('click', () => {
        console.log('Botón clickeado'); // <- Esto debería aparecer en la consola
        iframe.src = "{{ route('pagos.estado.cuenta.pdf', $prestamo->id) }}";
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
    });
});
</script>