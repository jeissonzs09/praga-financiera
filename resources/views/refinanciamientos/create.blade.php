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

        <!-- Saldo pendiente e intereses al día -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block font-medium mb-1">Capital pendiente</label>
                <input type="number" step="0.01" name="capital_pendiente" 
                       class="w-full border rounded px-3 py-2 bg-gray-100" 
                       value="{{ number_format($capitalPendiente,2,'.','') }}" disabled>
            </div>
            <div>
                <label class="block font-medium mb-1">Intereses al día</label>
                <input type="number" step="0.01" name="interes_al_dia" 
                       class="w-full border rounded px-3 py-2 bg-gray-100" 
                       value="{{ number_format($interesAlDia,2,'.','') }}" disabled>
            </div>
        </div>

        <!-- Monto adicional y configuración del refinanciamiento -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium mb-1">Monto adicional</label>
                <input type="number" step="0.01" name="monto_adicional" class="w-full border rounded px-3 py-2" required>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.getElementById('btn-calcular').addEventListener('click', function () {
    const capitalPendiente = parseFloat('{{ $capitalPendiente }}');
    const interesAlDia = parseFloat('{{ $interesAlDia }}');
    const montoAdicional = parseFloat(document.querySelector('[name="monto_adicional"]').value);
    const porcentajeInteres = parseFloat(document.querySelector('[name="porcentaje_interes"]').value) / 100;
    const plazoMeses = parseInt(document.querySelector('[name="plazo"]').value);
    const periodo = document.querySelector('[name="periodo"]').value;

    if (isNaN(montoAdicional) || isNaN(porcentajeInteres) || isNaN(plazoMeses) || !periodo) {
        alert('Por favor complete todos los campos para calcular.');
        return;
    }

    const nuevoCapital = capitalPendiente + interesAlDia + montoAdicional;

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

    document.getElementById('cuota').textContent = 'L. ' + cuotaTotal.toFixed(2);
    document.getElementById('total').textContent = 'L. ' + totalPagar.toFixed(2);
    document.getElementById('intereses').textContent = 'L. ' + totalIntereses.toFixed(2);
    document.getElementById('resultado').classList.remove('hidden');

    // Crear tabla de cuotas simuladas
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

    for(let i=1;i<=cuotas;i++){
        let fecha = new Date(document.getElementById('fecha_inicio').value);
        if(periodo==='Mensual') fecha.setMonth(fecha.getMonth()+i-1);
        else if(periodo==='Quincenal') fecha.setDate(fecha.getDate() + (i-1)*15);
        else if(periodo==='Semanal') fecha.setDate(fecha.getDate() + (i-1)*7);

        html += `
            <tr class="border-t">
                <td class="text-center px-2 py-1">${i}</td>
                <td class="px-2 py-1">${fecha.toISOString().slice(0,10)}</td>
                <td class="px-2 py-1">L. ${capitalFijo.toFixed(2)}</td>
                <td class="px-2 py-1">L. ${interesPorCuota.toFixed(2)}</td>
                <td class="px-2 py-1 font-bold">L. ${cuotaTotal.toFixed(2)}</td>
                <td class="px-2 py-1">Pendiente</td>
            </tr>
        `;
    }

    html += '</tbody></table>';
    document.getElementById('tabla-cuotas').innerHTML = html;
});
</script>
@endsection