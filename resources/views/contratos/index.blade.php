@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-4">Listado de Contratos</h1>

    <table class="min-w-full bg-white rounded shadow">
        <thead class="bg-blue-900 text-white">
            <tr>
                <th class="px-4 py-2">Cliente</th>
                <th class="px-4 py-2">Tipo Préstamo</th>
                <th class="px-4 py-2">Monto</th>
                <th class="px-4 py-2">Interés</th>
                <th class="px-4 py-2">Plazo</th>
                <th class="px-4 py-2">Periodo</th>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Estado</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prestamos as $prestamo)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $prestamo->cliente->nombre_completo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->tipo_prestamo }}</td>
                    <td class="px-4 py-2">L. {{ number_format($prestamo->valor_prestamo, 2) }}</td>
                    <td class="px-4 py-2">{{ $prestamo->porcentaje_interes }}%</td>
                    <td class="px-4 py-2">{{ $prestamo->plazo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->periodo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->fecha_inicio }}</td>
                    <td class="px-4 py-2">{{ $prestamo->estado }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('contratos.pdf', $prestamo->id) }}"
                           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                            Generar contrato
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $prestamos->links() }}
    </div>
</div>
@endsection