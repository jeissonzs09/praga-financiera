@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-praga mb-6 text-center">🧮 Distribuir pago recibido</h2>

    <!-- Monto recibido -->
    <div class="mb-4 text-center">
        <p class="text-lg font-bold">Monto recibido: <span class="text-praga">L. {{ number_format($monto, 2) }}</span></p>
        <p class="text-sm text-gray-600">Método: {{ $metodo_pago }} | Observaciones: {{ $observaciones ?? '—' }}</p>
    </div>

    <!-- Plan de cuotas -->
    <div class="mb-6">
        <p class="font-semibold mb-2">Seleccione las cuotas que desea afectar:</p>
        <table class="min-w-full text-sm text-gray-800 border">
            <thead class="bg-blue-900 text-white uppercase">
                <tr>
                    <th class="px-3 py-2">✔</th>
                    <th class="px-3 py-2">Cuota</th>
                    <th class="px-3 py-2">Vence</th>
                    <th class="px-3 py-2">Capital</th>
                    <th class="px-3 py-2">Interés</th>
                    <th class="px-3 py-2">Total cuota</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cuotas as $cuota)
    @php
        $vencida = \Carbon\Carbon::parse($cuota['vence'])->isPast();
        if ($cuota['estado'] === 'Pagada' && ($cuota['es_tardio'] ?? false)) {
            $clase = 'bg-red-100 text-red-800';
        } elseif ($cuota['estado'] === 'Pagada') {
            $clase = 'bg-green-100 text-green-800';
        } elseif ($cuota['estado'] === 'Parcial') {
            $clase = 'bg-yellow-100 text-yellow-800';
        } elseif (in_array($cuota['estado'], ['Pendiente', 'Vencida']) && $vencida) {
            $clase = 'bg-red-100 text-red-800';
        } else {
            $clase = '';
        }
    @endphp

    <tr class="{{ $clase }}">
        <td class="px-3 py-2 text-center">
            <input type="checkbox" class="cuota-check"
                   data-nro="{{ $cuota['nro'] }}"
                   data-capital="{{ $cuota['capital'] }}"
                   data-interes="{{ $cuota['interes'] }}"
                   data-vence="{{ $cuota['vence'] }}">
        </td>
        <td class="px-3 py-2 text-center">{{ $cuota['nro'] }}</td>
        <td class="px-3 py-2 text-center">{{ \Carbon\Carbon::parse($cuota['vence'])->format('d/m/Y') }}</td>
        <td class="px-3 py-2 text-right">L. {{ number_format($cuota['capital'], 2) }}</td>
        <td class="px-3 py-2 text-right">L. {{ number_format($cuota['interes'], 2) }}</td>
        <td class="px-3 py-2 text-right font-bold">L. {{ number_format($cuota['capital'] + $cuota['interes'], 2) }}</td>
    </tr>
@endforeach

            </tbody>
        </table>
    </div>

    <!-- Tabla de distribución -->
    <form action="{{ route('pagos.guardar', $prestamo->id) }}" method="POST">
        @csrf
        <input type="hidden" name="monto_total" value="{{ $monto }}">
        <input type="hidden" name="metodo_pago" value="{{ $metodo_pago }}">
        <input type="hidden" name="observaciones" value="{{ $observaciones }}">

        <table class="min-w-full text-sm text-gray-800 border mb-4" id="tablaDistribucion">
            <thead class="bg-yellow-100 text-gray-700">
                <tr>
                    <th class="px-3 py-2">Cuota</th>
                    <th class="px-3 py-2">Vence</th>
                    <th class="px-3 py-2">Capital</th>
                    <th class="px-3 py-2">Interés</th>
                    <th class="px-3 py-2">Recargo</th>
                    <th class="px-3 py-2">Total</th>
                    <th class="px-3 py-2">❌</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <p id="alertaExcedente" class="text-sm text-red-600 hidden">⚠️ La suma excede el monto disponible.</p>

        <p id="alertaExcesoCuota" class="text-sm text-red-600 hidden">
    ⚠️ El capital o interés asignado excede el valor original de la cuota seleccionada.
