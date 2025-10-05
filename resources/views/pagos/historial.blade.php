@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-6 text-center">
        Historial de pagos {{ $prestamo->cliente->nombre_completo }}
    </h1>
    <p class="text-center mb-4">
        <strong>Préstamo #:</strong> {{ $prestamo->id }} — 
        <strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}
    </p>

    {{-- Encabezado con botón a la derecha --}}
    <div class="flex justify-between items-center mb-3">
        <h4 class="text-lg font-semibold">Historial de pagos — {{ $prestamo->cliente->nombre }}</h4>
        <a href="{{ route('recibos.index', ['prestamo' => $prestamo->id]) }}"
           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow text-sm flex items-center gap-1">
            📄 <span>Ver recibos</span>
        </a>
    </div>

<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm text-gray-800 border">
        <thead class="bg-blue-900 text-white">
            <tr>
                <th class="px-3 py-2 border">Fecha</th>
                <th class="px-3 py-2 border text-center">Cuota</th>
                <th class="px-3 py-2 border text-right">Capital</th>
                <th class="px-3 py-2 border text-right">Interés</th>
                <th class="px-3 py-2 border text-right">Total</th>
                <th class="px-3 py-2 border">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $detalle)
                <tr class="border-b hover:bg-gray-50">
                    {{-- Usar la fecha_pago, si existe --}}
                    <td class="px-3 py-2 border">
                        {{ \Carbon\Carbon::parse($detalle->fecha_pago)->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-2 border text-center">{{ $detalle->cuota_numero }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($detalle->capital, 2) }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($detalle->interes, 2) }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($detalle->total, 2) }}</td>
                    <td class="px-3 py-2 border">{{ $detalle->observaciones ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-gray-500">No hay pagos registrados</td>
                </tr>
            @endforelse
        </tbody>

        {{-- Totales --}}
        @if($detalles->count() > 0)
            <tfoot class="bg-gray-100 font-semibold">
                <tr>
                    <td colspan="2" class="px-3 py-2 border text-right">Totales:</td>
                    <td class="px-3 py-2 border text-right">
                        L. {{ number_format($detalles->sum('capital'), 2) }}
                    </td>
                    <td class="px-3 py-2 border text-right">
                        L. {{ number_format($detalles->sum('interes'), 2) }}
                    </td>
                    <td class="px-3 py-2 border text-right">
                        L. {{ number_format($detalles->sum('total'), 2) }}
                    </td>
                    <td class="px-3 py-2 border"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

    {{-- Botón volver al plan --}}
    <div class="mt-4 text-right">
        <a href="{{ route('pagos.plan', $prestamo->id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm">
            ⬅ Volver al plan
        </a>
    </div>
</div>
@endsection