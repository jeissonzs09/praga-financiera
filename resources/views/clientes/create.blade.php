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
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Nuevo Cliente</h2>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.store') }}" method="POST" class="form-2cols" enctype="multipart/form-data">
        @csrf

        <!-- Columna Izquierda -->
        <div class="space-y-6">

            <!-- ENCABEZADO -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Solicitud</h3>
            <div class="section-grid">
                <div>
                    <label class="block font-medium">Fecha de Solicitud</label>
                    <input type="date" name="fecha_solicitud" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium">Fecha de Aprobación</label>
                    <input type="date" name="fecha_aprobacion" class="w-full border rounded px-3 py-2">
                </div>
                <div class="span-2">
                    <label class="block font-medium">Motivo de Crédito</label>
                    <input type="text" name="motivo_credito" class="w-full border rounded px-3 py-2">
                </div>
            </div>

<!-- DATOS PERSONALES -->
<h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Datos Personales</h3>
<div class="section-grid">
    <div class="span-2">
        <label class="block font-medium">Nombre completo *</label>
        <input type="text" name="nombre_completo" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
        <label class="block font-medium">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">Edad</label>
        <input type="number" name="edad" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">Nacionalidad</label>
        <input type="text" name="nacionalidad" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">Número Celular</label>
        <input type="text" name="celular" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block font-medium">Identificación No.</label>
        <input type="text" name="identificacion" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">RTN No.</label>
        <input type="text" name="rtn" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">Sexo</label>
        <select name="sexo" class="w-full border rounded px-3 py-2">
            <option>Masculino</option>
            <option>Femenino</option>
        </select>
    </div>

    <!-- Tipo Identificación -->
    <div class="span-2">
        <label class="block font-medium">Tipo de Identificación</label>
        <div class="flex flex-wrap gap-4 mt-1">
            @foreach(['Identidad','Pasaporte','Carnet de residencia','RTN'] as $tipo)
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tipo_identificacion[]" value="{{ $tipo }}" class="mr-2">
                    {{ $tipo }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="span-2">
        <label class="block font-medium">Estado civil</label>
        <select name="estado_civil" class="w-full border rounded px-3 py-2">
            <option>Soltero</option>
            <option>Casado</option>
            <option>Viudo</option>
            <option>Unión Libre</option>
        </select>
    </div>

    <!-- Hijos / Hijas -->
    <div>
        <label class="block font-medium">Número de hijos</label>
        <input type="number" name="hijos" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block font-medium">Número de hijas</label>
        <input type="number" name="hijas" class="w-full border rounded px-3 py-2">
    </div>

    <div class="span-2">
        <label class="block font-medium">Dirección completa de residencia</label>
        <textarea name="direccion" class="w-full border rounded px-3 py-2"></textarea>
    </div>

    <div>
        <label class="block font-medium">Teléfono de residencia</label>
        <input type="text" name="telefono_residencia" class="w-full border rounded px-3 py-2">
    </div>
</div>

<!-- REFERENCIAS -->
<h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Referencias</h3>
<div id="referencias-container" class="space-y-3">
    <div class="section-grid referencia-item">
        <input type="text" name="referencias[0][nombre]" placeholder="Nombre de la referencia" class="border rounded px-3 py-2">
        <input type="text" name="referencias[0][telefono]" placeholder="Teléfono" class="border rounded px-3 py-2">
    </div>
</div>
<button type="button" id="add-referencia" class="mt-2 bg-green-600 text-white px-3 py-1 rounded">+ Agregar referencia</button>

<script>
    let refIndex = 1;
    document.getElementById('add-referencia').addEventListener('click', function () {
        const container = document.getElementById('referencias-container');
        const newRef = document.createElement('div');
        newRef.classList.add('section-grid','referencia-item');
        newRef.innerHTML = `
            <input type="text" name="referencias[${refIndex}][nombre]" placeholder="Nombre de la referencia" class="border rounded px-3 py-2">
            <input type="text" name="referencias[${refIndex}][telefono]" placeholder="Teléfono" class="border rounded px-3 py-2">
        `;
        container.appendChild(newRef);
        refIndex++;
    });
</script>

<!-- IDENTIDAD DEL CLIENTE -->
<h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Identidad del Cliente</h3>
<div class="space-y-2">
    <input type="file" name="identidad_img[]" accept="image/*" multiple class="w-full border rounded px-3 py-2">
    <small class="text-gray-500">Suba ambas caras de la identidad (JPG, PNG, etc.).</small>
</div>


                        <!-- SUBIDA DE FOTOGRAFÍAS DE GARANTÍAS -->
<h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Fotografías de Garantías</h3>
<div class="space-y-2">
    <input type="file" name="fotos_garantias[]" accept="image/*" multiple class="w-full border rounded px-3 py-2">
    <small class="text-gray-500">Puede seleccionar varias imágenes (formatos JPG, PNG, etc.).</small>
</div>

<!-- SUBIDA DE CONTRATO EN PDF -->
<h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Contrato</h3>
<div class="space-y-2">
    <input type="file" name="contrato_pdf" accept="application/pdf" class="w-full border rounded px-3 py-2">
    <small class="text-gray-500">Solo formato PDF.</small>
</div>
        </div>

        

        <!-- Columna Derecha -->
        <div class="space-y-6">

            <!-- DATOS DEL CÓNYUGE -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Datos del Cónyuge</h3>
            <div class="section-grid">
                <input type="text" name="conyuge_nombre" placeholder="Nombre completo" class="span-2 border rounded px-3 py-2">
                <input type="text" name="conyuge_telefono" placeholder="Teléfono de residencia" class="border rounded px-3 py-2">
                <input type="text" name="conyuge_celular" placeholder="Celular" class="border rounded px-3 py-2">
            </div>

            <!-- INFORMACIÓN LABORAL -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Información Laboral</h3>
            <div class="section-grid">
                <input type="email" name="correo" placeholder="Correo electrónico" class="span-2 border rounded px-3 py-2">
                <input type="text" name="profesion" placeholder="Profesión, ocupación u oficio" class="span-2 border rounded px-3 py-2">
                <input type="text" name="negocio" placeholder="Nombre del negocio (si aplica)" class="span-2 border rounded px-3 py-2">
                <input type="text" name="actividad_economica" placeholder="Giro o actividad económica del negocio" class="span-2 border rounded px-3 py-2">
                <input type="text" name="cargo" placeholder="Posición o cargo que desempeña" class="border rounded px-3 py-2">
                <input type="text" name="tipo_labor" placeholder="Tiempo de laborar u operación" class="border rounded px-3 py-2">
                <input type="text" name="empresa" placeholder="Nombre de la empresa donde labora" class="span-2 border rounded px-3 py-2">
                <textarea name="direccion_empresa" placeholder="Dirección completa de la empresa" class="span-2 border rounded px-3 py-2"></textarea>
            </div>

            <!-- ORIGEN DE INGRESOS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Origen de Ingresos</h3>
            <div class="section-grid">
                @foreach(['Salario','Negocio Propio','Renta de bienes','Socio','Pensión','Jubilación','Otros'] as $ingreso)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="ingresos[]" value="{{ $ingreso }}" class="mr-2">
                        {{ $ingreso }}
                    </label>
                @endforeach
            </div>

            <!-- NIVEL DE INGRESOS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Nivel de Ingreso Aproximado</h3>
            <div class="section-grid">
                @foreach(['0-3','4-6','7-10','11-20','21-50','50 en adelante'] as $rango)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="nivel_ingreso[]" value="{{ $rango }}" class="mr-2">
                        {{ $rango }}
                    </label>
                @endforeach
            </div>

            <!-- DESCRIPCIÓN DE GARANTÍAS -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Descripción de Garantías</h3>
            <div class="space-y-2">
                @for ($i = 1; $i <= 5; $i++)
                    <input type="text" name="garantia[]" placeholder="{{ $i }}." class="w-full border rounded px-3 py-2">
                @endfor
            </div>


            <!-- DECLARACIÓN -->
            <h3 class="text-lg font-semibold text-praga border-b border-gray-200 pb-2 mb-4">Declaración</h3>
            <textarea name="declaracion" rows="4" class="w-full border rounded px-3 py-2">
Declaro que toda la información ofrecida es verdadera, de no ser así, esta empresa estará en la capacidad de proceder en mi contra de la manera que crea pertinente.
            </textarea>

            <!-- BOTONES -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('clientes.index') }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm">
                   Cancelar
                </a>
                <button type="submit"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm">
                   Guardar Cliente
                </button>
            </div>
        </div>
    </form>
</div>
@endsection