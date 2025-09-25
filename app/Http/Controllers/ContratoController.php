<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\NumeroHelper;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index()
{
    $query = Prestamo::with('cliente')->latest();

    if (request('buscar')) {
        $query->whereHas('cliente', function ($q) {
            $q->where('nombre_completo', 'like', '%' . request('buscar') . '%');
        });
    }

    if (request('fecha')) {
        $query->whereDate('fecha_inicio', request('fecha'));
    }

    if (request('estado')) {
        $query->where('estado', request('estado'));
    }

    $contratos = $query->paginate(10)->withQueryString();

    return view('contratos.index', compact('contratos'));
}


    private function generarPlan(Prestamo $prestamo)
{
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal = $capitalTotal * ($tasa / 100) * $plazoMeses;
    $interesPorCuota = round($interesTotal / $numeroCuotas, 2);

    $saldo  = $capitalTotal;
    $inicio = \Carbon\Carbon::parse($prestamo->fecha_inicio);
    $cuotas = [];

    for ($i = 1; $i <= $numeroCuotas; $i++) {
        $vence = match($frecuencia) {
            'quincenal' => $inicio->copy()->addDays(15 * $i),
            'semanal'   => $inicio->copy()->addDays(7 * $i),
            default     => $inicio->copy()->addMonths($i)
        };

        $saldo -= $capitalPorCuota;

        $cuota = [
            'nro'       => $i,
            'vence'     => $vence->format('Y-m-d'),
            'capital'   => $capitalPorCuota,
            'interes'   => $interesPorCuota,
            'recargos'  => 0,
            'mora'      => 0,
            'total'     => $capitalPorCuota + $interesPorCuota,
            'estado'    => 'Pendiente',
            'saldo'     => round($saldo, 2),
            'es_tardio' => false // siempre booleano
        ];

        // 🔹 Solo si es un préstamo real, buscar pago y marcar tardío
        if ($prestamo->exists) {
            $pago = \App\Models\Pago::where('prestamo_id', $prestamo->id)
                ->where('cuota_numero', $i)
                ->first();

            if ($pago) {
                $cuota['estado'] = 'Pagada';

                // Comparación segura de fechas
                $venceDate = \Carbon\Carbon::createFromFormat('Y-m-d', $cuota['vence']);
                $fechaPago = \Carbon\Carbon::parse($pago->created_at);

                // Si el pago fue después de la fecha de vencimiento → tardío
                $cuota['es_tardio'] = $fechaPago->gt($venceDate);
            }
        }

        $cuotas[] = $cuota;
    }

    return $cuotas;
}

public function generarPdf(Prestamo $prestamo)
{
         // 🔹 Asegurarte de que las fechas salgan en español
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
    \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

$plan = $this->generarPlan($prestamo);

// Tomar la primera cuota del plan
$primerCuota = $plan[0]['total'];
$montoCuota = $plan[0]['total'];

// Calcular cuota mensual según frecuencia
$frecuencia = strtolower($prestamo->periodo);
$cuotaMensual = match($frecuencia) {
    'semanal'   => $primerCuota * 4,
    'quincenal' => $primerCuota * 2,
    default     => $primerCuota,
};

$fechaPrimeraCuota = $plan[0]['vence'];
$fechaUltimaCuota  = end($plan)['vence'];

// Pasar $cuotaMensual a la vista en lugar de $montoCuota

\Carbon\Carbon::setLocale('es');

$pdf = Pdf::loadView('contratos.plantilla', compact(
    'prestamo',
    'cuotaMensual',
    'fechaPrimeraCuota',
    'fechaUltimaCuota',
    'montoCuota'
))->setPaper('letter');

    return $pdf->download("Contrato-Prestamo-{$prestamo->id}.pdf");
}


public function generarPagare(Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

    // 🔹 Generar el plan de cuotas (igual que en generarPdf)
    $plan = $this->generarPlan($prestamo);
    $fechaUltimaCuota = end($plan)['vence']; // <-- aquí obtienes la última cuota

    // ✅ Convertir valor a letras
    $letras = NumeroHelper::convertirALetras($prestamo->valor_prestamo);

    // ✅ Pasar todas las variables necesarias a la vista
    return \Barryvdh\DomPDF\Facade\Pdf::loadView('contratos.pagare', compact(
        'prestamo',
        'letras',
        'fechaUltimaCuota' // <-- ahora ya existe en la vista
    ))
    ->setPaper('letter')
    ->download("Pagare-Prestamo-{$prestamo->id}.pdf");
}


public function mostrarPago(Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

    // 🔹 Aquí generamos el texto en letras
    $letras = \App\Helpers\NumeroHelper::convertirALetras($prestamo->valor_prestamo);

    return view('contratos.pago', compact('prestamo', 'letras'));
}

