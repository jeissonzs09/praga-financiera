@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-praga mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-praga" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 3v18h18V3H3zm3 3h12v12H6V6z"/>
        </svg>
        Reporte de Pagos Institucional
    </h2>

    <form method="GET" action="{{ route('reportes.excel') }}" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="inicio" class="block text-sm font-medium text-gray-700">📅 Fecha de inicio</label>
                <input type="date" name="inicio" id="inicio" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga">
            </div>
            <div>
                <label for="fin" class="block text-sm font-medium text-gray-700">📅 Fecha final</label>
                <input type="date" name="fin" id="fin" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga">
            </div>
            <div>
                <label for="prestamo_id" class="block text-sm font-medium text-gray-700">💳 Préstamo</label>
                <select name="prestamo_id" id="prestamo_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga">
                    <option value="">Seleccione...</option>
                    @foreach($prestamos as $prestamo)
                        <option value="{{ $prestamo->id }}">
                            {{ $prestamo->codigo }} — {{ $prestamo->cliente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="text-right">
            <button type="submit"
                class="inline-flex items-center bg-praga hover:bg-praga-dark text-white font-semibold px-5 py-2 rounded shadow">
                📥 Descargar Excel
            </button>
        </div>
    </form>

    @if(session('mensaje'))
        <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            {{ session('mensaje') }}
        </div>
    @endif
</div>
@endsection