<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm; /* Márgenes: arriba, derecha, abajo, izquierda */
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            text-align: justify;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo img {
            max-height: 80px;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

{{-- 🔹 Logo institucional --}}
<div class="logo">
    <img src="{{ public_path('images/logo-praga.png') }}" alt="Logo Inversiones Praga">
</div>

<div class="titulo">CONTRATO PRIVADO DE PRÉSTAMO CON GARANTÍA PRENDARIA</div>

<p>
Nosotros, <strong>{{ $prestamo->cliente->nombre_completo }}</strong>, mayor de edad, estado civil <strong>{{ $prestamo->cliente->estado_civil }}</strong>, 
profesión u oficio <strong>{{ $prestamo->cliente->profesion }}</strong>, nacionalidad hondureña y con domicilio en 
<strong>{{ $prestamo->cliente->domicilio }}</strong>, ciudad de <strong>{{ $prestamo->cliente->ciudad }}</strong>, 
Departamento de <strong>{{ $prestamo->cliente->departamento }}</strong>, con Documento Nacional de Identidad No. 
<strong>{{ $prestamo->cliente->identificacion }}</strong>, actuando en mi condición personal, quien en adelante se denominará EL DEUDOR; 
y, <strong>DIEGO ENRIQUE SORIANO AGUILAR</strong>, mayor de edad, soltero, hondureño y de este domicilio, actuando en mi condición de 
Gerente General de INVERSIONES PRAGA SOCIEDAD ANÓNIMA que en adelante se conocerá como EL ACREEDOR; 
hemos convenido en celebrar y como al efecto celebramos el presente CONTRATO DE PRÉSTAMO sujeto a las siguientes estipulaciones:
</p>

<p><strong>PRIMERO. Información:</strong> Declara EL DEUDOR, que previo a la suscripción del presente contrato ha recibido a su satisfacción por parte del acreedor, la información relacionada con el presente contrato de préstamo, intereses, comisiones pactadas, así como las consecuencias por el incumplimiento de la obligación.</p>

@php
    // Calcular cuota mensual según frecuencia real
    $frecuencia = strtolower($prestamo->periodo); // <-- usamos 'periodo'
    if ($frecuencia == 'semanal') {
        $cuotaMensual = $montoCuota * 4; // 4 semanas en un mes
    } elseif ($frecuencia == 'quincenal') {
        $cuotaMensual = $montoCuota * 2; // 2 quincenas en un mes
    } else {
        $cuotaMensual = $montoCuota; // mensual
    }
@endphp

<p>
<strong>SEGUNDO. Plazo:</strong> Es entendido que el plazo de pago de la cantidad de 
<strong>{{ number_format($prestamo->valor_prestamo, 2) }}</strong> Lempiras (Lps. 
<strong>{{ number_format($prestamo->valor_prestamo, 2) }}</strong>) recibida en calidad de préstamo, será de 
<strong>{{ $prestamo->plazo }}</strong> meses en cuotas de 
<strong>L. {{ number_format($cuotaMensual, 2) }}</strong> MENSUAL; comenzando el 
<strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d') }}</strong> de 
<strong>{{ \Carbon\Carbon::parse($fechaInicio)->translatedFormat('F') }}</strong> de 
<strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('Y') }}</strong> al 
<strong>{{ \Carbon\Carbon::parse($fechaUltimaCuota)->format('d') }}</strong> de 
<strong>{{ \Carbon\Carbon::parse($fechaUltimaCuota)->translatedFormat('F') }}</strong> de 
<strong>{{ \Carbon\Carbon::parse($fechaUltimaCuota)->format('Y') }}</strong> conforme al presente contrato y al plan de pago suscrito.
</p>


<p><strong>TERCERO. Disposición y condiciones del crédito:</strong> Las partes convienen que el monto total del crédito no incluye comisiones, intereses ordinarios, accesorios e impuestos y/o gastos que EL DEUDOR debe pagar a INVERSIONES PRAGA. El importe del crédito, EL DEUDOR realizará los pagos mediante cualquiera de las siguientes formas: 1) La entrega de efectivo en las cajas de INVERSIONES PRAGA; 2) cualquier otra forma o medio de disposición que EL ACREEDOR establezca, autorice o acepte en el futuro; dichas disposiciones estarán sujetas a las posibilidades de INVERSIONES PRAGA.</p>

