@extends('layouts.app')

@php
    $titulo = 'Nuevo Refinanciamiento';
@endphp

@section('content')

<style>
.select2-container {
    width: 100% !important;
}

.select2-selection {
    height: 2.5rem !important;
    padding: 0.5rem 0.75rem !important;
    font-size: 1rem !important;
}
</style>

<div class="p-4 max-w-5xl mx-auto bg-white rounded shadow">

    <form action="{{ route('refinanciamientos.store') }}" method="POST" class="space-y-8">
        @csrf

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

<!-- Fecha de inicio -->
<div class="mb-4">
    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de inicio del refinanciamiento</label>
    <input type="date" name="fecha_inicio" id="fecha_inicio"
           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
           value="{{ old('fecha_inicio', \Carbon\Carbon::now()->format('Y-m-d')) }}">
</div>

<!-- Cliente (precargado) -->
<div class="mb-4">
    <label class="block font-medium mb-1">Cliente</label>
    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" 
           value="{{ $prestamo->cliente->nombre_completo }}" disabled>
    <input type="hidden" name="cliente_id" value="{{ $prestamo->cliente->id_cliente }}">
</div>

<!-- Capital e intereses pendientes -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
    <div>
        <label class="block font-medium mb-1">Capital pendiente</label>
        <input type="number" step="0.01" id="capital_pendiente" name="capital_pendiente" 
               class="w-full border rounded px-3 py-2" 
               value="{{ number_format($capitalPendiente,2,'.','') }}" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Intereses pendientes</label>
        <input type="number" step="0.01" id="interes_pendiente" name="interes_pendiente" 
               class="w-full border rounded px-3 py-2" 
               value="{{ number_format($interesAlDia,2,'.','') }}" required>
    </div>
</div>

<!-- Configuración del refinanciamiento -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
    <div>
        <label class="block font-medium mb-1">Tipo de préstamo</label>
        <select name="tipo_prestamo" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Seleccione --</option>
            <option value="Fiduciario Simple">Fiduciario Simple</option>
            <option value="Fiduciario Compuesto">Fiduciario Compuesto</option>
            <option value="Prendario Simple">Prendario Simple</option>
            <option value="Prendario Compuesto">Prendario Compuesto</option>
        </select>
    </div>
    <div>
        <label class="block font-medium mb-1">Tipo de interés</label>
        <select name="tipo_interes" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Seleccione --</option>
            <option value="Interés Simple">Interés Simple</option>
            <option value="Interés Compuesto">Interés Compuesto</option>
        </select>
    </div>
        <div>
        <label class="block font-medium mb-1">Nuevo monto</label>
        <input type="number" step="0.01" id="nuevo_monto" name="monto_adicional" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Porcentaje interés (%)</label>
        <input type="number" step="0.01" id="porcentaje_interes" name="porcentaje_interes" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Plazo (meses)</label>
        <input type="number" id="plazo" name="plazo" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block font-medium mb-1">Periodo</label>
        <select id="periodo" name="periodo" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Seleccione --</option>
            <option value="Mensual">Mensual</option>
            <option value="Quincenal">Quincenal</option>
            <option value="Semanal">Semanal</option>
        </select>
    </div>
