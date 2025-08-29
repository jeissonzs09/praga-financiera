@extends('layouts.app')

@php
    $titulo = 'INVERSIONES PRAGA - DETALLE DE PRÉSTAMO ' . strtoupper($prestamo->tipo_prestamo);
@endphp

@section('content')
<div class="p-4">

    <!-- Encabezado del préstamo -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h1 class="text-xl font-bold mb-6 text-center">{{ $titulo }}</h1>
        <p class="text-center"><strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}</p>
         <p class="text-center"><strong>Identidad:</strong> {{ $prestamo->cliente->identificacion ?? 'N/A' }}</p>
        <p class="text-center"><strong>N° Préstamo:</strong> {{ $prestamo->id }}</p>
        <p class="text-center"><strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
        <p class="text-center"><strong>Interés:</strong> {{ $prestamo->porcentaje_interes }}%</p>
        <p class="text-center"><strong>Plazo:</strong> {{ $prestamo->plazo }} {{ $prestamo->plazo > 1 ? 'meses' : 'mes' }}</p>
    </div>

<div class="mb-4 flex justify-end gap-2">
<a href="{{ route('pagos.plan', $prestamo->id) }}"
   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
    ← Volver al plan actual
</a>
</div>

    

    <!-- Tabla del plan -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800">
            <thead class="bg-blue-900 text-white text-sm uppercase">
                <tr>
                    <th class="px-4 py-3">Cuota</th>
                    <th class="px-4 py-3">Vence</th>
                    <th class="px-4 py-3">Capital</th>
                    <th class="px-4 py-3">Interés</th>
                    <th class="px-4 py-3">Recargos</th>
                    <th class="px-4 py-3">Mora</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Saldo Capital</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
    @foreach($cuotas as $cuota)
        <tr
            @class([
                'bg-green-100 text-green-800' => $cuota['estado'] === 'Pagada',
                'bg-yellow-100 text-yellow-800' => $cuota['estado'] === 'Parcial',
                'bg-red-100 text-red-800' => $cuota['estado'] === 'Pendiente' && \Carbon\Carbon::createFromFormat('d/m/Y', $cuota['vence'])->isPast(),
    ])
            ])
        >
            <td class="px-4 py-2 text-center">{{ $cuota['nro'] }}</td>
            <td class="px-4 py-2 text-center">{{ $cuota['vence'] }}</td>
            <td class="px-4 py-2 text-right">
                L. {{ $cuota['estado'] === 'Pagada' ? '0.00' : number_format($cuota['capital'], 2) }}
            </td>
            <td class="px-4 py-2 text-right">
                L. {{ $cuota['estado'] === 'Pagada' ? '0.00' : number_format($cuota['interes'], 2) }}
            </td>
            <td class="px-4 py-2 text-right">L. {{ number_format($cuota['recargos'], 2) }}</td>
            <td class="px-4 py-2 text-right">L. {{ number_format($cuota['mora'], 2) }}</td>
            <td class="px-4 py-2 text-right font-bold">
                L. {{ $cuota['estado'] === 'Pagada' ? '0.00' : number_format($cuota['total'], 2) }}
            </td>
            <td class="px-4 py-2 text-right">
                L. {{ number_format($cuota['saldo'], 2) }}
            </td>
        </tr>
    @endforeach
</tbody>

            <tfoot class="bg-gray-50 text-sm font-semibold">
    <tr>
        <td class="px-4 py-2 text-right" colspan="2">Totales</td>
        <td class="px-4 py-2 text-right">L. {{ number_format($totales['capital'] ?? 0, 2) }}</td>
        <td class="px-4 py-2 text-right">L. {{ number_format($totales['interes'] ?? 0, 2) }}</td>
        <td class="px-4 py-2 text-right">L. 0.00</td>
        <td class="px-4 py-2 text-right">L. 0.00</td>
        <td class="px-4 py-2 text-right">L. {{ number_format($totales['total'] ?? 0, 2) }}</td>
        <td class="px-4 py-2 text-right">—</td>
    </tr>
</tfoot>
        </table>
    </div>
</div>
@endsection

<div id="modalPago" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Registrar pago</h2>
        <form action="{{ route('pagos.store', $prestamo->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium">Monto</label>
                <input type="number" step="0.01" name="monto" required
                       class="border rounded px-3 py-2 w-full">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Observaciones</label>
                <textarea name="observaciones" class="border rounded px-3 py-2 w-full"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="cerrarModalPago()" class="px-4 py-2 rounded border">
                    Cancelar
                </button>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalPago() {
    document.getElementById('modalPago').classList.remove('hidden');
    document.getElementById('modalPago').classList.add('flex');
}

function cerrarModalPago() {
    document.getElementById('modalPago').classList.add('hidden');
    document.getElementById('modalPago').classList.remove('flex');
}
</script>