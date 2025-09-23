<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\NumeroHelper;

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
     \Carbon\Carbon::setLocale('es');
    $prestamo->load('cliente');

    // 🔹 Generar el plan de cuotas
    $plan = $this->generarPlan($prestamo);

    // 🔹 Extraer datos clave para el contrato
    $montoCuota = $plan[0]['total'];
    $fechaPrimeraCuota = $plan[0]['vence'];
    $fechaUltimaCuota = end($plan)['vence'];

    // 🔹 Generar el PDF con todos los datos necesarios
    $pdf = Pdf::loadView('contratos.plantilla', compact(
        'prestamo',
        'montoCuota',
        'fechaPrimeraCuota',
        'fechaUltimaCuota'
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

}