<p><strong>CUARTO. Pagos:</strong> EL DEUDOR se obliga a restituir a INVERSIONES PRAGA, el monto del crédito dispuesto en las condiciones pactadas, efectuando el pago de esta manera: a) en efectivo, b) con cualquier otro medio de pago que disponga EL ACREEDOR.</p>

<p><strong>QUINTO:</strong> La falta de pago de cualquier cuota de capital o intereses por parte de EL DEUDOR, y habiendo emplazado la nota de cobro y este no cumple con la obligación adquirida, facultará a EL ACREEDOR a exigir el pago del total de la obligación adeudada, aunque la misma no se encuentre vencida en su totalidad de conformidad a los términos establecidos en el presente contrato, y en los casos de prescripción de plazo, EL ACREEDOR tendrá la facultad de proceder a la recuperación de la prenda puesta en garantía.</p>

<p><strong>SEXTO. De la mora:</strong> La mora ocasionada por la falta de pago de cualquier cuota de capital o intereses convenidos, por parte de EL DEUDOR, facultará a EL ACREEDOR a realizar acciones administrativas, extrajudiciales o judiciales para exigir el pago del total de la obligación adeudada <br><br>
<p>
aunque la misma no se encuentre vencida en su totalidad; asimismo queda facultado para exigir el pago total de la obligación:  
1) por falta de pago de gastos pactados;  
2) cualquier otra deuda pendiente a favor de la financiera;  
3) por ejecución judicial iniciada por terceros o por la misma financiera en contra del cliente;  
4) por incumplimiento o negativa por parte de EL DEUDOR a proporcionar información requerida por EL ACREEDOR;  
5) por deterioro en los estados financieros o pérdidas de EL DEUDOR que afecten el patrimonio de este sin que se restituya el capital con aportes en dinero efectivo.  

La Certificación de Estado de Cuenta extendida por el contador de EL ACREEDOR junto con los demás documentos que amparan el crédito constituyen título ejecutivo y harán fe en juicio para establecer el saldo resultante a cargo de EL DEUDOR.
</p></p>


<p>
<strong>SÉPTIMO. Autorización extraordinaria:</strong> Es pactado y entendido por EL DEUDOR, que conforme al Artículo 55 numeral 2 y 60 de la Ley de Garantías Mobiliarias, EL ACREEDOR queda autorizado para apropiarse directamente de la totalidad o parte de los bienes garantizadores, en caso de que la garantía se pueda volver inservible al permanecer en poder del deudor, o cuando por la mora en el cumplimiento de la obligación sea evidente que no tiene EL DEUDOR capacidad para enfrentar la obligación contraída.
</p>

<p>
<strong>Mecanismos de cobro:</strong> EL DEUDOR conviene, acepta y reconoce que, en caso de existir mora en su crédito, EL ACREEDOR iniciará las acciones de cobranza establecidas en su manual interno, y podrá requerir, a su sola discreción, la contratación de los servicios de profesionales del derecho para recuperar extrajudicial o judicialmente la deuda existente, ya sea en efectivo o a través del levantamiento del artículo puesto en garantía.  

Queda entendido y aceptado por EL DEUDOR que los mecanismos de las gestiones de cobro extrajudicial y judicial que se efectúen, y que signifiquen costos, gastos y pago de honorarios, serán pagados por EL DEUDOR.  

Estos cargos por honorarios se aplicarán al cliente a partir del día siguiente al vencimiento del plazo y habiendo emplazado en legal y debida forma la nota de cobro.  

En caso de acciones de cobranza judicial, se cargará al préstamo la cantidad de mil quinientos lempiras (L. 1,500.00) en concepto de papelería, certificados de autenticidad y otros por la presentación de demanda; los honorarios legales se podrán establecer hasta un 30% o en su defecto conforme al Arancel del Profesional del Derecho, y las costas del juicio según lo establecido en el Código Procesal Civil.  

Por este acto queda enterado que el incumplimiento de sus obligaciones le genera mayores costos y deterioro a su historial crediticio contenido en las Centrales de Riesgos Privadas y en la Central de Información Crediticia.
</p>

<p>
<strong>OCTAVO. Otras condiciones:</strong>  
a) EL DEUDOR confiere a EL ACREEDOR el derecho de realizar la inspección y avalúo de bienes muebles o inmuebles a cargo de EL DEUDOR, dados en garantía durante la vigencia de este contrato, cada vez que sea requerido a través de las personas naturales o jurídicas acreditadas por la empresa.  

@php
    // Convertir el JSON a array
    $garantias = json_decode($prestamo->cliente->garantia, true) ?? [];

    // Filtrar valores vacíos o nulos
    $garantias = array_filter($garantias);
