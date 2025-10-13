@extends('layouts.app')

@php
    $titulo = 'Cuotas Pendientes';
@endphp

@section('content')
<div class="p-4">

    <h2 class="text-xl font-semibold mb-4 text-center">{{ $titulo }}</h2>

    <!-- Buscador -->
    <div class="mb-4 flex gap-2 items-center justify-center">
        <input type="text" id="buscar" placeholder="Buscar por cliente..."
               class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="overflow-visible bg-white rounded-lg shadow">
        <div style="max-height:600px; overflow-y:auto; position:relative; z-index:0;">
            <table class="min-w-full text-sm text-gray-800" id="tablaCuotas">
                <thead class="bg-blue-900 text-white text-sm uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">N°</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-center">Fecha</th>
                        <th class="px-4 py-3 text-center">Cuotas Atrasadas</th>
                        <th class="px-4 py-3 text-right">Capital Pendiente</th>
                        <th class="px-4 py-3 text-right">Interés Pendiente</th>
                        <th class="px-4 py-3 text-right">Monto Pendiente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        // Agrupar cuotas atrasadas por cliente
                        $clientes = [];
                        foreach($cuotasAtrasadas as $cuota){
                            $idCliente = $cuota['cliente']->id_cliente;

                            if(!isset($clientes[$idCliente])){
                                $clientes[$idCliente] = [
                                    'nombre_completo' => $cuota['cliente']->nombre_completo,
                                    'primer_fecha' => $cuota['vence'],
                                    'capital_pendiente' => 0,
                                    'interes_pendiente' => 0,
                                    'monto_pendiente' => 0,
                                    'cuotas_atrasadas' => 0, // Inicializamos aquí
                                ];
                            }

                            // Actualizar la primera fecha de vencimiento
                            if(\Carbon\Carbon::parse($cuota['vence'])->lt(\Carbon\Carbon::parse($clientes[$idCliente]['primer_fecha']))){
                                $clientes[$idCliente]['primer_fecha'] = $cuota['vence'];
                            }

                            // Sumar los importes pendientes
                            $clientes[$idCliente]['capital_pendiente'] += $cuota['capital'];
                            $clientes[$idCliente]['interes_pendiente'] += $cuota['interes'];
                            $clientes[$idCliente]['monto_pendiente'] += $cuota['total'];

                            // Contar la cuota atrasada
                            $clientes[$idCliente]['cuotas_atrasadas']++;
                        }
                    @endphp

                    @forelse($clientes as $index => $cliente)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $cliente['nombre_completo'] }}</td>
                            <td class="px-4 py-2 text-center">{{ \Carbon\Carbon::parse($cliente['primer_fecha'])->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-center">{{ $cliente['cuotas_atrasadas'] }}</td>
                            <td class="px-4 py-2 text-right">L. {{ number_format($cliente['capital_pendiente'], 2) }}</td>
                            <td class="px-4 py-2 text-right">L. {{ number_format($cliente['interes_pendiente'], 2) }}</td>
                            <td class="px-4 py-2 text-right font-bold">L. {{ number_format($cliente['monto_pendiente'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500 italic">
                                No hay cuotas pendientes
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
    #tablaCuotas thead {
        position: sticky;
        top: 0;
        background-color: #1e3a8a;
        z-index: 10;
    }
</style>

{{-- 🔹 Búsqueda dinámica en DOM --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar');
    const filas = document.querySelectorAll('#tablaCuotas tbody tr');

    buscarInput.addEventListener('input', function() {
        const texto = buscarInput.value.toLowerCase();

        filas.forEach(fila => {
            const cliente = fila.cells[1].textContent.toLowerCase();
            fila.style.display = cliente.includes(texto) ? '' : 'none';
        });
    });
});
</script>

@endsection