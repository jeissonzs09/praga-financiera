@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-4">Listado de Contratos</h1>

{{-- 🔹 Filtros dinámicos --}}
<div class="mb-4 flex flex-wrap gap-2 items-center">
    <input type="text" id="buscar" name="buscar" value="{{ request('buscar') }}"
           placeholder="Buscar por cliente..."
           class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

    <select id="estado" name="estado"
            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="">-- Todos los estados --</option>
        <option value="Activo" {{ request('estado') === 'Activo' ? 'selected' : '' }}>Activo</option>
        <option value="Finalizado" {{ request('estado') === 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
    </select>

    <input type="date" id="fecha" name="fecha" value="{{ request('fecha') }}"
           class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
</div>

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
        <tbody id="contratos-body">
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

    {{-- 🔹 Nuevo enlace para Resumen de Operación --}}
    <a href="javascript:void(0);" 
       onclick="abrirModalResumenOperacion({{ $prestamo->id }}, '{{ route('contratos.generarResumenOperacionModal', $prestamo->id) }}')"
       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
       📊 Resumen de Datos
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

<!-- 🔷 Modal Resumen Operación -->
<div id="modalResumenOperacion" class="hidden">
    <div class="modal-content">
        <button onclick="toggleModalResumenOperacion(false)" class="close-button">✖</button>

        <h2 class="text-base font-bold mb-3 text-center">Ingresar Deducciones</h2>
        <form id="formResumenOperacion" method="POST">
            @csrf
            <input type="hidden" id="prestamo_id_resumen" name="prestamo_id">

            <div class="mb-2">
                <label>Gastos Administrativos (1%)</label>
                <input type="number" name="gastos_administrativos" step="0.01" class="deduccion w-full border px-2 py-1" required>
            </div>
            <div class="mb-2">
                <label>Deducción Central de Riesgos</label>
                <input type="number" name="deduccion_central" step="0.01" class="deduccion w-full border px-2 py-1" required>
            </div>
            <div class="mb-2">
                <label>Capital Pendiente</label>
                <input type="number" name="capital_pendiente" step="0.01" class="deduccion w-full border px-2 py-1" required>
            </div>
            <div class="mb-2">
                <label>Interés Pendiente</label>
                <input type="number" name="interes_pendiente" step="0.01" class="deduccion w-full border px-2 py-1" required>
            </div>
            <div class="mb-2">
                <label>Mora</label>
                <input type="number" name="mora" step="0.01" class="deduccion w-full border px-2 py-1" required>
            </div>

            <div class="mb-2">
                <label>Total Deducciones</label>
                <input type="number" name="total_deducciones" step="0.01" id="total_deducciones" class="w-full border px-2 py-1" readonly required>
            </div>

            <div class="mb-2">
                <label>Total a Entregar</label>
                <input type="number" name="total_entregar" step="0.01" id="total_entregar" class="w-full border px-2 py-1" required>
            </div>

            <div class="flex justify-end mt-3">
                <button type="button" onclick="toggleModalResumenOperacion(false)" class="mr-2 px-3 py-1 bg-gray-300 rounded">Cancelar</button>
                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Generar PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- 🔷 CSS -->
<style>
#modalResumenOperacion {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    background-color: rgba(0,0,0,0.5);
    overflow-y: auto;
    padding-top: 6rem; /* separa del header */
}

#modalResumenOperacion.hidden {
    display: none;
}

.modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 480px;
    padding: 1.5rem;
    position: relative;
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
}

.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    border: none;
    background: transparent;
    font-size: 1.2rem;
    cursor: pointer;
}
</style>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar');
    const estadoSelect = document.getElementById('estado');
    const fechaInput = document.getElementById('fecha');
    const tbody = document.getElementById('contratos-body');

    function filtrarContratos() {
        const buscar = buscarInput.value;
        const estado = estadoSelect.value;
        const fecha = fechaInput.value;

        const url = new URL(`{{ route('contratos.index') }}`);
        if (buscar) url.searchParams.append('buscar', buscar);
        if (estado) url.searchParams.append('estado', estado);
        if (fecha) url.searchParams.append('fecha', fecha);

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('#contratos-body');
                const pagination = doc.querySelector('.mt-4');

                if (newTbody) tbody.innerHTML = newTbody.innerHTML;
                if (pagination) document.querySelector('.mt-4').innerHTML = pagination.innerHTML;
            })
            .catch(err => console.error('Error al filtrar:', err));
    }

    buscarInput.addEventListener('keyup', filtrarContratos);
    estadoSelect.addEventListener('change', filtrarContratos);
    fechaInput.addEventListener('change', filtrarContratos);
});
</script>

<!-- 🔷 JS -->
<script>
function toggleModalResumenOperacion(open = true) {
    const modal = document.getElementById('modalResumenOperacion');
    modal.style.display = open ? 'flex' : 'none';
}

function abrirModalResumenOperacion(prestamoId, url) {
    toggleModalResumenOperacion(true);
    const form = document.getElementById('formResumenOperacion');
    form.action = url;
    document.getElementById('prestamo_id_resumen').value = prestamoId;

    // Limpiar campos al abrir
    form.querySelectorAll('input').forEach(input => {
        if(input.type === "number") input.value = '';
    });
}

// Calcular total automáticamente cuando cambien los campos de deducción
document.addEventListener('input', function(e){
    if(e.target.classList.contains('deduccion')) {
        const deducciones = document.querySelectorAll('.deduccion');
        let total = 0;
        deducciones.forEach(input => total += parseFloat(input.value) || 0);
        document.getElementById('total_deducciones').value = total.toFixed(2);
    }
});
</script>
@endsection