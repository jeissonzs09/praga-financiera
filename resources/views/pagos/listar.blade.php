@extends('layouts.app')

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

<!-- Encabezado resaltado -->
<div class="bg-blue-900 text-white rounded-lg shadow p-4 mb-4 text-center">
    <h1 class="text-2xl font-bold tracking-wide">
        Pagos registrados para el préstamo de {{ $prestamo->cliente->nombre_completo }}
    </h1>
    <p class="mt-1 text-sm">Préstamo N° {{ $prestamo->id }} — Monto: L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
</div>

<!-- Botón Salir alineado a la izquierda -->
<div class="flex justify-start mb-2">
    <a href="{{ route('pagos.index') }}" 
       class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow text-sm">
        ❌ Salir
    </a>
</div>

<!-- Tabla de pagos -->
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm text-gray-800 border">
        <thead class="bg-blue-900 text-white">
            <tr>
                <th class="px-3 py-2 border">Fecha</th>
                <th class="px-3 py-2 border text-right">Monto</th>
                <th class="px-3 py-2 border">Observaciones</th>
                <th class="px-3 py-2 border text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recibos as $recibo)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 border">{{ \Carbon\Carbon::parse($recibo->fecha_pago)->format('d/m/Y') }}</td>
                    <td class="px-3 py-2 border text-right">L. {{ number_format($recibo->monto, 2) }}</td>
                    <td class="px-3 py-2 border">{{ $recibo->observaciones ?: '—' }}</td>
                    <td class="px-3 py-2 border text-center">
                        <form action="{{ route('pagos.eliminarRecibo', $recibo->id) }}" method="POST" class="inline-block eliminar-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow text-sm">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-gray-500 italic">
                        No hay pagos registrados para este préstamo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Confirmación visual al eliminar -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.eliminar-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const monto = this.closest('tr').querySelector('td:nth-child(2)').innerText.trim();
            if (confirm(`¿Seguro que deseas eliminar este pago de ${monto}?`)) {
                this.submit();
            }
        });
    });
});
</script>
@endsection