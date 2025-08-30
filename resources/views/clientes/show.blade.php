@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

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
            <p><strong>Identificación:</strong> {{ $cliente->identificacion }}</p>
            <p><strong>Tipo de identificación:</strong> 
                {{ is_array(json_decode($cliente->tipo_identificacion ?? '[]')) ? implode(', ', json_decode($cliente->tipo_identificacion ?? '[]')) : $cliente->tipo_identificacion }}
            </p>
            <p><strong>RTN:</strong> {{ $cliente->rtn }}</p>
            <p><strong>Sexo:</strong> {{ $cliente->sexo }}</p>
            <p><strong>Estado civil:</strong> {{ $cliente->estado_civil }}</p>
            <p class="md:col-span-3"><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
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
            <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
            <p><strong>Hijos:</strong> {{ $cliente->hijos }}</p>
            <p><strong>Hijas:</strong> {{ $cliente->hijas }}</p>
            <p class="md:col-span-3"><strong>Profesión:</strong> {{ $cliente->profesion }}</p>
            <p class="md:col-span-3"><strong>Negocio:</strong> {{ $cliente->negocio }}</p>
            <p class="md:col-span-3"><strong>Actividad Económica:</strong> {{ $cliente->actividad_economica }}</p>
            <p><strong>Cargo:</strong> {{ $cliente->cargo }}</p>
            <p><strong>Tipo de labor:</strong> {{ $cliente->tipo_labor }}</p>
            <p><strong>Empresa:</strong> {{ $cliente->empresa }}</p>
            <p class="md:col-span-3"><strong>Dirección empresa:</strong> {{ $cliente->direccion_empresa }}</p>
            <p><strong>Teléfono trabajo:</strong> {{ $cliente->telefono_trabajo }}</p>
            <p><strong>Nivel de ingresos:</strong> 
                {{ is_array(json_decode($cliente->nivel_ingreso ?? '[]')) ? implode(', ', json_decode($cliente->nivel_ingreso ?? '[]')) : $cliente->nivel_ingreso }}
            </p>
        </div>
    </div>

    <!-- --- REFERENCIAS --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Referencias</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <p><strong>Referencia 1:</strong> {{ $cliente->referencia1 }}</p>
            <p><strong>Referencia 2:</strong> {{ $cliente->referencia2 }}</p>
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
    @if($cliente->identidad_img)
    <div>
        <h4 class="font-semibold mb-2">Identidad</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach(json_decode($cliente->identidad_img) as $img)
                <div class="border rounded p-2">
                    <img src="{{ asset('storage/' . $img) }}" alt="Identidad" class="w-full h-40 object-cover rounded">
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Garantías (imágenes) -->
    @if($cliente->fotos_garantias)
    <div>
        <h4 class="font-semibold mb-2">Fotos de Garantías</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach(json_decode($cliente->fotos_garantias) as $img)
                <div class="border rounded p-2">
                    <img src="{{ asset('storage/' . $img) }}" alt="Garantía" class="w-full h-40 object-cover rounded">
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Contrato PDF -->
    @if($cliente->contrato_pdf)
    <div>
        <h4 class="font-semibold mb-2">Contrato PDF</h4>
        <div class="border rounded p-4 bg-gray-50 text-center">
            <a href="{{ asset('storage/' . $cliente->contrato_pdf) }}" target="_blank" class="text-blue-600 hover:underline">
                Ver Contrato PDF
            </a>
        </div>
    </div>
    @endif
</div>

    <!-- --- DECLARACIÓN --- -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Declaración</h3>
        <p class="whitespace-pre-line">{{ $cliente->declaracion }}</p>
    </div>

    <!-- --- BOTÓN VOLVER --- -->
    <div class="text-right">
        <a href="{{ route('clientes.index') }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow text-sm">
           Volver
        </a>
    </div>

</div>
@endsection