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
                                <a href="javascript:void(0);" 
   onclick="abrirModalContrato({{ $prestamo->id }}, '{{ route('contratos.generarPdfModal', $prestamo->id) }}')"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📄 Generar contrato
</a>
<a href="javascript:void(0);" 
   onclick="abrirModalPagare({{ $prestamo->id }}, '{{ route('contratos.generarPagareModal', $prestamo->id) }}')"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📝 Generar pagaré
</a>

<a href="javascript:void(0);" 
   onclick="abrirModalDeclaracion({{ $prestamo->id }}, '{{ route('contratos.generarDeclaracionModal', $prestamo->id) }}')"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📑 Declaración de garantías
</a>

<a href="javascript:void(0);" 
   onclick="abrirModalAutorizacion({{ $prestamo->id }}, '{{ route('contratos.generarAutorizacionModal', $prestamo->id) }}')"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
   📝 Generar autorización
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

{{-- 🔹 Modal para ingresar datos del contrato --}}
<div id="modalContrato" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="toggleModal(false)">✕</button>
        <h2 class="text-lg font-semibold mb-4">Datos para el contrato</h2>

        <form id="formContrato" method="POST" action="">
            @csrf
            <input type="hidden" name="prestamo_id" id="prestamo_id_modal">

            {{-- 🔹 Fecha de inicio del préstamo --}}
            <div class="mb-4">
                <h3 class="font-semibold mb-2">Fecha de inicio del préstamo</h3>
                <input type="date" name="fecha_inicio" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            {{-- 🔹 Ciudad, Departamento y Fecha de firma --}}
            <div class="mb-4">
                <h3 class="font-semibold mb-2">Datos de firma del contrato</h3>

                <div class="mb-2">
                    <label class="block text-sm">Ciudad:</label>
                    <input type="text" name="ciudad" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" value="Pespire" required>
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Departamento:</label>
                    <input type="text" name="departamento" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" value="Choluteca" required>
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Fecha de firma del contrato:</label>
                    <input type="date" name="fecha_firma" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                </div>
            </div>

            {{-- 🔹 Botón de envío --}}
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mt-3 w-full transition-colors">
                Descargar contrato
            </button>
        </form>
    </div>
</div>

<!-- Modal Generar Pagaré -->
<div id="modalPagare" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="toggleModalPagare(false)">✕</button>
        <h2 class="text-lg font-semibold mb-4">Datos para el pagaré</h2>

        <form id="formPagare" method="POST" action="">
            @csrf
            <input type="hidden" name="prestamo_id" id="prestamo_id_pagare">

            <div class="mb-4">
                <label class="block text-sm mb-1">Fecha de firma del pagaré</label>
                <input type="date" name="fechaFirma" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Ciudad</label>
                <input type="text" name="ciudadFirma" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Departamento</label>
                <input type="text" name="departamentoFirma" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full transition-colors">
                Generar PDF
            </button>
        </form>
    </div>
</div>

<!-- Modal Generar Declaración -->
<div id="modalDeclaracion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="toggleModalDeclaracion(false)">✕</button>
        <h2 class="text-lg font-semibold mb-4">Datos para la declaración</h2>

        <form id="formDeclaracion" method="POST" action="">
            @csrf
            <input type="hidden" name="prestamo_id" id="prestamo_id_declaracion">

            <div class="mb-4">
                <label class="block text-sm mb-1">Fecha de la declaración</label>
                <input type="date" name="fechaDeclaracion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Ciudad</label>
                <input type="text" name="ciudadDeclaracion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Departamento</label>
                <input type="text" name="departamentoDeclaracion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full transition-colors">
                Generar PDF
            </button>
        </form>
    </div>
</div>

<!-- Modal Generar Autorización -->
<div id="modalAutorizacion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="toggleModalAutorizacion(false)">✕</button>
        <h2 class="text-lg font-semibold mb-4">Datos para la autorización</h2>

        <form id="formAutorizacion" method="POST" action="">
            @csrf
            <input type="hidden" name="prestamo_id" id="prestamo_id_autorizacion">

            <div class="mb-4">
                <label class="block text-sm mb-1">Ciudad</label>
                <input type="text" name="ciudadAutorizacion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Departamento</label>
                <input type="text" name="departamentoAutorizacion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Fecha</label>
                <input type="date" name="fechaAutorizacion" class="border rounded w-full p-2 focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full transition-colors">
                Generar PDF
            </button>
        </form>
    </div>
</div>

<script>
    // Abrir y cerrar modal
    function toggleModal(open = true) {
        const modal = document.getElementById('modalContrato');
        if(open) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    // Abrir modal y pasar el id del préstamo y la URL del formulario
    function abrirModalContrato(prestamoId, url) {
        toggleModal(true);
        document.getElementById('prestamo_id_modal').value = prestamoId;
        document.getElementById('formContrato').action = url;
    }

    // Abrir y cerrar modal del pagaré
function toggleModalPagare(open = true) {
    const modal = document.getElementById('modalPagare');
    if(open) modal.classList.remove('hidden');
    else modal.classList.add('hidden');
}

// Abrir modal y pasar id del préstamo y URL del formulario
function abrirModalPagare(prestamoId, url) {
    toggleModalPagare(true);
    const form = document.getElementById('formPagare');
    form.action = url;
    document.getElementById('prestamo_id_pagare').value = prestamoId;

    // Opcional: valores por defecto
    form.querySelector('input[name="fechaFirma"]').value = new Date().toISOString().split('T')[0];
    form.querySelector('input[name="ciudadFirma"]').value = "Pespire"; // o puedes sacar de cliente
    form.querySelector('input[name="departamentoFirma"]').value = "Choluteca"; // o del cliente
}

function toggleModalDeclaracion(open = true) {
        const modal = document.getElementById('modalDeclaracion');
        if(open) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    // Abrir modal y pasar id del préstamo y URL del formulario
    function abrirModalDeclaracion(prestamoId, url) {
        toggleModalDeclaracion(true);
        const form = document.getElementById('formDeclaracion');
        form.action = url;
        document.getElementById('prestamo_id_declaracion').value = prestamoId;

        // Valores por defecto opcionales
        form.querySelector('input[name="fechaDeclaracion"]').value = new Date().toISOString().split('T')[0];
        form.querySelector('input[name="ciudadDeclaracion"]').value = "Pespire";
        form.querySelector('input[name="departamentoDeclaracion"]').value = "Choluteca";
    }

        function toggleModalAutorizacion(open = true) {
        const modal = document.getElementById('modalAutorizacion');
        if(open) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    function abrirModalAutorizacion(prestamoId, url) {
        toggleModalAutorizacion(true);
        const form = document.getElementById('formAutorizacion');
        form.action = url;
        document.getElementById('prestamo_id_autorizacion').value = prestamoId;

        // Opcional: valores por defecto
        form.querySelector('input[name="fechaAutorizacion"]').value = new Date().toISOString().split('T')[0];
        form.querySelector('input[name="ciudadAutorizacion"]').value = "Pespire";
        form.querySelector('input[name="departamentoAutorizacion"]').value = "Choluteca";
    }

</script>



@endsection