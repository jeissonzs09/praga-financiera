@extends('layouts.app')

@section('content')
<div class="p-4 max-w-6xl mx-auto">

    <h2 class="text-2xl font-semibold text-praga mb-6">📊 REPORTE DE CUOTAS</h2>

<form method="POST" action="{{ route('reportes.generar') }}" class="flex flex-col md:flex-row gap-2 mb-4 items-end">
    @csrf
    <div>
        <label for="inicio" class="block text-sm font-medium text-gray-700">Fecha inicio</label>
        <input type="date" name="inicio" id="inicio" required
               value="{{ old('inicio') }}"
               class="border rounded px-3 py-1">
    </div>

    <div>
        <label for="fin" class="block text-sm font-medium text-gray-700">Fecha fin</label>
        <input type="date" name="fin" id="fin" required
               value="{{ old('fin') }}"
               class="border rounded px-3 py-1">
    </div>

    <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded shadow text-sm">
            Generar Reporte
        </button>
    </div>
</form>


    @if(isset($reportes) && count($reportes))
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full text-sm text-gray-800">
                <thead class="bg-blue-900 text-white text-sm uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha Inicio</th>
                        <th class="px-4 py-3 text-left">Fecha Fin</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($reportes as $reporte)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2">{{ $reporte['inicio'] }}</td>
                            <td class="px-4 py-2">{{ $reporte['fin'] }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                <a href="{{ route('reportes.excel', ['inicio'=>$reporte['inicio'], 'fin'=>$reporte['fin']]) }}"
                                   class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
<a href="{{ route('reportes.pdf', ['inicio'=>$reporte['inicio'], 'fin'=>$reporte['fin']]) }}"
   target="_blank"
   class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
    <i class="fas fa-file-pdf"></i> PDF
</a>

<form action="{{ route('reportes.eliminar', $loop->index) }}" method="POST" style="display:inline-block;">
    @csrf
    <button type="submit"
        class="inline-flex items-center gap-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs"
        onclick="return confirm('¿Deseas eliminar este reporte?')">
        <i class="fas fa-trash"></i> Eliminar
    </button>
</form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(session('mensaje'))
        <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            {{ session('mensaje') }}
        </div>
    @endif
</div>
@endsection