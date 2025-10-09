@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-blue-600 mb-2 flex items-center justify-center">
        <i class="fas fa-cash-register mr-2"></i>
        Registrar nuevo pago
    </h2>

    <!-- Nombre del cliente -->
    <p class="text-center text-gray-700 mb-6">
        <strong>Cliente:</strong> {{ $prestamo->cliente->nombre_completo }}
    </p>

    <form action="{{ route('pagos.distribuir', $prestamo->id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Fecha de pago -->
        <div>
            <label for="fecha_pago" class="block text-sm font-medium text-gray-700">📅 Fecha de pago</label>
            <input type="date" name="fecha_pago" id="fecha_pago"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-600 focus:ring-blue-600"
                   value="{{ old('fecha_pago', \Carbon\Carbon::now()->format('Y-m-d')) }}">
        </div>

        <!-- Monto recibido -->
        <div>
            <label for="monto" class="block text-sm font-medium text-gray-700">💰 Monto recibido</label>
            <input type="number" name="monto" id="monto" step="0.01" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
        </div>

        <!-- Método de pago -->
        <div>
            <label for="metodo_pago" class="block text-sm font-medium text-gray-700">💳 Método de pago</label>
            <select name="metodo_pago" id="metodo_pago" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
                <option value="">Seleccione...</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Transferencia">Transferencia</option>
            </select>
        </div>

        <!-- Observaciones -->
        <div>
            <label for="observaciones" class="block text-sm font-medium text-gray-700">📝 Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <!-- Botón Cancelar -->
            <a href="{{ route('pagos.index') }}"
               class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded shadow text-sm">
                ❌ Cancelar
            </a>

            <!-- Botón Realizar Pago -->
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow text-sm">
                <i class="fas fa-check-circle"></i> Realizar Pago
            </button>
        </div>
    </form>
</div>
@endsection