</p>
        <!-- Totales finales -->
        <div class="mt-4 text-right font-semibold text-gray-700">
            <p>Total capital: L. <span id="totalCapital">0.00</span></p>
            <p>Total interés: L. <span id="totalInteres">0.00</span></p>
            <p>Total recargo: L. <span id="totalRecargo">0.00</span></p>
            <p class="text-praga text-lg mt-2">Total aplicado: L. <span id="totalGeneral">0.00</span></p>
        </div>

        <div class="text-right mt-6">
    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
        💾 Guardar distribución
    </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const montoTotal = {{ $monto }};
    let montoRestante = montoTotal;
    const tabla = document.querySelector('#tablaDistribucion tbody');
    const alerta = document.getElementById('alertaExcedente');

    // Selección de cuotas
    document.querySelectorAll('.cuota-check').forEach(check => {
        check.addEventListener('change', function () {
            const nro = this.dataset.nro;
            const capital = parseFloat(this.dataset.capital);
            const interes = parseFloat(this.dataset.interes);
            const vence = this.dataset.vence;

            if (this.checked) {
                recalcularMontoRestante();

                let capitalAsignado = Math.min(capital, montoRestante);
                let interesAsignado = Math.min(interes, montoRestante - capitalAsignado);
                let recargo = 0;
                let total = capitalAsignado + interesAsignado + recargo;

                montoRestante -= total;

                const row = document.createElement('tr');
                row.setAttribute('id', 'cuota-' + nro);
                row.innerHTML = `
                    <td class="px-3 py-2 text-center">${nro}
                        <input type="hidden" name="cuotas[${nro}][nro]" value="${nro}">
                    </td>
                    <td class="px-3 py-2 text-center">${vence}</td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" name="cuotas[${nro}][capital]" value="${capitalAsignado}"
                               class="w-full border rounded px-2 py-1 campo-distribucion">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" name="cuotas[${nro}][interes]" value="${interesAsignado}"
                               class="w-full border rounded px-2 py-1 campo-distribucion">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" name="cuotas[${nro}][recargo]" value="${recargo}"
                               class="w-full border rounded px-2 py-1 campo-distribucion">
                    </td>
                    <td class="px-3 py-2 font-bold text-right">L. <span class="total-cuota">${total.toFixed(2)}</span></td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="btn-eliminar text-red-600 hover:text-red-800 font-bold" data-nro="${nro}">✖</button>
                    </td>
                `;
                tabla.appendChild(row);
                activarEventosFila(row);
                actualizarTotales();
            } else {
                eliminarCuota(nro);
            }
        });
    });

    // Eliminar cuota manualmente
    tabla.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-eliminar')) {
            const nro = e.target.dataset.nro;
            eliminarCuota(nro);
        }
    });

    function eliminarCuota(nro) {
        const fila = document.getElementById('cuota-' + nro);
        if (fila) fila.remove();

        const check = document.querySelector(`.cuota-check[data-nro="${nro}"]`);
        if (check) check.checked = false;

        recalcularMontoRestante();
        actualizarTotales();
    }

        function recalcularMontoRestante() {
        montoRestante = montoTotal;
        document.querySelectorAll('#tablaDistribucion tbody tr').forEach(row => {
            const capital = parseFloat(row.querySelector('input[name*="[capital]"]').value) || 0;
            const interes = parseFloat(row.querySelector('input[name*="[interes]"]').value) || 0;
            const recargo = parseFloat(row.querySelector('input[name*="[recargo]"]').value) || 0;
            montoRestante -= (capital + interes + recargo);
        });
    }

    function validarDistribucion() {
        recalcularMontoRestante();
        alerta.classList.toggle('hidden', montoRestante >= 0);
    }

    function actualizarTotales() {
    let totalCapital = 0, totalInteres = 0, totalRecargo = 0;
    let excesoDetectado = false;

    document.querySelectorAll('#tablaDistribucion tbody tr').forEach(row => {
        const nro = row.querySelector('input[name*="[nro]"]').value;
        const capitalInput = row.querySelector('input[name*="[capital]"]');
        const interesInput = row.querySelector('input[name*="[interes]"]');
        const recargoInput = row.querySelector('input[name*="[recargo]"]');

        const capital = parseFloat(capitalInput.value) || 0;
        const interes = parseFloat(interesInput.value) || 0;
        const recargo = parseFloat(recargoInput.value) || 0;
        const total = capital + interes + recargo;

        // Validar exceso por cuota
        const planCapital = parseFloat(document.querySelector(`.cuota-check[data-nro="${nro}"]`).dataset.capital);
        const planInteres = parseFloat(document.querySelector(`.cuota-check[data-nro="${nro}"]`).dataset.interes);

        if (capital > planCapital || interes > planInteres) {
            excesoDetectado = true;
        }

        // Actualizar total por fila
        row.querySelector('.total-cuota').textContent = total.toFixed(2);

        // Acumular totales
        totalCapital += capital;
        totalInteres += interes;
        totalRecargo += recargo;
    });

    // Mostrar totales finales
    document.getElementById('totalCapital').textContent = totalCapital.toFixed(2);
    document.getElementById('totalInteres').textContent = totalInteres.toFixed(2);
    document.getElementById('totalRecargo').textContent = totalRecargo.toFixed(2);
    document.getElementById('totalGeneral').textContent = (totalCapital + totalInteres + totalRecargo).toFixed(2);

    // Mostrar alertas
    document.getElementById('alertaExcesoCuota').classList.toggle('hidden', !excesoDetectado);
    validarDistribucion();
}

    function activarEventosFila(row) {
        row.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                actualizarTotales();
            });
        });
    }
});
</script>
@endsection