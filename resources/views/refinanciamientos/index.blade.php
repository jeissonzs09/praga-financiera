@extends('layouts.app')

@php
    $titulo = 'Refinanciamiento de Préstamos';
@endphp

@section('content')
<div class="p-4">

    <h2 class="text-xl font-semibold mb-4">Listado de préstamos activos</h2>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800">
            <thead class="bg-blue-900 text-white text-sm uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Tipo Préstamo</th>
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
                        <td class="px-4 py-2">L. {{ number_format($prestamo->valor_prestamo, 2) }}</td>
                        <td class="px-4 py-2">{{ $prestamo->porcentaje_interes }}%</td>
                        <td class="px-4 py-2">{{ $prestamo->plazo }} meses</td>
                        <td class="px-4 py-2">{{ $prestamo->periodo }}</td>
                        <td class="px-4 py-2">{{ $prestamo->fecha_inicio }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                {{ ucfirst($prestamo->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center space-x-1">
                            <!-- Botón Refinanciar -->
                            <a href="{{ route('refinanciamientos.create', $prestamo->id) }}"
                               class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                <i class="fas fa-sync-alt"></i> Refinanciar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500 italic">
                            No hay préstamos activos para refinanciar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection