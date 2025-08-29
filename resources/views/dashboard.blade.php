@extends('layouts.app')

@section('content')
<div class="p-6 space-y-8">

    <h1 class="text-2xl font-bold text-gray-800">Inversiones PRAGA - Panel Principal</h1>

    {{-- Tarjetas de resumen estilo ejemplo --}}
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

        {{-- Mes actual --}}
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <div class="flex flex-col items-center">
                <i class="bi bi-calendar-month text-purple-600 text-5xl mb-3"></i>
                <strong class="text-gray-600 text-sm uppercase tracking-wide">Mes actual</strong>
                <div class="text-xl font-bold text-purple-600 mt-1">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Gráfica de pagos por mes --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Pagos por mes ({{ now()->year }})</h2>
        <canvas id="pagosMes"></canvas>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('pagosMes').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($pagos_mes_labels) !!},
        datasets: [{
            label: 'Total (L.)',
            data: {!! json_encode($pagos_mes_data) !!},
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endsection