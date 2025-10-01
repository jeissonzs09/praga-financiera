@extends('layouts.app')

@php
    $titulo = 'Listado de Clientes';
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


    <!-- Buscador -->
    <div class="mb-4 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4">
        <form action="{{ route('clientes.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Buscar por nombre..." 
                   class="border border-gray-300 rounded px-3 py-2 w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
                Buscar
            </button>
        </form>

        <a href="{{ route('clientes.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
            <i class="fas fa-user-plus"></i> Nuevo Cliente
        </a>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800">
            <thead class="bg-blue-900 text-white text-sm uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left">Celular</th>
                    <th class="px-4 py-3 text-left">Profesión</th>
                    <th class="px-4 py-3 text-left">Negocio</th>
                    <th class="px-4 py-3 text-left">Dirección</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $cliente->nombre_completo }}</td>
                        <td class="px-4 py-2">{{ $cliente->celular }}</td>
                        <td class="px-4 py-2">{{ $cliente->profesion }}</td>
                        <td class="px-4 py-2">{{ $cliente->negocio }}</td>
                        <td class="px-4 py-2">{{ $cliente->direccion }}</td>
                        <td class="px-4 py-2 text-center">
                            <div x-data="{ open: false }" class="relative inline-block text-left">
    <button @click="open = !open" 
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded shadow text-sm inline-flex items-center gap-1">
        ⚙️ Acciones
        <span :class="{'rotate-180': open}" class="transition-transform duration-200">▾</span>
    </button>

    <div x-show="open" @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute z-10 mt-2 w-44 bg-white border border-gray-200 rounded shadow-xl">
         
        <a href="{{ route('clientes.show', $cliente->id_cliente) }}"
           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            👁️ Ver Detalle
        </a>
        <a href="{{ route('clientes.edit', $cliente->id_cliente) }}"
           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            ✏️ Editar
        </a>
        <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST"
              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este cliente?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                🗑️ Eliminar
            </button>
        </form>
    </div>
</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500 italic">
                            No hay clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection