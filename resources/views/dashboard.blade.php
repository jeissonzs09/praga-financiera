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

    {{-- Gráfica de pagos por mes --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Pagos por mes ({{ now()->year }})</h2>
        <canvas id="pagosMes"></canvas>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendario');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        events: [
    { title: 'Pago de Juan Pérez', start: '2025-09-02', color: '#3b82f6' },
    { title: 'Pago de María López', start: '2025-09-10', color: '#22c55e' },
    { title: 'Pago de Carlos Díaz', start: '2025-09-15', color: '#f59e0b' }
],
    });
    calendar.render();
});
</script>
@endsection