@extends('layouts.app')

@section('content')
<div class="p-4">
    {{-- Título y datos del préstamo --}}
    <h1 class="text-xl font-bold mb-6 text-center">
        Recibos — {{ $prestamo->cliente->nombre_completo }}
    </h1>
    <p class="text-center mb-4">
        <strong>Préstamo #:</strong> {{ $prestamo->id }} — 
        <strong>Monto:</strong> L. {{ number_format($prestamo->valor_prestamo, 2) }}
    </p>

    {{-- Botón volver al historial --}}
    <div class="mb-3 text-right">
        <a href="{{ route('pagos.historial', $prestamo->id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm inline-flex items-center gap-1">
            ⬅ <span>Volver al historial</span>
        </a>
    </div>

    {{-- Tabla de recibos --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800 border">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="px-3 py-2 border">Fecha</th>
                    <th class="px-3 py-2 border text-right">Monto total</th>
                    <th class="px-3 py-2 border">Observaciones</th>
                    <th class="px-3 py-2 border text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recibos as $recibo)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 border">{{ $recibo->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 border text-right">L. {{ number_format($recibo->monto_total, 2) }}</td>
                        <td class="px-3 py-2 border">{{ $recibo->observaciones }}</td>
                        <td class="px-3 py-2 border text-center">
                            <a href="{{ route('recibos.pdf', $recibo->id_recibo) }}"
   class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow text-sm inline-flex items-center gap-1">
    📄 <span>Generar recibo</span>
</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-500">No hay recibos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection