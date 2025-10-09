@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-praga mb-6 text-center">🧮 Distribuir pago recibido</h2>

        <p class="text-center mb-6 text-gray-700">
        Cliente: <strong>{{ $prestamo->cliente->nombre_completo }}</strong>
    </p>

    <!-- Monto recibido -->
    <div class="mb-4 text-center">
        <p class="text-lg font-bold">Monto recibido: <span class="text-praga">L. {{ number_format($monto, 2) }}</span></p>
        <p class="text-sm text-gray-600">Método: {{ $metodo_pago }} | Observaciones: {{ $observaciones ?? '—' }}</p>
    </div>

<!-- Botones Salir y Atrás -->
<div class="flex justify-start mb-4 gap-2">
    <!-- Salir -->
    <a href="{{ route('pagos.index') }}"
       class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow text-sm">
        ❌ Salir
    </a>

    <!-- Atrás -->
    <a href="{{ route('pagos.create', $prestamo->id) }}"
       class="inline-flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow text-sm">
        ⬅️ Atrás
    </a>
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
    <form action="{{ route('pagos.guardar', $prestamo->id) }}" method="POST" id="formDistribucion">
        @csrf
        <input type="hidden" name="monto_total" value="{{ $monto }}">
        <input type="hidden" name="metodo_pago" value="{{ $metodo_pago }}">
        <input type="hidden" name="observaciones" value="{{ $observaciones }}">
        <input type="hidden" name="fecha_pago" value="{{ $fecha_pago }}">

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
    const tabla = document.querySelector('#tablaDistribucion tbody');
    const form = document.querySelector('#formDistribucion');

    // Seleccionar cuotas
    document.querySelectorAll('.cuota-check').forEach(check => {
        check.addEventListener('change', function () {
            const nro = this.dataset.nro;
            const capitalOriginal = parseFloat(this.dataset.capital);
            const interesOriginal = parseFloat(this.dataset.interes);

            if (this.checked) {
                // Crear fila con valores iniciales
                const row = document.createElement('tr');
                row.id = 'cuota-' + nro;
                row.innerHTML = `
                    <td class="px-3 py-2 text-center">${nro}<input type="hidden" name="cuotas[${nro}][nro]" value="${nro}"></td>
                    <td class="px-3 py-2 text-center">${this.dataset.vence}</td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="cuotas[${nro}][capital]" value="${capitalOriginal.toFixed(2)}" class="w-full border rounded px-2 py-1 campo-distribucion"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="cuotas[${nro}][interes]" value="${interesOriginal.toFixed(2)}" class="w-full border rounded px-2 py-1 campo-distribucion"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" name="cuotas[${nro}][recargo]" value="0.00" class="w-full border rounded px-2 py-1 campo-distribucion"></td>
                    <td class="px-3 py-2 font-bold text-right">L. <span class="total-cuota">${(capitalOriginal + interesOriginal).toFixed(2)}</span></td>
                    <td class="px-3 py-2 text-center"><button type="button" class="btn-eliminar text-red-600 hover:text-red-800 font-bold" data-nro="${nro}">✖</button></td>
                `;
                tabla.appendChild(row);
                activarEventosFila(row);
                
                // 🔹 Llamar a actualizarTotales para redistribuir excedentes correctamente
                actualizarTotales();
            } else {
                eliminarCuota(nro);
            }
        });
    });

    // Eliminar cuota
    tabla.addEventListener('click', function(e){
        if(e.target.classList.contains('btn-eliminar')){
            const nro = e.target.dataset.nro;
            eliminarCuota(nro);
        }
    });

    function eliminarCuota(nro){
        const fila = document.getElementById('cuota-' + nro);
        if(fila) fila.remove();

        const check = document.querySelector(`.cuota-check[data-nro="${nro}"]`);
        if(check) check.checked = false;

        actualizarTotales();
    }

    // Actualizar totales y redistribuir excedente
