@extends('layouts.app')

@section('content')

<style>
    .form-2cols{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap:2rem;
    }
    .section-grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap:1rem;
    }
    .span-2{ grid-column: 1 / -1; }
</style>

<div class="max-w-7xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Editar Cliente</h2>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente->id_cliente) }}" method="POST" class="form-2cols" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- COLUMNA IZQUIERDA -->
        <div class="space-y-6">

            <!-- SOLICITUD -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Solicitud</h3>
            <div class="section-grid">
                <div>
                    <label class="block font-medium">Fecha de Solicitud</label>
                    <input type="date" name="fecha_solicitud" value="{{ $cliente->fecha_solicitud }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Fecha de Aprobación</label>
                    <input type="date" name="fecha_aprobacion" value="{{ $cliente->fecha_aprobacion }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="span-2">
                    <label class="block font-medium">Motivo de Crédito</label>
                    <input type="text" name="motivo_credito" value="{{ $cliente->motivo_credito }}" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <!-- DATOS PERSONALES -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Datos Personales</h3>
            <div class="section-grid">
                <div class="span-2">
                    <label class="block font-medium">Nombre completo *</label>
                    <input type="text" name="nombre_completo" value="{{ $cliente->nombre_completo }}" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block font-medium">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ $cliente->fecha_nacimiento }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Edad</label>
                    <input type="number" name="edad" value="{{ $cliente->edad }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Nacionalidad</label>
                    <input type="text" name="nacionalidad" value="{{ $cliente->nacionalidad }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Número Celular</label>
                    <input type="text" name="celular" value="{{ $cliente->celular }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Identificación No.</label>
                    <input type="text" name="identificacion" value="{{ $cliente->identificacion }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">RTN No.</label>
                    <input type="text" name="rtn" value="{{ $cliente->rtn }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Sexo</label>
                    <select name="sexo" class="w-full border rounded px-3 py-2">
                        <option {{ $cliente->sexo == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                        <option {{ $cliente->sexo == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                    </select>
                </div>
                <div class="span-2">
                    <label class="block font-medium">Domicilio</label>
                    <input type="text" name="domicilio" value="{{ $cliente->domicilio }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Ciudad</label>
                    <input type="text" name="ciudad" value="{{ $cliente->ciudad }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Departamento</label>
                    <input type="text" name="departamento" value="{{ $cliente->departamento }}" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block font-medium">Direccion Exacta</label>
                    <input type="text" name="departamento" value="{{ $cliente->direccion }}" class="w-full border rounded px-3 py-2">
                </div>

                <div class="span-2">
                    <label class="block font-medium">Estado civil</label>
                    <select name="estado_civil" class="w-full border rounded px-3 py-2">
                        @foreach(['Soltero','Casado','Viudo','Unión Libre'] as $estado)
                            <option value="{{ $estado }}" {{ $cliente->estado_civil == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium">Número de hijos</label>
                    <input type="number" name="hijos" value="{{ $cliente->hijos }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Número de hijas</label>
                    <input type="number" name="hijas" value="{{ $cliente->hijas }}" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <!-- REFERENCIAS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Referencias</h3>
            <div class="section-grid">
                <input type="text" name="referencia1_nombre" value="{{ $cliente->referencia1_nombre }}" placeholder="Referencia 1 - Nombre" class="border rounded px-3 py-2">
                <input type="text" name="referencia1_telefono" value="{{ $cliente->referencia1_telefono }}" placeholder="Referencia 1 - Teléfono" class="border rounded px-3 py-2">
            </div>
            <div class="section-grid mt-2">
                <input type="text" name="referencia2_nombre" value="{{ $cliente->referencia2_nombre }}" placeholder="Referencia 2 - Nombre" class="border rounded px-3 py-2">
                <input type="text" name="referencia2_telefono" value="{{ $cliente->referencia2_telefono }}" placeholder="Referencia 2 - Teléfono" class="border rounded px-3 py-2">
            </div>

            <!-- IDENTIDAD DEL CLIENTE -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Identidad del Cliente</h3>
            <div class="space-y-2">
                <input type="file" name="identidad_img[]" accept="image/*" multiple class="w-full border rounded px-3 py-2">
                <small class="text-gray-500">Suba ambas caras de la identidad (JPG, PNG, etc.).</small>
            </div>

            <!-- FOTOGRAFÍAS DE GARANTÍAS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Fotografías de Garantías</h3>
            <div class="space-y-2">
                <input type="file" name="fotos_garantias[]" accept="image/*" multiple class="w-full border rounded px-3 py-2">
                <small class="text-gray-500">Puede seleccionar varias imágenes (JPG, PNG, etc.).</small>
            </div>

            <!-- CONTRATOS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Contratos</h3>
@php
    // Si contrato_pdf es un JSON, lo decodifica, si no, crea un array con el único archivo
    $contratos = @json_decode($cliente->contrato_pdf, true);
    if(!$contratos) {
        $contratos = $cliente->contrato_pdf ? [$cliente->contrato_pdf] : [];
    }
@endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                @foreach($contratos as $index => $archivo)
                    <div class="border rounded p-2 bg-gray-50 text-center relative">
                        <a href="{{ asset('storage/' . $archivo) }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ basename($archivo) }}
                        </a>
                        <button type="button" onclick="eliminarContrato({{ $index }})" class="absolute top-1 right-1 text-red-600 font-bold">X</button>
                    </div>
                @endforeach
            </div>
            <input type="file" name="contrato_pdf[]" accept="application/pdf" multiple class="w-full border rounded px-3 py-2">
            <small class="text-gray-500">Puede subir más archivos PDF sin eliminar los existentes.</small>
            <input type="hidden" name="contratos_eliminados" id="contratos_eliminados" value="">

        </div>

        <!-- COLUMNA DERECHA -->
        <div class="space-y-6">

            <!-- DATOS DEL CÓNYUGE -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Datos del Cónyuge</h3>
            <div class="section-grid">
                <input type="text" name="conyuge_nombre" value="{{ $cliente->conyuge_nombre }}" placeholder="Nombre completo" class="span-2 border rounded px-3 py-2">
                <input type="text" name="conyuge_telefono" value="{{ $cliente->conyuge_telefono }}" placeholder="Teléfono de residencia" class="border rounded px-3 py-2">
                <input type="text" name="conyuge_celular" value="{{ $cliente->conyuge_celular }}" placeholder="Celular" class="border rounded px-3 py-2">
            </div>

            <!-- INFORMACIÓN LABORAL -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Información Laboral</h3>
            <div class="section-grid">
                <input type="email" name="correo" value="{{ $cliente->correo }}" placeholder="Correo electrónico" class="span-2 border rounded px-3 py-2">
                <input type="text" name="profesion" value="{{ $cliente->profesion }}" placeholder="Profesión, ocupación u oficio" class="span-2 border rounded px-3 py-2">
                <input type="text" name="negocio" value="{{ $cliente->negocio }}" placeholder="Nombre del negocio (si aplica)" class="span-2 border rounded px-3 py-2">
                <input type="text" name="actividad_economica" value="{{ $cliente->actividad_economica }}" placeholder="Giro o actividad económica del negocio" class="span-2 border rounded px-3 py-2">
                <input type="text" name="cargo" value="{{ $cliente->cargo }}" placeholder="Posición o cargo que desempeña" class="border rounded px-3 py-2">
                <input type="text" name="tipo_labor" value="{{ $cliente->tipo_labor }}" placeholder="Tiempo de laborar u operación" class="border rounded px-3 py-2">
                <input type="text" name="empresa" value="{{ $cliente->empresa }}" placeholder="Nombre de la empresa donde labora" class="span-2 border rounded px-3 py-2">
                <textarea name="direccion_empresa" placeholder="Dirección completa de la empresa" class="span-2 border rounded px-3 py-2">{{ $cliente->direccion_empresa }}</textarea>
            </div>

            <!-- ORIGEN DE INGRESOS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Origen de Ingresos</h3>
            <div class="section-grid">
                @foreach(['Salario','Negocio Propio','Renta de bienes','Socio','Pensión','Jubilación','Otros'] as $ingreso)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="ingresos[]" value="{{ $ingreso }}" class="mr-2"
                               {{ in_array($ingreso, (array) $cliente->ingresos ?? []) ? 'checked' : '' }}>
                        {{ $ingreso }}
                    </label>
                @endforeach
            </div>

            <!-- NIVEL DE INGRESOS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Nivel de Ingreso Aproximado</h3>
            <div class="section-grid">
                @foreach(['0-3','4-6','7-10','11-20','21-50','50 en adelante'] as $rango)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="nivel_ingreso[]" value="{{ $rango }}" class="mr-2"
                               {{ in_array($rango, (array) $cliente->nivel_ingreso ?? []) ? 'checked' : '' }}>
                        {{ $rango }}
                    </label>
                @endforeach
            </div>

            <!-- DESCRIPCIÓN DE GARANTÍAS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Descripción de Garantías</h3>
            <div class="space-y-2">
                @php
                    $garantias = json_decode($cliente->garantia ?? '[]', true);
                @endphp
                @for ($i = 0; $i < 5; $i++)
                    <input type="text" name="garantia[]" placeholder="{{ $i + 1 }}." value="{{ $garantias[$i] ?? '' }}" class="w-full border rounded px-3 py-2">
                @endfor
            </div>

            <!-- DECLARACIÓN -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Declaración</h3>
            <textarea name="declaracion" rows="4" class="w-full border rounded px-3 py-2">{{ $cliente->declaracion }}</textarea>

            <!-- BOTONES -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('clientes.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm">
                   Cancelar
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm">
                   Actualizar Cliente
                </button>
            </div>

        </div>
    </form>
</div>

<script>
let eliminados = [];
function eliminarContrato(index){
    eliminados.push(index);
    document.getElementById('contratos_eliminados').value = JSON.stringify(eliminados);
    event.target.parentElement.style.display = 'none';
}
</script>

@endsection