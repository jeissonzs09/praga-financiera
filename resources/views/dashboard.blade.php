@extends('layouts.app')

@section('content')
<div class="p-6 space-y-8">

    <h1 class="text-2xl font-bold text-gray-800">Inversiones PRAGA - Panel Principal</h1>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        {{-- Clientes --}}
        <a href="{{ route('clientes.index') }}" class="block">
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                <div class="flex flex-col items-center">
                    <i class="bi bi-people-fill text-blue-600 text-5xl mb-3"></i>
                    <strong class="text-gray-600 text-sm uppercase tracking-wide">Clientes</strong>
                    <div class="text-3xl font-bold text-blue-600 mt-1">{{ $clientes_count }}</div>
                </div>
            </div>
        </a>

        {{-- Pagos de hoy --}}
        <a href="{{ route('pagos.index', 1) }}" class="block">
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                <div class="flex flex-col items-center">
                    <i class="bi bi-cash-stack text-green-600 text-5xl mb-3"></i>
                    <strong class="text-gray-600 text-sm uppercase tracking-wide">Pagos recibidos hoy</strong>
                    <div class="text-2xl font-bold text-green-600 mt-1">L. {{ number_format($pagos_dia, 2) }}</div>
                </div>
            </div>
        </a>

        {{-- Fecha actual --}}
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <div class="flex flex-col items-center">
                <i class="bi bi-calendar-month text-purple-600 text-5xl mb-3"></i>
                <strong class="text-gray-600 text-sm uppercase tracking-wide">Fecha actual</strong>
                <div class="text-xl font-bold text-purple-600 mt-1">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Calendario de pagos --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Calendario de pagos</h2>
        <div id="calendario" style="width: 100%; min-height: 500px;"></div>
    </div>

    {{-- Modal Pagos --}}
    <div id="modalPagos" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Pagos del día</h2>
            <ul id="listaPagos" class="space-y-2"></ul>
            <div class="mt-4 flex justify-end">
                <button onclick="cerrarModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendario');
    let listaPagos = document.getElementById('listaPagos');
    let modal = document.getElementById('modalPagos');

    fetch('{{ route("calendario.pagos") }}')
        .then(res => res.json())
        .then(events => {
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                events: events,
                eventContent: function(arg) {
                    // Aquí reemplazamos el título por un botón
                    let btn = document.createElement('button');
                    btn.className = 'bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700';
                    btn.innerText = 'Ver Pagos';
                    btn.onclick = () => mostrarPagos(arg.event.extendedProps.pagos);

                    return { domNodes: [btn] };
                },
                eventDidMount: function(info) {
                    // Si algún pago está atrasado, coloreamos el fondo de rojo
                    if (info.event.extendedProps.pagos.some(p => p.estado === 'Atrasado')) {
                        info.el.style.backgroundColor = 'red';
                        info.el.style.borderColor = 'darkred';
                    }
                }
            });

            calendar.render();
        });

    function mostrarPagos(pagos) {
        listaPagos.innerHTML = '';
        pagos.forEach(pago => {
            let li = document.createElement('li');
            li.className = 'flex justify-between items-center p-2 border rounded';
            li.innerHTML = `
                <span>${pago.cliente} - L. ${pago.total.toFixed(2)}</span>
                <span class="${pago.estado === 'Atrasado' ? 'text-red-600 font-bold' : 'text-gray-600'}">${pago.estado}</span>
            `;
            listaPagos.appendChild(li);
        });
        modal.classList.remove('hidden');
    }

    window.cerrarModal = function() {
        modal.classList.add('hidden');
    };
});
</script>
@endsection