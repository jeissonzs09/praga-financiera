@extends('layouts.app')

@section('content')
<div class="p-4">

@if(session('success'))
    <div 
        x-data="{ show: true }" 
        x-init="setTimeout(() => show = false, 1500)" 
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

<h1 class="text-xl font-bold mb-6 text-center">
    Recibos — {{ $prestamo->cliente->nombre_completo }}
</h1>
<p class="text-center mb-4">
    <strong>Préstamo #:</strong> {{ $prestamo->id }} — 
    <strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}
</p>

<div class="mb-3 text-right">
    <a href="{{ route('pagos.historial', $prestamo->id) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm inline-flex items-center gap-1">
        ⬅ <span>Volver al historial</span>
    </a>
</div>

{{-- Tabla de recibos con capital e interés --}}
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm text-gray-800 border">
        <thead class="bg-blue-900 text-white">
            <tr>
                <th class="px-3 py-2 border">Fecha</th>
                <th class="px-3 py-2 border text-right">Capital</th>
                <th class="px-3 py-2 border text-right">Interés</th>
                <th class="px-3 py-2 border text-right">Monto total</th>
                <th class="px-3 py-2 border">Observaciones</th>
                <th class="px-3 py-2 border text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recibos as $recibo)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 border">{{ \Carbon\Carbon::parse($recibo->fecha_pago)->format('d/m/Y') }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($recibo->capital, 2) }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($recibo->interes, 2) }}</td>
                    <td class="px-3 py-2 border text-right font-bold">L. {{ number_format($recibo->monto, 2) }}</td>
                    <td class="px-3 py-2 border">{{ $recibo->observaciones }}</td>
                    <td class="px-3 py-2 border text-center">
                        <a href="{{ route('recibos.pdf', $recibo->id) }}"
                           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow text-sm inline-flex items-center gap-1">
                            📄 <span>Generar recibo</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-gray-500">No hay recibos registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection