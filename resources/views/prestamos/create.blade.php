@extends('layouts.app')

@php
    $titulo = 'Nuevo Préstamo';
@endphp

@section('content')
<div class="p-4 max-w-5xl mx-auto bg-white rounded shadow">

    <form action="{{ route('prestamos.store') }}" method="POST" class="space-y-8">
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

        @csrf

        <!-- Cliente -->
        <div>
            <label class="block font-medium mb-1">Cliente</label>
            <select name="cliente_id" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Seleccione un cliente --</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id_cliente }}">{{ $cliente->nombre_completo }}</option>
                @endforeach
            </select>
        </div>

        <!-- Configuración -->
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
</div>

<script>
document.getElementById('btn-calcular').addEventListener('click', function () {
    const monto = parseFloat(document.querySelector('[name="valor_prestamo"]').value);
    const tasaMensual = parseFloat(document.querySelector('[name="porcentaje_interes"]').value) / 100;
    const plazoMeses = parseInt(document.querySelector('[name="plazo"]').value);
    const periodo = document.querySelector('[name="periodo"]').value;

    if (isNaN(monto) || isNaN(tasaMensual) || isNaN(plazoMeses) || !periodo) {
        alert('Por favor complete Monto, Interés, Plazo y Periodo para calcular.');
        return;
    }

    // 1) Pagos por mes según periodo seleccionado
    let pagosPorMes = 1;
    if (periodo === 'Quincenal') pagosPorMes = 2;
    else if (periodo === 'Semanal') pagosPorMes = 4;

    // 2) Número total de cuotas
    const cuotas = plazoMeses * pagosPorMes;

    // 3) Capital fijo por cuota
    const capitalFijo = monto / cuotas;

    // 4) Tasa del periodo (mensual dividido entre pagos por mes)
    const tasaPorPeriodo = tasaMensual / pagosPorMes;

    // 5) Interés fijo por cuota (sobre monto inicial)
    const interesPorCuota = monto * tasaPorPeriodo;

    // 6) Cuota total
    const cuotaTotal = capitalFijo + interesPorCuota;

    // 7) Totales
    const totalIntereses = interesPorCuota * cuotas;
    const totalPagar = monto + totalIntereses;

    // 8) Mostrar resultados
    document.getElementById('cuota').textContent = 'L. ' + cuotaTotal.toFixed(2);
    document.getElementById('total').textContent = 'L. ' + totalPagar.toFixed(2);
    document.getElementById('intereses').textContent = 'L. ' + totalIntereses.toFixed(2);
    document.getElementById('resultado').classList.remove('hidden');
});
</script>
@endsection