@endphp

Dichas garantías consisten en:
<ol>
    @foreach($garantias as $garantia)
        <li>{{ $garantia }}</li>
    @endforeach
</ol>

Con el propósito de asegurarse de que las condiciones patrimoniales en garantía, que EL ACREEDOR tuvo en cuenta para la aprobación y otorgamiento de este crédito, se mantienen.  

b) EL DEUDOR, desde ya, autoriza expresamente para que EL ACREEDOR proceda a recoger, recuperar o asegurar el artículo puesto en garantía para la obtención del crédito, una vez vencidos los plazos establecidos en las cláusulas que antecede.
</p>

<p>
<strong>NOVENO. MODIFICACIONES:</strong> La tasa de interés NOMINAL VARIABLE podrá ser modificada automáticamente de conformidad a las condiciones del sistema financiero nacional y/o de los proveedores de los fondos, aceptando EL DEUDOR que al efectuarse la referida modificación de la tasa de interés pactada la cuota podrá aumentar de conformidad a dicha modificación, quedando convenido que EL ACREEDOR está autorizado para cobrar y efectuar tales ajustes, previa notificación.
</p>

<p>
<strong>DÉCIMO:</strong> EL DEUDOR tendrá el derecho a la cancelación anticipada de los saldos adeudados, debiendo pagar el total pactado en este contrato, el capital más los intereses convenidos.
</p>

<p>
<strong>DÉCIMO PRIMERO:</strong> EL DEUDOR se obliga al fiel cumplimiento de las condiciones y términos antes relacionados en este contrato, así como las indicadas en la carátula del crédito, plan de pago, pagaré y cualquier otro documento que se genere a raíz de este contrato.
</p>

<p>
<strong>DÉCIMO SEGUNDO. RECLAMOS:</strong> EL DEUDOR podrá presentar reclamo a EL ACREEDOR si considera que se le está cobrando el valor de la cuota antes de la fecha o más de lo pactado en el presente contrato, con sus recibos y documentos que acrediten tal hecho.
</p>

<p>
<strong>DÉCIMO TERCERO:</strong> Manifiesta el señor <strong>DIEGO ENRIQUE SORIANO AGUILAR</strong> en su condición de Gerente General de INVERSIONES PRAGA: Que con instrucciones precisas de su representada declara ser cierto en todas sus partes lo declarado anteriormente por el señor(a) <strong>{{ $prestamo->cliente->nombre_completo }}</strong>, quien es EL DEUDOR, y por ser así lo convenido, se aceptan las condiciones y términos establecidas en el presente contrato de préstamo.
</p>

<p>
En fe de lo cual se firma este contrato en la ciudad de <strong>{{ $ciudad }}</strong>, Departamento de <strong>{{ $departamento }}</strong>, a los 
<strong>{{ $fechaFirma->format('d') }}</strong> días del mes de 
<strong>{{ $fechaFirma->translatedFormat('F') }}</strong> del año 
<strong>{{ $fechaFirma->format('Y') }}</strong>.
</p>

<br><br><br>

<table style="width:100%; font-size:12pt;">
    <tr>
        {{-- 🔹 Columna izquierda: Nombre y DNI --}}
        <td style="width:50%; vertical-align:top;">
            _________________________________________<br>
                      Nombre y EL DEUDOR<br>
             DNI: {{ $prestamo->cliente->identificacion }}
        </td>

        {{-- 🔹 Columna derecha: Firma y huella --}}
        <td style="width:50%; vertical-align:top; text-align:right;">
            _________________________________________<br>
                   Firma y huella de EL DEUDOR
        </td>
    </tr>
</table>

<br><br>

{{-- 🔹 Firma centrada del Gerente General --}}
<div style="text-align:center; font-size:12pt;">
    _________________________________________<br>
         FIRMA Y SELLO GERENTE GENERAL<br>
             RTN: 06019025150113
</div>

<br><br>

{{-- 🔹 Información institucional centrada --}}
<div style="text-align:center; font-size:12pt; font-weight:bold;">
    INVERSIONES PRAGA S.A.<br>
    Barrio el Centro, Calle del Comercio, contiguo a Inversiones Rafael<br>
    Pespire, Choluteca, Honduras.<br>
    Teléfono: 8998-2346<br>
    Correo electrónico: inv.praga2025@gmail.com<br>
    RTN: 06019025150113
</div>

</body>
</html>