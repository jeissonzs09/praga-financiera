@extends('layouts.app')

@section('content')
<h1>Pagos del préstamo de {{ $prestamo->cliente->nombre_completo }}</h1>

<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm text-gray-800">
        <thead class="bg-blue-900 text-white text-sm uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Fecha</th>
                <th class="px-4 py-3 text-left">Monto total</th>
                <th class="px-4 py-3 text-left">Observaciones</th>
                <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($recibos as $recibo)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2">{{ $recibo->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">L. {{ number_format($recibo->monto_total, 2) }}</td>
                    <td class="px-4 py-2">{{ $recibo->observaciones ?: '—' }}</td>
                    <td class="px-4 py-2 text-center">
                        <form action="{{ route('pagos.eliminarRecibo', $recibo->id_recibo) }}" method="POST" class="inline-block eliminar-form">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500 italic">
                        No hay pagos registrados para este préstamo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

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