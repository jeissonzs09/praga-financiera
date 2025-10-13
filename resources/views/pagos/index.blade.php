@extends('layouts.app')

@php
    $titulo = 'Registro de Pagos';
@endphp

@section('content')
<div class="p-4">

<h2 class="text-xl font-semibold mb-4">{{ $titulo }}</h2>

<!-- Buscador -->
<div class="mb-4 flex gap-2 items-center">
    <input type="text" id="buscar" placeholder="Buscar por cliente..."
           class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<div class="overflow-visible bg-white rounded-lg shadow">
    <div style="max-height:600px; overflow-y:auto;">
        <table class="min-w-full text-sm text-gray-800" id="tablaPagos">
            <thead class="bg-blue-900 text-white text-sm uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-right">Total deuda</th>
                    <th class="px-4 py-3 text-right">Pagado</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($prestamos as $prestamo)
                    @php
                        $totalDeuda = $prestamo->valor_prestamo + ($prestamo->valor_prestamo * (($prestamo->porcentaje_interes / 100) * $prestamo->plazo));
                        $pagado = $prestamo->pagos->sum('monto') ?? 0;
                        $saldo = $totalDeuda - $pagado;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $prestamo->cliente->nombre_completo }}</td>
                        <td class="px-4 py-2 text-right">L. {{ number_format($totalDeuda, 2) }}</td>
                        <td class="px-4 py-2 text-right">L. {{ number_format($pagado, 2) }}</td>
                        <td class="px-4 py-2 text-right {{ $saldo <= 0 ? 'text-green-600 font-bold' : '' }}">
                            L. {{ number_format($saldo, 2) }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                {{ ucfirst($prestamo->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="relative inline-block text-left">
                                <button type="button"
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded shadow text-sm inline-flex items-center gap-1"
                                        onclick="toggleMenu({{ $prestamo->id }})">
                                    ⚙️ Acciones
                                    <span class="transition-transform duration-200">▾</span>
                                </button>

                                <div id="menu-{{ $prestamo->id }}" class="hidden absolute z-50 mt-2 w-44 bg-white border border-gray-200 rounded shadow-xl">
                                    <a href="{{ route('pagos.create', $prestamo->id) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        💰 Generar Pago
                                    </a>
                                    <a href="{{ route('pagos.listar', $prestamo->id) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        🗑️ Eliminar Pago
                                    </a>
                                    <a href="{{ route('pagos.historial', $prestamo->id) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📜 Ver Historial de Pagos
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 italic">
                            No hay préstamos activos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

{{-- 🔹 Encabezado sticky --}}
<style>
    #tablaPagos thead {
        position: sticky;
        top: 0;
        background-color: #1e3a8a;
        z-index: 10;
    }
</style>

{{-- 🔹 Script para desplegar menú --}}
<script>
    function toggleMenu(id) {
        const menu = document.getElementById('menu-' + id);
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (!menu.contains(e.target) && !e.target.closest('button')) {
                menu.classList.add('hidden');
            }
        });
    });
</script>

{{-- 🔹 Búsqueda dinámica en DOM --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar');
    const filas = document.querySelectorAll('#tablaPagos tbody tr');

    buscarInput.addEventListener('input', function() {
        const texto = buscarInput.value.toLowerCase();

        filas.forEach(fila => {
            const cliente = fila.cells[0].textContent.toLowerCase();
            fila.style.display = cliente.includes(texto) ? '' : 'none';
        });
    });
});
</script>

@endsection