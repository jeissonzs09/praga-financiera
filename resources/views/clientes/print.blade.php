<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha Cliente - {{ $cliente->nombre_completo }}</title>

    <meta name="viewport" content="width=device-width,initial-scale=1">

    <style>
        @page { size: letter; margin: 20mm 20mm; }

        html, body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 18px 12px;
            box-sizing: border-box;
        }

        header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:18px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
        }

        .logo {
            display:flex;
            align-items:center;
            gap:12px;
        }

        .logo img { height:65px; object-fit:contain; }

        h1 {
            font-size:18pt;
            margin:0;
            text-align:center;
        }

        .section {
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight:700;
            margin-bottom:8px;
            font-size:11pt;
            color:#0f172a;
        }

        .grid {
            display:grid;
            grid-template-columns: 35% 65%;
            gap:6px 12px;
            align-items:start;
            font-size:10.5pt;
        }

        .label { font-weight:700; color:#0f172a; }

        .two-col {
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:8px;
        }

        .right { text-align:right; }
        .muted { color:#6b7280; font-size:10pt; }

        .no-print { display:inline-block; margin-left:8px; }
        @media print {
            .no-print { display:none !important; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container">

    <header>
        <div class="logo">
            <img src="{{ asset('images/logo-praga.png') }}" alt="Logo Inversiones Praga">
            <div>
                <div style="font-weight:700; font-size:14pt; color:#1e3a8a;">INVERSIONES PRAGA</div>
                <div class="muted" style="font-size:9pt">Ficha del Cliente</div>
            </div>
        </div>

        <div class="muted right">
            <div>Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
            <div>Cliente ID: {{ $cliente->id_cliente ?? $cliente->id }}</div>
        </div>
    </header>

    <!-- DATOS PERSONALES -->
    <div class="section">
        <div class="section-title">Datos Personales</div>
        <div class="grid">
            <div class="label">Nombre</div><div>{{ $cliente->nombre_completo ?? '—' }}</div>
            <div class="label">Identificación</div><div>{{ $cliente->identificacion ?? '—' }}</div>
            <div class="label">Celular</div><div>{{ $cliente->celular ?? '—' }}</div>
            <div class="label">Correo</div><div>{{ $cliente->correo ?? '—' }}</div>
            <div class="label">Fecha de nacimiento</div><div>{{ $cliente->fecha_nacimiento ?? '—' }}</div>
            <div class="label">Edad</div><div>{{ $cliente->edad ?? '—' }}</div>
            <div class="label">Estado civil</div><div>{{ $cliente->estado_civil ?? '—' }}</div>
            <div class="label">Domicilio</div><div>{{ $cliente->domicilio ?? '—' }}</div>
            <div class="label">Ciudad / Departamento</div><div>{{ $cliente->ciudad ?? '—' }} / {{ $cliente->departamento ?? '—' }}</div>
            <div class="label">Dirección completa</div><div>{{ $cliente->direccion ?? '—' }}</div>
        </div>
    </div>

    <!-- DATOS DEL CÓNYUGE -->
    <div class="section">
        <div class="section-title">Datos del Cónyuge</div>
        <div class="two-col">
            <div>
                <div class="label">Nombre</div>
                <div>{{ $cliente->conyuge_nombre ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Teléfono / Celular</div>
                <div>{{ $cliente->conyuge_telefono ?? '—' }} / {{ $cliente->conyuge_celular ?? '—' }}</div>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN LABORAL -->
    <div class="section">
        <div class="section-title">Información Laboral</div>
        <div class="grid">
            <div class="label">Profesión / Negocio</div><div>{{ $cliente->profesion ?? '—' }} / {{ $cliente->negocio ?? '—' }}</div>
            <div class="label">Actividad económica</div><div>{{ $cliente->actividad_economica ?? '—' }}</div>
            <div class="label">Empresa / Cargo</div><div>{{ $cliente->empresa ?? '—' }} / {{ $cliente->cargo ?? '—' }}</div>
            <div class="label">Teléfono trabajo</div><div>{{ $cliente->telefono_trabajo ?? '—' }}</div>
            <div class="label">Ingreso mensual</div><div>L. {{ number_format($cliente->ingreso_mensual ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- REFERENCIAS -->
    <div class="section">
        <div class="section-title">Referencias</div>
        <div class="grid">
            <div class="label">Referencia 1</div>
            <div>{{ $cliente->referencia1_nombre ?? '—' }} — {{ $cliente->referencia1_telefono ?? '—' }}</div>

            <div class="label">Referencia 2</div>
            <div>{{ $cliente->referencia2_nombre ?? '—' }} — {{ $cliente->referencia2_telefono ?? '—' }}</div>
        </div>
    </div>

    <!-- ORIGEN DE INGRESOS / GARANTÍAS / DECLARACIÓN -->
    <div class="section">
        <div class="section-title">Origen de ingresos</div>
        <div>{{ is_array(json_decode($cliente->ingresos ?? '[]')) ? implode(', ', json_decode($cliente->ingresos ?? '[]')) : ($cliente->ingresos ?? '—') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Garantías</div>
        @php $garantias = json_decode($cliente->garantia ?? '[]'); @endphp
        @if($garantias && count($garantias)>0)
            <ul>
                @foreach($garantias as $g)<li>{{ $g }}</li>@endforeach
            </ul>
        @else
            <div>—</div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Declaración</div>
        <div class="muted">{{ $cliente->declaracion ?? '—' }}</div>
    </div>

    <div style="margin-top:14px; text-align:right;">
        <button onclick="window.print()" class="no-print" style="padding:8px 12px; background:#2563eb; color:white; border-radius:6px; border:none; cursor:pointer;">Imprimir</button>
        <button onclick="window.close()" class="no-print" style="padding:8px 12px; margin-left:8px; background:#6b7280; color:white; border-radius:6px; border:none; cursor:pointer;">Cerrar</button>
    </div>

</div>
</body>
</html>
