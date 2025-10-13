@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <h2 class="text-2xl font-semibold text-praga mb-6 text-center">📄 Estado de cuenta</h2>

    <div class="mt-4">
        <h3 class="text-lg font-semibold mb-2">Visualización</h3>
        <iframe 
            src="{{ route('prestamos.verEstadoCuentaPDF', $prestamo->id) }}" 
            width="100%" 
            height="800px" 
            style="border:1px solid #ccc;">
        </iframe>
    </div>

    <div class="mt-4 text-right">
        <a href="{{ route('prestamos.descargarEstadoCuentaPDF', $prestamo->id) }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
           📥 Descargar PDF
        </a>
    </div>
</div>
@endsection