@extends('layouts.app')

@php
    $titulo = 'Historial de Préstamos';
@endphp

@section('content')
<style>
    .prestamos-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 1rem;
        height: calc(100vh - 2rem);
    }

    .clientes-panel {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1rem;
        overflow: hidden;
    }

    .top-controls {
        flex: none;
        margin-bottom: 0.5rem;
    }

    .clientes-list-wrapper {
        flex: 1;
        overflow-y: auto;
        border-top: 1px solid #e5e7eb;
        max-height: calc(100vh - 12rem);
    }

    .clientes-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .clientes-list li {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .clientes-list li:hover,
    .clientes-list li:focus,
    .clientes-list li.selected {
        background-color: #fde68a;
    }

    .plan-panel {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 1rem;
        overflow-y: auto;
    }
</style>

<div class="prestamos-container">
    {{-- 🔹 Panel izquierdo --}}
    <div class="clientes-panel">
        <div class="top-controls text-center">
            <a href="{{ route('prestamos.index') }}"
               class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm">
                <i class="fas fa-arrow-left"></i> Volver a Activos
            </a>
        </div>

        <div class="top-controls">
            <input type="text" id="buscarCliente" placeholder="Buscar cliente..."
                   style="width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:0.25rem;">
        </div>

        <div class="top-controls">
            <label style="font-weight: bold; display:block; margin-bottom:0.3rem;">Ordenar por:</label>
            <div style="display: flex; gap: 1rem;">
                <label><input type="radio" name="orden" value="nombre" checked> Nombre</label>
                <label><input type="radio" name="orden" value="creacion">Creación</label>
            </div>
        </div>

        <div class="clientes-list-wrapper">
            <ul id="listaClientes" class="clientes-list">
                @foreach ($prestamos->where('estado', '!=', 'Activo') as $prestamo)
                    <li data-id="{{ $prestamo->id }}" tabindex="0">
                        {{ $prestamo->cliente->nombre_completo }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- 🔹 Panel derecho --}}
    <div id="planContainer" class="plan-panel">
        <p style="text-align:center; color:#6b7280; font-style:italic;">
            Selecciona un cliente para ver su historial de pagos
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buscarInput = document.getElementById('buscarCliente');
    const lista = document.getElementById('listaClientes');
    const planContainer = document.getElementById('planContainer');

    const allItems = Array.from(lista.querySelectorAll('li'));
    let items = [...allItems];
    let selectedIndex = -1;

    const radiosOrden = document.querySelectorAll('input[name="orden"]');
    radiosOrden.forEach(radio => {
        radio.addEventListener('change', () => ordenarClientes(radio.value));
    });

    function ordenarClientes(tipo) {
        let listaOrdenada = tipo === 'nombre'
            ? [...allItems].sort((a, b) => a.textContent.localeCompare(b.textContent))
            : [...allItems].sort((a, b) => parseInt(b.dataset.id) - parseInt(a.dataset.id));

        lista.innerHTML = '';
        listaOrdenada.forEach(li => lista.appendChild(li));
        items = listaOrdenada;
        selectedIndex = -1;
    }

    ordenarClientes('nombre');

    buscarInput.addEventListener('input', e => {
        const filtro = e.target.value.toLowerCase();
        allItems.forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(filtro) ? '' : 'none';
        });
        items = allItems.filter(li => li.style.display !== 'none');
        selectedIndex = -1;
    });

    lista.addEventListener('click', e => {
        if (e.target.closest('li')) seleccionar(e.target.closest('li'));
    });

    document.addEventListener('keydown', e => {
        const visibles = items.filter(li => li.style.display !== 'none');
        if (!visibles.length) return;

        if (e.key === 'ArrowDown') {
            selectedIndex = Math.min(selectedIndex + 1, visibles.length - 1);
            seleccionar(visibles[selectedIndex]);
        } else if (e.key === 'ArrowUp') {
            selectedIndex = Math.max(selectedIndex - 1, 0);
            seleccionar(visibles[selectedIndex]);
        }
    });

    function seleccionar(li) {
        items.forEach(i => i.classList.remove('selected'));
        li.classList.add('selected');
        li.scrollIntoView({ block: 'nearest' });
        cargarPlan(li.dataset.id);
        selectedIndex = items.indexOf(li);
    }

    async function cargarPlan(id) {
        planContainer.innerHTML = '<p style="text-align:center; color:#9ca3af; font-style:italic;">Cargando...</p>';
        const response = await fetch(`/prestamos/${id}/plan?estado=Todas`);
        const html = await response.text();
        planContainer.innerHTML = html;
    }
});
</script>
@endsection