function actualizarTotales() {
    let totalCapital = 0, totalInteres = 0, totalRecargo = 0;
    let montoRestante = montoTotal;

    document.querySelectorAll('#tablaDistribucion tbody tr').forEach(row => {
        const nro = row.querySelector('input[name*="[nro]"]').value;
        const capitalInput = row.querySelector('input[name*="[capital]"]');
        const interesInput = row.querySelector('input[name*="[interes]"]');
        const recargoInput = row.querySelector('input[name*="[recargo]"]');

        const planCapital = parseFloat(document.querySelector(`.cuota-check[data-nro="${nro}"]`).dataset.capital);
        const planInteres = parseFloat(document.querySelector(`.cuota-check[data-nro="${nro}"]`).dataset.interes);
        const vence = new Date(document.querySelector(`.cuota-check[data-nro="${nro}"]`).dataset.vence);
        const vencida = vence < new Date();

        const recargo = parseFloat(recargoInput.value) || 0;

        // Si no hay valor asignado por el usuario, calculamos automático
        if (!row.dataset.asignado) {
            let capitalAsignar = 0;
            let interesAsignar = 0;

            if (montoRestante > 0) {
                if (vencida) {
                    interesAsignar = Math.min(planInteres, montoRestante);
                    montoRestante -= interesAsignar;

                    if (montoRestante > 0) {
                        capitalAsignar = Math.min(planCapital, montoRestante);
                        montoRestante -= capitalAsignar;
                    }
                } else {
                    capitalAsignar = Math.min(planCapital, montoRestante);
                    montoRestante -= capitalAsignar;

                    if (montoRestante > 0) {
                        interesAsignar = Math.min(planInteres, montoRestante);
                        montoRestante -= interesAsignar;
                    }
                }
            }

            capitalInput.value = capitalAsignar.toFixed(2);
            interesInput.value = interesAsignar.toFixed(2);
            row.dataset.asignado = true; // marcar como calculado
        } else {
            // Si el usuario ya editó, restamos lo que puso del monto restante
            montoRestante -= parseFloat(capitalInput.value) + parseFloat(interesInput.value);
        }

        // Actualizar total por fila
        const totalFila = parseFloat(capitalInput.value) + parseFloat(interesInput.value) + recargo;
        row.querySelector('.total-cuota').textContent = totalFila.toFixed(2);

        // Acumular totales
        totalCapital += parseFloat(capitalInput.value);
        totalInteres += parseFloat(interesInput.value);
        totalRecargo += recargo;
    });

    // Mostrar totales
    document.getElementById('totalCapital').textContent = totalCapital.toFixed(2);
    document.getElementById('totalInteres').textContent = totalInteres.toFixed(2);
    document.getElementById('totalRecargo').textContent = totalRecargo.toFixed(2);
    document.getElementById('totalGeneral').textContent = (totalCapital + totalInteres + totalRecargo).toFixed(2);
}

function activarEventosFila(row){
    row.querySelectorAll('input.campo-distribucion').forEach(input => {
        input.addEventListener('input', actualizarTotales);
    });
}

    // Validación final al presionar Guardar
    form.addEventListener('submit', function(e){
        const totalAplicado = parseFloat(document.getElementById('totalGeneral').textContent);
        if(totalAplicado > montoTotal){
            e.preventDefault();
            mostrarAlerta("La cantidad excede el monto recibido");
        } else if(totalAplicado < montoTotal){
            e.preventDefault();
            mostrarAlerta("Falta completar el pago");
        }
    });

    function mostrarAlerta(mensaje){
        const alertaExistente = document.getElementById('alertaPago');
        if(alertaExistente) alertaExistente.remove();

        const alerta = document.createElement('div');
        alerta.id = "alertaPago";
        alerta.className = "fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-600 text-white px-6 py-4 rounded shadow-lg text-center text-lg z-50 animate-bounce";
        alerta.innerHTML = `<strong>⚠️ ${mensaje}</strong>`;
        document.body.appendChild(alerta);

        setTimeout(() => alerta.remove(), 3000);
    }
});
</script>
@endsection