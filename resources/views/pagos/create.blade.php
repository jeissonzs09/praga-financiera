@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-lg font-bold mb-4">
        Registrar pago — {{ $prestamo->cliente->nombre_completo }}
    </h2>

    <form action="{{ route('pagos.store', $prestamo->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="block text-sm font-medium">Monto</label>
            <input type="number" step="0.01" name="monto" required class="border rounded px-3 py-2 w-full">
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Observaciones</label>
            <textarea name="observaciones" class="border rounded px-3 py-2 w-full"></textarea>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('pagos.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Guardar Pago
            </button>
        </div>
    </form>
</div>
@endsection