@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-praga mb-6 flex items-center justify-center">
        <svg class="w-6 h-6 mr-2 text-praga" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 3v18h18V3H3zm3 3h12v12H6V6z"/>
        </svg>
        Registrar nuevo pago
    </h2>

    <form action="{{ route('pagos.distribuir', $prestamo->id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Monto recibido -->
        <div>
            <label for="monto" class="block text-sm font-medium text-gray-700">💰 Monto recibido</label>
            <input type="number" name="monto" id="monto" step="0.01" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga">
        </div>

        <!-- Método de pago -->
        <div>
            <label for="metodo_pago" class="block text-sm font-medium text-gray-700">💳 Método de pago</label>
            <select name="metodo_pago" id="metodo_pago" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga">
                <option value="">Seleccione...</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Transferencia">Transferencia</option>
            </select>
        </div>

        <!-- Observaciones -->
        <div>
            <label for="observaciones" class="block text-sm font-medium text-gray-700">📝 Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-praga focus:ring-praga"></textarea>
        </div>

        <!-- Botón continuar -->
        <div class="text-right">
            <button type="submit"
                    class="inline-flex items-center bg-praga hover:bg-praga-dark text-white font-semibold px-6 py-2 rounded shadow">
                ➡️ Continuar con distribución
            </button>
        </div>
    </form>
</div>
@endsection