@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-semibold text-gray-700 mb-6">
        Detalles del Cliente: {{ $cliente->nombre_completo }}
    </h2>

    <!-- Datos Personales -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Datos Personales</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <p><strong>Fecha de nacimiento:</strong> {{ $cliente->fecha_nacimiento }}</p>
        <p><strong>Edad:</strong> {{ $cliente->edad }}</p>
        <p><strong>Nacionalidad:</strong> {{ $cliente->nacionalidad }}</p>
        <p><strong>Celular:</strong> {{ $cliente->celular }}</p>
        <p><strong>Identificación:</strong> {{ $cliente->identificacion }}</p>
        <p><strong>RTN:</strong> {{ $cliente->rtn }}</p>
        <p><strong>Sexo:</strong> {{ $cliente->sexo }}</p>
        <p><strong>Estado civil:</strong> {{ $cliente->estado_civil }}</p>
        <p class="md:col-span-2"><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
    </div>

    <!-- Datos del Cónyuge -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Datos del Cónyuge</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <p><strong>Nombre:</strong> {{ $cliente->conyuge_nombre }}</p>
        <p><strong>Teléfono:</strong> {{ $cliente->conyuge_telefono }}</p>
        <p><strong>Celular:</strong> {{ $cliente->conyuge_celular }}</p>
    </div>

    <!-- Información Laboral -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Información Laboral</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
        <p><strong>Hijos:</strong> {{ $cliente->hijos }}</p>
        <p class="md:col-span-2"><strong>Profesión:</strong> {{ $cliente->profesion }}</p>
        <p class="md:col-span-2"><strong>Negocio:</strong> {{ $cliente->negocio }}</p>
        <p class="md:col-span-2"><strong>Actividad Económica:</strong> {{ $cliente->actividad_economica }}</p>
        <p><strong>Cargo:</strong> {{ $cliente->cargo }}</p>
        <p><strong>Tipo de labor:</strong> {{ $cliente->tipo_labor }}</p>
        <p class="md:col-span-2"><strong>Dirección empresa:</strong> {{ $cliente->direccion_empresa }}</p>
    </div>

    <!-- Referencias -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Referencias</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <p><strong>Teléfono trabajo:</strong> {{ $cliente->telefono_trabajo }}</p>
        <p><strong>Referencia 1:</strong> {{ $cliente->referencia1 }}</p>
        <p><strong>Referencia 2:</strong> {{ $cliente->referencia2 }}</p>
    </div>

    <!-- Origen de ingresos -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Origen de ingresos</h3>
    <p class="mb-6">{{ is_array($cliente->ingresos) ? implode(', ', $cliente->ingresos) : $cliente->ingresos }}</p>

    <!-- Declaración -->
    <h3 class="text-lg font-semibold text-praga border-b pb-2 mb-4">Declaración</h3>
    <p class="mb-6 whitespace-pre-line">{{ $cliente->declaracion }}</p>

    <!-- Botón volver -->
    <a href="{{ route('clientes.index') }}" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow text-sm">
       Volver
    </a>
</div>
@endsection