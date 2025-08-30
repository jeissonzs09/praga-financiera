@extends('layouts.app')

@php
    $titulo = 'Listado de Clientes';
@endphp

@section('content')
<div class="p-4">

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
                            <a href="{{ route('clientes.show', $cliente->id_cliente) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-sm">
                                Ver Detalle
                            </a>
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