public function generarDeclaracion(Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');

    $prestamo->load('cliente');

    return pdf::loadView('contratos.declaracion', compact('prestamo'))
        ->setPaper('letter')
        ->download("Declaracion-Garantias-{$prestamo->id}.pdf");
}

public function generarAutorizacion(Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

    return pdf::loadView('contratos.autorizacion', compact('prestamo'))
        ->setPaper('letter')
        ->download("Autorizacion-Credito-{$prestamo->id}.pdf");
}

public function generarPdfModal(Request $request, Prestamo $prestamo)
{
    // 🔹 Configurar idioma español
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
    \Carbon\Carbon::setLocale('es');

    // 🔹 Validación del modal
    $request->validate([
        'fecha_inicio' => 'required|date',
        'ciudad'       => 'required|string',
        'departamento' => 'required|string',
        'fecha_firma'  => 'required|date',
    ]);

    $prestamo->load('cliente');
    $plan = $this->generarPlan($prestamo);

    $montoCuota      = $plan[0]['total'] ?? 0;
    $fechaUltimaCuota = end($plan)['vence'] ?? now();

    $fechaInicio  = $request->fecha_inicio;
    $ciudad       = $request->ciudad;
    $departamento = $request->departamento;
    $fechaFirma   = \Carbon\Carbon::parse($request->fecha_firma);

    $pdf = Pdf::loadView('contratos.plantilla', compact(
        'prestamo',
        'montoCuota',
        'fechaInicio',
        'fechaUltimaCuota',
        'ciudad',
        'departamento',
        'fechaFirma'
    ))->setPaper('letter');

    // 🔹 Descargar PDF con nombre personalizado
    return $pdf->download(
        "Contrato de Prestamo PRAGA - {$prestamo->cliente->nombre_completo} - {$fechaFirma->format('d-m-Y')}.pdf"
    );
}

public function generarPagareModal(Request $request, Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');

    // Validar los datos enviados desde el modal
    $data = $request->validate([
        'fechaFirma' => 'required|date',
        'ciudadFirma' => 'required|string',
        'departamentoFirma' => 'required|string',
    ]);

    // Cargar relación cliente
    $prestamo->load('cliente');

    // 🔹 Generar plan de cuotas para obtener la última fecha
    $plan = $this->generarPlan($prestamo);
    $fechaUltimaCuota = end($plan)['vence'];

    // ✅ Convertir valor a letras usando tu helper
    $letras = NumeroHelper::convertirALetras($prestamo->valor_prestamo);

    // ✅ Generar PDF y descargar con nombre personalizado
    return \Barryvdh\DomPDF\Facade\Pdf::loadView('contratos.pagare', [
        'prestamo' => $prestamo,
        'letras' => $letras,
        'fechaUltimaCuota' => $fechaUltimaCuota,
        'fechaFirma' => $data['fechaFirma'],
        'ciudadFirma' => $data['ciudadFirma'],
        'departamentoFirma' => $data['departamentoFirma'],
    ])
    ->setPaper('letter')
    ->download(
        "Pagare PRAGA - {$prestamo->cliente->nombre_completo} - {$data['fechaFirma']}.pdf"
    );
}

public function generarDeclaracionModal(Request $request, Prestamo $prestamo)
{
    \Carbon\Carbon::setLocale('es');

    $data = $request->validate([
        'fechaDeclaracion' => 'required|date',
        'ciudadDeclaracion' => 'required|string',
        'departamentoDeclaracion' => 'required|string',
    ]);

    $prestamo->load('cliente');

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('contratos.declaracion', [
        'prestamo' => $prestamo,
        'fechaDeclaracion' => $data['fechaDeclaracion'],
        'ciudadDeclaracion' => $data['ciudadDeclaracion'],
        'departamentoDeclaracion' => $data['departamentoDeclaracion'],
    ])
    ->setPaper('letter')
    ->download(
        "Declaracion-Garantias PRAGA - {$prestamo->cliente->nombre_completo} - {$data['fechaDeclaracion']}.pdf"
    );
}


public function generarAutorizacionModal(Request $request, Prestamo $prestamo)
{
    $data = $request->validate([
        'fechaAutorizacion' => 'required|date',
        'ciudadAutorizacion' => 'required|string',
        'departamentoAutorizacion' => 'required|string',
    ]);

    \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('contratos.autorizacion', [
        'prestamo' => $prestamo,
        'fechaAutorizacion' => $data['fechaAutorizacion'],
        'ciudadAutorizacion' => $data['ciudadAutorizacion'],
        'departamentoAutorizacion' => $data['departamentoAutorizacion'],
    ])
    ->setPaper('letter')
    ->download(
        "Autorizacion-Credito PRAGA - {$prestamo->cliente->nombre_completo} - {$data['fechaAutorizacion']}.pdf"
    );
}

}
