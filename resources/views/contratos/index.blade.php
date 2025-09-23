@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-4">Listado de Contratos</h1>

    {{-- 🔹 Filtros --}}
    <form method="GET" action="{{ route('contratos.index') }}" class="mb-4 flex flex-wrap gap-2 items-center">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Buscar por cliente"
               class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

        <select name="estado" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="">-- Todos los estados --</option>
            <option value="Activo" {{ request('estado') === 'Activo' ? 'selected' : '' }}>Activo</option>
            <option value="Finalizado" {{ request('estado') === 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
        </select>

        <input type="date" name="fecha" value="{{ request('fecha') }}"
               class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
            Filtrar
        </button>
    </form>

    {{-- 🔹 Paginación arriba --}}
    <div class="flex justify-end mb-2">
        {{ $contratos->links() }}
    </div>

    {{-- 🔹 Tabla de contratos --}}
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
            @foreach($contratos as $prestamo)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $prestamo->cliente->nombre_completo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->tipo_prestamo }}</td>
                    <td class="px-4 py-2">L. {{ number_format($prestamo->valor_prestamo, 2) }}</td>
                    <td class="px-4 py-2">{{ $prestamo->porcentaje_interes }}%</td>
                    <td class="px-4 py-2">{{ $prestamo->plazo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->periodo }}</td>
                    <td class="px-4 py-2">{{ $prestamo->fecha_inicio ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $prestamo->estado }}</td>
                    <td class="px-4 py-2 relative">
                        <div class="inline-block text-left">
                            <button type="button"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-sm"
                                    onclick="toggleMenu({{ $prestamo->id }})">
                                Documentos
                            </button>

                            <div id="menu-{{ $prestamo->id }}" class="hidden absolute z-10 mt-2 w-48 bg-white border border-gray-300 rounded shadow">
                                <a href="{{ route('contratos.pdf', $prestamo->id) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    📄 Generar contrato
                                </a>
                                <a href="{{ route('contratos.pagare', $prestamo->id) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    📝 Generar pagaré
                                </a>
                                <a href="{{ route('contratos.declaracion', $prestamo->id) }}"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📑 Declaración de garantías
</a>
<a href="{{ route('contratos.autorizacion', $prestamo->id) }}"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📝 Autorización
</a>

                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 🔹 Paginación abajo --}}
    <div class="mt-4">
        {{ $contratos->links() }}
    </div>
</div>

{{-- 🔹 Script para desplegar menú --}}
<script>
    function toggleMenu(id) {
        const menu = document.getElementById('menu-' + id);
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (!menu.contains(e.target) && !e.target.matches('button')) {
                menu.classList.add('hidden');
            }
        });
    });
</script>
@endsection