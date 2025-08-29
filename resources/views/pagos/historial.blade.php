@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-6 text-center">
        Historial de pagos — {{ $prestamo->cliente->nombre_completo }}
    </h1>
    <p class="text-center mb-4">
        <strong>Préstamo #:</strong> {{ $prestamo->id }} — 
        <strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}
    </p>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800 border">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="px-3 py-2 border">Fecha</th>
                    <th class="px-3 py-2 border">Cuota</th>
                    <th class="px-3 py-2 border text-right">Monto</th>
                    <th class="px-3 py-2 border">Observaciones</th>
                    <th class="px-3 py-2 border text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamo->pagos as $pago)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 border">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 border text-center">{{ $pago->cuota_numero ?? '-' }}</td>
                        <td class="px-3 py-2 border text-right">L. {{ number_format($pago->monto, 2) }}</td>
                        <td class="px-3 py-2 border">{{ $pago->observaciones }}</td>
                        <td class="px-3 py-2 border text-center">
    @if(!empty($pago->id_pago))
        <a href="{{ route('pagos.recibo', ['pago' => $pago->id_pago]) }}"
           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow text-sm"
           target="_blank">
            Generar recibo
        </a>
    @else
        —
    @endif
</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">No hay pagos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-right">
        <a href="{{ route('pagos.plan', $prestamo->id) }}"
           class="px-4 py-2 border rounded hover:bg-gray-100">
            Volver al plan
        </a>
    </div>
</div>
@endsection