</div>

        <!-- Resultado de monto a entregar -->
        <div class="mt-6">
            <label class="block font-medium mb-1">Monto a entregar al cliente</label>
            <input type="text" id="monto_entregar" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button" id="btn-calcular" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                Calcular refinanciamiento
            </button>
            <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                Guardar refinanciamiento
            </button>
        </div>
    </form>

    <!-- Resultado cálculo -->
    <div id="resultado" class="mt-6 hidden bg-gray-50 p-4 rounded shadow">
        <h3 class="font-semibold mb-3">INVERSIONES PRAGA - DETALLE DEL REFINANCIAMIENTO</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-700 mb-4">
            <div><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</div>
            <div><strong>Identidad:</strong> {{ $prestamo->cliente->identidad }}</div>
            <div><strong>Fecha creación:</strong> <span id="fecha_inicio_display"></span></div>
            <div><strong>Monto:</strong> L. <span id="nuevo_monto_display"></span></div>
            <div><strong>Interés:</strong> <span id="porcentaje_interes_display"></span>%</div>
            <div><strong>Plazo:</strong> <span id="plazo_display"></span> meses</div>
        </div>

        <!-- Totales en línea -->
        <div class="flex flex-wrap gap-6 text-sm font-semibold">
            <div>Total Capital: L. <span id="total_capital"></span></div>
            <div>Total Intereses: L. <span id="total_intereses"></span></div>
            <div>Cuota: L. <span id="cuota"></span></div>
            <div>Total a pagar: L. <span id="total"></span></div>
            <div>Monto a entregar: L. <span id="monto_entregar_display"></span></div>
        </div>
    </div>

    <!-- Tabla de cuotas simuladas -->
    <div id="tabla-cuotas" class="mt-6"></div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.getElementById('btn-calcular').addEventListener('click', function () {
    const capitalPendiente = parseFloat(document.getElementById('capital_pendiente').value) || 0;
    const interesPendiente = parseFloat(document.getElementById('interes_pendiente').value) || 0;
    const nuevoMonto = parseFloat(document.getElementById('nuevo_monto').value) || 0;
    const porcentajeInteres = parseFloat(document.getElementById('porcentaje_interes').value) / 100 || 0;
    const plazoMeses = parseInt(document.getElementById('plazo').value) || 0;
    const periodo = document.getElementById('periodo').value;

    if (nuevoMonto <= 0 || porcentajeInteres <= 0 || plazoMeses <= 0 || !periodo) {
        alert('Por favor complete todos los campos correctamente.');
        return;
    }

    // 🔹 Cálculo del monto a entregar al cliente con 1% de descuento
    const montoBase = nuevoMonto - (capitalPendiente + interesPendiente);
    const montoEntregar = montoBase - (montoBase * 0.01);
    document.getElementById('monto_entregar').value = 'L. ' + montoEntregar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // 🔹 Nuevo capital total (para el plan de pago)
    const nuevoCapital = nuevoMonto;

    let pagosPorMes = 1;
    if (periodo === 'Quincenal') pagosPorMes = 2;
    else if (periodo === 'Semanal') pagosPorMes = 4;

    const cuotas = plazoMeses * pagosPorMes;
    const capitalFijo = nuevoCapital / cuotas;
    const tasaPorPeriodo = porcentajeInteres / pagosPorMes;
    const interesPorCuota = nuevoCapital * tasaPorPeriodo;
    const cuotaTotal = capitalFijo + interesPorCuota;
    const totalIntereses = interesPorCuota * cuotas;
    const totalPagar = nuevoCapital + totalIntereses;

    // 🔹 Actualizar encabezado y totales en línea con comas
    document.getElementById('fecha_inicio_display').textContent = document.getElementById('fecha_inicio').value;
    document.getElementById('nuevo_monto_display').textContent = nuevoCapital.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('porcentaje_interes_display').textContent = (porcentajeInteres*100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('plazo_display').textContent = plazoMeses.toLocaleString('en-US');

    document.getElementById('total_capital').textContent = nuevoCapital.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('total_intereses').textContent = totalIntereses.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('cuota').textContent = cuotaTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('total').textContent = totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('monto_entregar_display').textContent = montoEntregar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('resultado').classList.remove('hidden');

// 🔹 Crear tabla de cuotas simuladas
let html = `
    <h3 class="font-semibold mb-3">Plan de pago simulado</h3>
    <table class="w-full text-sm border rounded overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-2 py-1">#</th>
                <th class="px-2 py-1">Vence</th>
                <th class="px-2 py-1">Capital</th>
                <th class="px-2 py-1">Interés</th>
                <th class="px-2 py-1">Total</th>
                <th class="px-2 py-1">Estado</th>
            </tr>
        </thead>
        <tbody>
`;

for (let i = 1; i <= cuotas; i++) {
    let fecha = new Date(document.getElementById('fecha_inicio').value);

    if (periodo === 'Mensual') fecha.setMonth(fecha.getMonth() + i - 1);
    else if (periodo === 'Quincenal') fecha.setDate(fecha.getDate() + (i - 1) * 15);
    else if (periodo === 'Semanal') fecha.setDate(fecha.getDate() + (i - 1) * 7);

    // 🔹 Formatear fecha a DD/MM/YYYY
    let dia = fecha.getDate().toString().padStart(2, '0');
    let mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
    let anio = fecha.getFullYear();
    let fechaFormateada = `${dia}/${mes}/${anio}`;

    html += `
        <tr class="border-t">
            <td class="text-center px-2 py-1">${i}</td>
            <td class="px-2 py-1">${fechaFormateada}</td>
            <td class="px-2 py-1">L. ${capitalFijo.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td class="px-2 py-1">L. ${interesPorCuota.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td class="px-2 py-1 font-bold">L. ${cuotaTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td class="px-2 py-1">Pendiente</td>
        </tr>
    `;
}

html += '</tbody></table>';
document.getElementById('tabla-cuotas').innerHTML = html;
});
</script>
@endsection