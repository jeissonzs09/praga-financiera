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
    <div style="max-height:600px; overflow-y:auto; position:relative; z-index:0;">
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
    onclick="toggleMenu({{ $prestamo->id }}, event)">
    ⚙️ Acciones
    <span class="transition-transform duration-200">▾</span>
</button>
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

<!-- Contenedor global para el menú -->
<div id="menu-container"></div>

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

{{-- 🔹 Script para desplegar menú dinámico --}}
<script>
let menuAbiertoId = null;

function toggleMenu(id, event) {
    const container = document.getElementById('menu-container');

    // Si el mismo botón se vuelve a presionar, cerrar el menú
    if (menuAbiertoId === id) {
        container.innerHTML = '';
        menuAbiertoId = null;
        return;
    }

    // Cerrar cualquier menú anterior
    container.innerHTML = '';
    menuAbiertoId = id;

    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();

    const menu = document.createElement('div');
    menu.className = 'fixed z-[9999] bg-white border border-gray-200 rounded shadow-xl';
    menu.style.top = rect.bottom + 'px';
    menu.style.left = rect.left + 'px';
    menu.style.minWidth = '11rem';

    menu.innerHTML = `
        <a href="{{ route('pagos.create', '__ID__') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">💰 Generar Pago</a>
        <a href="{{ route('pagos.listar', '__ID__') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">🗑️ Eliminar Pago</a>
        <a href="{{ route('pagos.historial', '__ID__') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📜 Ver Historial de Pagos</a>
    `.replace(/__ID__/g, id);

    container.appendChild(menu);
}

// Cerrar menú al hacer clic fuera
document.addEventListener('click', function (e) {
    const menu = document.getElementById('menu-container');
    if (!e.target.closest('.fixed') && !e.target.closest('button')) {
        menu.innerHTML = '';
        menuAbiertoId = null;
    }
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

        // Cerrar menú si se está filtrando
        document.getElementById('menu-container').innerHTML = '';
    });
});
</script>

@endsection