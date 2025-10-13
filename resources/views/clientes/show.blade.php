@extends('layouts.app')

@section('content')
<style>
    /* Estilos específicos para impresión */
    @media print {
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }

        /* Ocultar elementos innecesarios */
        .no-print, button, a, .sidebar, .topbar {
            display: none !important;
        }

        /* Ajustar ancho de contenido */
        .max-w-6xl {
            width: 100%;
            max-width: none;
        }

        /* Evitar quiebre de grids importantes */
        .bg-white { page-break-inside: avoid; }

        /* Ajustar espacios */
        .p-6 { padding: 1rem; }
        .rounded-lg { border-radius: 0.5rem; }

        /* Títulos más grandes para impresión */
        h3.text-lg { font-size: 14pt; }

        /* Evitar que se corten listas y grids */
        ul, .grid { page-break-inside: avoid; }

        /* Asegurar que se muestre solo el contenido */
        .printable { display: block; }
    }
</style>

    <!-- --- BOTONES --- -->
    <div class="flex justify-end gap-3 no-print mt-4">
<a href="{{ route('clientes.imprimir', $cliente->id_cliente) }}" 
   target="_blank"
   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
   Imprimir Cliente
</a>
    </div>

<div class="max-w-6xl mx-auto space-y-6 printable">

    <!-- --- SOLICITUD / CRÉDITO --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Solicitud / Crédito</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <p><strong>Fecha de solicitud:</strong> {{ $cliente->fecha_solicitud }}</p>
            <p><strong>Fecha de aprobación:</strong> {{ $cliente->fecha_aprobacion }}</p>
            <p class="md:col-span-3"><strong>Motivo crédito:</strong> {{ $cliente->motivo_credito }}</p>
        </div>
    </div>

    <!-- --- DATOS PERSONALES --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Datos Personales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <p><strong>Nombre:</strong> {{ $cliente->nombre_completo }}</p>
            <p><strong>Fecha de nacimiento:</strong> {{ $cliente->fecha_nacimiento }}</p>
            <p><strong>Edad:</strong> {{ $cliente->edad }}</p>
            <p><strong>Nacionalidad:</strong> {{ $cliente->nacionalidad }}</p>
            <p><strong>Celular:</strong> {{ $cliente->celular }}</p>
            <p><strong>Teléfono residencia:</strong> {{ $cliente->telefono_residencia }}</p>
            <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
            <p><strong>Identificación:</strong> {{ $cliente->identificacion }}</p>
            <p><strong>Tipo de identificación:</strong> 
                {{ is_array(json_decode($cliente->tipo_identificacion ?? '[]')) ? implode(', ', json_decode($cliente->tipo_identificacion ?? '[]')) : $cliente->tipo_identificacion }}
            </p>
            <p><strong>RTN:</strong> {{ $cliente->rtn }}</p>
            <p><strong>Sexo:</strong> {{ $cliente->sexo }}</p>
            <p><strong>Estado civil:</strong> {{ $cliente->estado_civil }}</p>
            <p><strong>Hijos:</strong> {{ $cliente->hijos }}</p>
            <p><strong>Hijas:</strong> {{ $cliente->hijas }}</p>
            <p><strong>Domicilio:</strong> {{ $cliente->domicilio }}</p>
            <p><strong>Ciudad:</strong> {{ $cliente->ciudad }}</p>
            <p><strong>Departamento:</strong> {{ $cliente->departamento }}</p>
            <p class="md:col-span-3"><strong>Dirección Completa:</strong> {{ $cliente->direccion }}</p>
        </div>
    </div>

    <!-- --- DATOS DEL CÓNYUGE --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Datos del Cónyuge</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <p><strong>Nombre:</strong> {{ $cliente->conyuge_nombre }}</p>
            <p><strong>Teléfono:</strong> {{ $cliente->conyuge_telefono }}</p>
            <p><strong>Celular:</strong> {{ $cliente->conyuge_celular }}</p>
        </div>
    </div>

    <!-- --- INFORMACIÓN LABORAL --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Información Laboral</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <p class="md:col-span-3"><strong>Profesión:</strong> {{ $cliente->profesion }}</p>
            <p class="md:col-span-3"><strong>Negocio:</strong> {{ $cliente->negocio }}</p>
            <p class="md:col-span-3"><strong>Actividad Económica:</strong> {{ $cliente->actividad_economica }}</p>
            <p><strong>Cargo:</strong> {{ $cliente->cargo }}</p>
            <p><strong>Tipo de labor:</strong> {{ $cliente->tipo_labor }}</p>
            <p><strong>Empresa:</strong> {{ $cliente->empresa }}</p>
            <p class="md:col-span-3"><strong>Dirección empresa:</strong> {{ $cliente->direccion_empresa }}</p>
            <p><strong>Teléfono trabajo:</strong> {{ $cliente->telefono_trabajo }}</p>
            <p><strong>Ingreso Mensual Aproximado:</strong> L. {{ number_format($cliente->ingreso_mensual ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- --- REFERENCIAS --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Referencias</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Referencia 1 - Nombre:</strong> {{ $cliente->referencia1_nombre }}</p>
            <p><strong>Referencia 1 - Teléfono:</strong> {{ $cliente->referencia1_telefono }}</p>
            <p><strong>Referencia 2 - Nombre:</strong> {{ $cliente->referencia2_nombre }}</p>
            <p><strong>Referencia 2 - Teléfono:</strong> {{ $cliente->referencia2_telefono }}</p>
        </div>
    </div>

    <!-- --- INGRESOS --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Origen de ingresos</h3>
        <p>
            {{ is_array(json_decode($cliente->ingresos ?? '[]')) ? implode(', ', json_decode($cliente->ingresos ?? '[]')) : $cliente->ingresos }}
        </p>
    </div>

    <!-- --- GARANTÍAS --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Garantías</h3>
        @php
            $garantias = json_decode($cliente->garantia ?? '[]');
        @endphp
        @if($garantias && count($garantias) > 0)
            <ul class="list-disc list-inside space-y-1">
                @foreach($garantias as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p>No hay garantías registradas.</p>
        @endif
    </div>

<!-- --- ARCHIVOS --- -->
<div class="bg-white p-6 rounded-lg shadow space-y-4">
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Archivos</h3>

    <!-- Identidad -->
    @php
        $identidades = json_decode($cliente->identidad_img, true) ?: [];
    @endphp
    @if(count($identidades) > 0)
        <div>
            <h4 class="font-semibold mb-2">Identidad</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($identidades as $img)
                    @if($img)
                    <div class="border rounded p-2">
                        <img src="{{ asset('storage/' . $img) }}" alt="Identidad" class="w-full h-40 object-cover rounded">
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Garantías (imágenes) -->
    @php
        $garantiasImgs = json_decode($cliente->fotos_garantias, true) ?: [];
    @endphp
    @if(count($garantiasImgs) > 0)
        <div>
            <h4 class="font-semibold mb-2">Fotos de Garantías</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($garantiasImgs as $img)
                    @if($img)
                    <div class="border rounded p-2">
                        <img src="{{ asset('storage/' . $img) }}" alt="Garantía" class="w-full h-40 object-cover rounded">
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Contrato PDF -->
    @php
        $contratos = json_decode($cliente->contrato_pdf, true) ?: [];
    @endphp
    @if(count($contratos) > 0)
        <div>
            <h4 class="font-semibold mb-2">Contrato PDF</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($contratos as $archivo)
                    @if($archivo)
                    <div class="border rounded p-4 bg-gray-50 text-center">
                        <a href="{{ asset('storage/' . $archivo) }}" target="_blank">
                            {{ basename($archivo) }}
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>


    <!-- --- DECLARACIÓN --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Declaración</h3>
        <p class="whitespace-pre-line">{{ $cliente->declaracion }}</p>
    </div>

    <!-- --- BOTONES --- -->
    <div class="flex justify-end gap-3 no-print mt-4">
        <a href="{{ route('clientes.index') }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow text-sm">
           Volver
        </a>
    </div>

</div>
@endsection