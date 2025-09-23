@extends('layouts.app')

@php
    $titulo = 'Nuevo Préstamo';
@endphp

@section('content')

<style>
.select2-container {
    width: 100% !important;
}

.select2-selection {
    height: 2.5rem !important; /* igual que tus inputs */
    padding: 0.5rem 0.75rem !important;
    font-size: 1rem !important;
}
</style>

<div class="p-4 max-w-5xl mx-auto bg-white rounded shadow">

    <form action="{{ route('prestamos.store') }}" method="POST" class="space-y-8">
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
            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de inicio del préstamo</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                   value="{{ old('fecha_inicio', \Carbon\Carbon::now()->format('Y-m-d')) }}">
        </div>

        <!-- Cliente con Select2 -->
        <div>
            <label class="block font-medium mb-1">Cliente</label>
            <select name="cliente_id" class="select2 w-full border rounded px-3 py-2" required>
                <option value="">-- Seleccione un cliente --</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id_cliente }}">{{ $cliente->nombre_completo }}</option>
                @endforeach
            </select>
        </div>

        <!-- Configuración del préstamo -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                <label class="block font-medium mb-1">Porcentaje interés (%)</label>
                <input type="number" step="0.01" name="porcentaje_interes" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Plazo (meses)</label>
                <input type="number" name="plazo" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Valor del préstamo</label>
                <input type="number" step="0.01" name="valor_prestamo" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Periodo</label>
                <select name="periodo" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Seleccione --</option>
                    <option value="Mensual">Mensual</option>
                    <option value="Quincenal">Quincenal</option>
                    <option value="Semanal">Semanal</option>
                </select>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button" id="btn-calcular" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                Calcular préstamo
            </button>
            <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                Guardar préstamo
            </button>
        </div>
    </form>

    <!-- Resultado cálculo -->
    <div id="resultado" class="mt-6 hidden bg-gray-50 p-4 rounded shadow">
        <h3 class="font-semibold mb-3">Resultado de la simulación</h3>
        <p><strong>Cuota:</strong> <span id="cuota"></span></p>
        <p><strong>Total a pagar:</strong> <span id="total"></span></p>
        <p><strong>Intereses:</strong> <span id="intereses"></span></p>
    </div>

    <!-- Tabla de cuotas simuladas -->
    <div id="tabla-cuotas" class="mt-6"></div>
</div>
@endsection

@section('scripts')
<!-- jQuery necesario para Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS y JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        placeholder: "-- Seleccione un cliente --",
        allowClear: true,
        width: 'resolve'
    });
});



// Tu código de cálculo de préstamo
document.getElementById('btn-calcular').addEventListener('click', function () {
    const monto = parseFloat(document.querySelector('[name="valor_prestamo"]').value);
    const tasaMensual = parseFloat(document.querySelector('[name="porcentaje_interes"]').value) / 100;
    const plazoMeses = parseInt(document.querySelector('[name="plazo"]').value);
    const periodo = document.querySelector('[name="periodo"]').value;

    if (isNaN(monto) || isNaN(tasaMensual) || isNaN(plazoMeses) || !periodo) {
        alert('Por favor complete Monto, Interés, Plazo y Periodo para calcular.');
        return;
    }

    let pagosPorMes = 1;
    if (periodo === 'Quincenal') pagosPorMes = 2;
    else if (periodo === 'Semanal') pagosPorMes = 4;

    const cuotas = plazoMeses * pagosPorMes;
    const capitalFijo = monto / cuotas;
    const tasaPorPeriodo = tasaMensual / pagosPorMes;
    const interesPorCuota = monto * tasaPorPeriodo;
    const cuotaTotal = capitalFijo + interesPorCuota;
    const totalIntereses = interesPorCuota * cuotas;
    const totalPagar = monto + totalIntereses;

    document.getElementById('cuota').textContent = 'L. ' + cuotaTotal.toFixed(2);
    document.getElementById('total').textContent = 'L. ' + totalPagar.toFixed(2);
    document.getElementById('intereses').textContent = 'L. ' + totalIntereses.toFixed(2);
    document.getElementById('resultado').classList.remove('hidden');

    const formData = new FormData();
    formData.append('valor_prestamo', monto);
    formData.append('porcentaje_interes', tasaMensual * 100);
    formData.append('plazo', plazoMeses);
    formData.append('periodo', periodo);
    formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("prestamos.simular") }}', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(cuotas => {
        mostrarTablaCuotas(cuotas);
    })
    .catch(err => {
        console.error('Error:', err);
        alert('No se pudo cargar el plan de pago.');
    });
});

function mostrarTablaCuotas(cuotas) {
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

    cuotas.forEach(c => {
        html += `
            <tr class="border-t">
                <td class="text-center px-2 py-1">${c.nro}</td>
                <td class="px-2 py-1">${c.vence}</td>
                <td class="px-2 py-1">L. ${c.capital.toFixed(2)}</td>
                <td class="px-2 py-1">L. ${c.interes.toFixed(2)}</td>
                <td class="px-2 py-1 font-bold">L. ${c.total.toFixed(2)}</td>
                <td class="px-2 py-1">${c.estado}</td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-cuotas').innerHTML = html;
}
</script>
@endsection