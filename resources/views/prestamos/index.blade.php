@extends('layouts.app')

@php
    $titulo = 'Listado de Préstamos';
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

    <div class="mb-4 flex justify-end">
        <a href="{{ route('prestamos.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
            <i class="fas fa-plus-circle"></i> Nuevo Préstamo
        </a>
    </div>

<form method="GET" action="{{ route('prestamos.index') }}" class="mb-4 flex flex-wrap gap-2 items-center">
    <input type="text" name="buscar" value="{{ request('buscar') }}"
           placeholder="Buscar por cliente"
           class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

    <select name="estado" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="">-- Todos los estados --</option>
        <option value="Activo" {{ request('estado') === 'Activo' ? 'selected' : '' }}>Activo</option>
        <option value="Finalizado" {{ request('estado') === 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
    </select>

    {{-- 🔹 Nuevo campo para filtrar por fecha --}}
    <input type="date" name="fecha" value="{{ request('fecha') }}"
           class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
        Filtrar
    </button>
</form>


<div class="flex justify-end mb-2">
    {{ $prestamos->links() }}
</div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        
        <table class="min-w-full text-sm text-gray-800">
            <thead class="bg-blue-900 text-white text-sm uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Tipo Préstamo</th>
                    <th class="px-4 py-3 text-left">Tipo Interés</th>
                    <th class="px-4 py-3 text-left">Monto</th>
                    <th class="px-4 py-3 text-left">Interés (%)</th>
                    <th class="px-4 py-3 text-left">Plazo</th>
                    <th class="px-4 py-3 text-left">Periodo</th>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($prestamos as $prestamo)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $prestamo->cliente->nombre_completo }}</td>
                        <td class="px-4 py-2">{{ $prestamo->tipo_prestamo }}</td>
                        <td class="px-4 py-2">{{ $prestamo->tipo_interes }}</td>
                        <td class="px-4 py-2">L. {{ number_format($prestamo->valor_prestamo, 2) }}</td>
                        <td class="px-4 py-2">{{ $prestamo->porcentaje_interes }}%</td>
                        <td class="px-4 py-2">{{ $prestamo->plazo }} meses</td>
                        <td class="px-4 py-2">{{ $prestamo->periodo }}</td>
                        <td class="px-4 py-2">{{ $prestamo->fecha_inicio}}</td>
                        <td class="px-4 py-2">
                            @if(strtolower($prestamo->estado) === 'activo')
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($prestamo->estado) }}
                                </span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">
                                    {{ ucfirst($prestamo->estado) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center space-x-1">
    <!-- Botón Plan -->
    <a href="{{ route('pagos.plan', $prestamo->id) }}" 
       class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs">
        <i class="fas fa-list"></i> Plan
    </a>

    <!-- Botón Eliminar (solo si NO está finalizado) -->
    @if(strtolower($prestamo->estado) !== 'finalizado')
        <form action="{{ route('prestamos.destroy', $prestamo->id) }}" 
              method="POST" 
              class="inline-block"
              onsubmit="return confirm('¿Seguro que deseas eliminar este préstamo? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                <i class="fas fa-trash-alt"></i> Eliminar
            </button>
        </form>
    @endif
</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500 italic">
                            No hay préstamos activos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection