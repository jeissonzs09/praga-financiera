@extends('layouts.app')

@section('content')
<div class="p-4">

    <!-- Encabezado resaltado -->
    <div class="bg-blue-900 text-white rounded-lg shadow p-4 mb-6 text-center">
        <h1 class="text-2xl font-bold tracking-wide">
            Pagos registrados para el préstamo de {{ $prestamo->cliente->nombre_completo }}
        </h1>
        <p class="mt-1 text-sm">Préstamo N° {{ $prestamo->id }} — Monto: L. {{ number_format($prestamo->valor_prestamo, 2) }}</p>
    </div>

    <!-- Botón para regresar al plan de pago -->
    <div class="mb-4 text-right">
        <a href="{{ route('pagos.plan', $prestamo->id) }}"
           class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded shadow">
            ← Regresar al plan de pago
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
                        <td class="px-3 py-2 border">{{ $recibo->created_at->format('d/m/Y H:i') }}</td>
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