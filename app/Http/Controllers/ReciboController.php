<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\Prestamo;
use Barryvdh\DomPDF\Facade\Pdf; // Importar DomPDF

class ReciboController extends Controller
{
    public function index($prestamoId)
    {
        $prestamo = Prestamo::findOrFail($prestamoId);

        // Traer todos los recibos de este préstamo con sus detalles
        $recibos = Recibo::with('detalles')
            ->where('prestamo_id', $prestamoId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('recibos.index', compact('prestamo', 'recibos'));
    }

    public function show($idRecibo)
{
    $recibo = Recibo::with('detalles')->findOrFail($idRecibo);

    return view('recibos.show', compact('recibo'));
}

private function generarPlan(Prestamo $prestamo)
{
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    // Mapeo de frecuencia a pagos por mes
    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    // Cálculo consistente con el frontend
    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $tasaPorPeriodo = ($tasa / 100) / $pagosPorMes;
    $interesPorCuota = round($capitalTotal * $tasaPorPeriodo, 2);
    $totalCuota = $capitalPorCuota + $interesPorCuota;

    $saldo  = $capitalTotal;
    $inicio = \Carbon\Carbon::parse($prestamo->created_at);
    $cuotas = [];

    // Total acumulado pagado (en orden)
    $totalPagos = $prestamo->relationLoaded('pagos') && $prestamo->pagos
        ? $prestamo->pagos->sum('monto')
        : 0;

    for ($i = 1; $i <= $numeroCuotas; $i++) {
        $vence = match($frecuencia) {
            'quincenal' => $inicio->copy()->addDays(15 * $i),
            'semanal'   => $inicio->copy()->addDays(7 * $i),
            default     => $inicio->copy()->addMonths($i)
        };

        $capitalRestante = $capitalPorCuota;
        $interesRestante = $interesPorCuota;

        if ($totalPagos >= $totalCuota) {
            $estado = 'Pagada';
            $pagado = $totalCuota;
            $pendiente = 0;
            $capitalRestante = 0;
            $interesRestante = 0;
            $totalPagos -= $totalCuota;
        } elseif ($totalPagos > 0) {
            $estado = 'Parcial';
            $pagado = $totalPagos;

            if ($totalPagos >= $interesRestante) {
                $totalPagos -= $interesRestante;
                $interesRestante = 0;

                if ($totalPagos >= $capitalRestante) {
                    $totalPagos -= $capitalRestante;
                    $capitalRestante = 0;
                } else {
                    $capitalRestante -= $totalPagos;
                    $totalPagos = 0;
                }
            } else {
                $interesRestante -= $totalPagos;
                $totalPagos = 0;
            }

            $pendiente = $capitalRestante + $interesRestante;
        } else {
            $estado = 'Pendiente';
            $pagado = 0;
            $pendiente = $totalCuota;
        }

        $cuotas[] = [
            'nro'       => $i,
            'vence'     => $vence->format('d/m/Y'),
            'capital'   => $capitalRestante,
            'interes'   => $interesRestante,
            'recargos'  => 0,
            'mora'      => 0,
            'total'     => $capitalRestante + $interesRestante,
            'saldo'     => $saldo,
            'estado'    => $estado,
            'pagado'    => $pagado,
            'pendiente' => $pendiente,
        ];

        $saldo -= $capitalPorCuota;
    }

    return $cuotas;
}

/**
 * Plan original sin restar pagos
 */
private function generarPlanOriginal(Prestamo $prestamo)
{
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    // Determinar número de cuotas según frecuencia
    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    // Capital por cuota
    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);

    // 🔹 Interés total pactado para todo el préstamo
    // Si la tasa es mensual, multiplicamos por el número de meses
    $interesTotal = $capitalTotal * ($tasa / 100) * $plazoMeses;

    // Interés por cuota
    $interesPorCuota = round($interesTotal / $numeroCuotas, 2);

    $saldo  = $capitalTotal;
    $inicio = \Carbon\Carbon::parse($prestamo->created_at);
    $cuotas = [];

    for ($i = 1; $i <= $numeroCuotas; $i++) {
        $vence = match($frecuencia) {
            'quincenal' => $inicio->copy()->addDays(15 * $i),
            'semanal'   => $inicio->copy()->addDays(7 * $i),
            default     => $inicio->copy()->addMonths($i)
        };

        $totalCuota = $capitalPorCuota + $interesPorCuota;

        $cuotas[] = [
            'nro'       => $i,
            'vence'     => $vence->format('d/m/Y'),
            'capital'   => $capitalPorCuota,
            'interes'   => $interesPorCuota,
            'recargos'  => 0,
            'mora'      => 0,
            'total'     => $totalCuota,
            'saldo'     => $saldo,
            'estado'    => 'Pendiente'
        ];

        $saldo -= $capitalPorCuota;
    }

    return $cuotas;
}



/**
 * Generar PDF del recibo
 */
public function pdf($idRecibo)
{
    $recibo = Recibo::with(['prestamo.cliente', 'detalles'])
        ->findOrFail($idRecibo);

    // Buscar recibo anterior
    $reciboAnterior = Recibo::where('prestamo_id', $recibo->prestamo_id)
        ->where('id_recibo', '<', $recibo->id_recibo)
        ->orderBy('id_recibo', 'desc')
        ->first();

    if ($reciboAnterior) {
        // Si existe, usamos su saldo actual
        $saldoAnterior = $reciboAnterior->saldo_actual;
    } else {
        // Si es el primer recibo, usamos el total del plan (capital + intereses)
        $planOriginal = $this->generarPlanOriginal($recibo->prestamo);
        $saldoAnterior = array_sum(array_column($planOriginal, 'total')); // Ej: 35,000
    }

    // Lo que abona este recibo (capital + intereses en monto_total)
    $capitalAbonado = $recibo->monto_total;

    // Calcular nuevo saldo
    $saldoActual = $saldoAnterior - $capitalAbonado;

    // Guardar saldo en el recibo
    $recibo->saldo_actual = $saldoActual;
    $recibo->save();

    // Convertir a letras
    $montoLetras = $this->montoEnLetras($recibo->monto_total);

    // Generar PDF
    $pdf = Pdf::loadView('recibos.pdf', [
        'recibo'           => $recibo,
        'saldoAnterior'    => $saldoAnterior,
        'capitalAbonado'   => $capitalAbonado,
        'saldoActual'      => $saldoActual,
        'montoLetras'      => $montoLetras,
        'cai'              => 'A1B2-C3D4-E5F6-G7H8-I9J0K1L2M3',
        'rangoAutorizado'  => '000-000-00-00000001 a 000-000-00-00010000',
        'fechaLimite'      => '31/12/2050'
    ])->setPaper('letter', 'portrait');

    return $pdf->download('Recibo_' . $recibo->id_recibo . '.pdf');
}

/**
 * Convierte monto a letras
 */
private function montoEnLetras(float $monto): string
{
    $entero   = floor($monto);
    $centavos = round(($monto - $entero) * 100);

    if (class_exists(\NumberFormatter::class)) {
        $fmt = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $palabras = mb_strtoupper($fmt->format($entero), 'UTF-8');
    } else {
        $palabras = mb_strtoupper((string)$entero, 'UTF-8');
    }

    return "*** {$palabras} LEMPIRAS CON " . str_pad($centavos, 2, '0', STR_PAD_LEFT) . "/100 ***";
}

}