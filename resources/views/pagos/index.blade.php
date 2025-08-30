@extends('layouts.app')

@php
    $titulo = 'Registro de Pagos';
@endphp

@section('content')
<div class="p-4">

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-gray-800">
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
                        <td class="px-4 py-2 text-center space-x-1">
                            <!-- Botones de acciones -->
                            <a href="{{ route('pagos.plan', $prestamo->id) }}" 
                               class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs">
                                <i class="fas fa-list"></i> Plan
                            </a>
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
@endsection
