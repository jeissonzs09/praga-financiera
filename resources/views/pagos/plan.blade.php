@extends('layouts.app')

@php
    $titulo = 'INVERSIONES PRAGA - DETALLE DE PRÉSTAMO ' . strtoupper($prestamo->tipo_prestamo);
@endphp

@section('content')
<div class="p-4">

@if(session('success'))
    <div 
        x-data="{ show: true }" 
        x-init="setTimeout(() => show = false, 4000)" 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        class="fixed inset-0 flex items-center justify-center z-50"
    >
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-5 rounded-2xl shadow-2xl text-center text-base md:text-lg font-semibold flex items-center gap-3 max-w-md w-full">
            <span class="text-2xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

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
<a href="{{ route('pagos.create', $prestamo->id) }}"
   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
    Registrar pago
</a>

        <a href="{{ route('pagos.listar', $prestamo->id) }}"
   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow">
    Eliminar Pago
</a>


        <a href="{{ route('pagos.historial', $prestamo->id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
            Ver registro de pagos
        </a>

        <a href="{{ route('pagos.plan.original', $prestamo->id) }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow">
            Ver plan original
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
                       @php
if ($cuota['estado'] === 'Pagada' && $cuota['es_tardio'] === true){
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
<tr class="{{ $clase }}">


                    
                        <td class="px-4 py-2 text-center">{{ $cuota['nro'] }}</td>
                        <td class="px-4 py-2 text-center">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $cuota['vence'])->format('d/m/Y') }}</td>
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
