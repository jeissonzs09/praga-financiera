@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-blue-600 mb-6 flex items-center justify-center">
        <i class="fas fa-cash-register mr-2"></i>
        Registrar nuevo pago
    </h2>

    <form action="{{ route('pagos.distribuir', $prestamo->id) }}" method="POST" class="space-y-6">
        @csrf

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

        <!-- Botón continuar -->
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow text-sm">
                <i class="fas fa-check-circle"></i> Realizar Pago
            </button>
        </div>
    </form>
</div>
@endsection