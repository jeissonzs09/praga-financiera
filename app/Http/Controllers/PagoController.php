<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function index()
    {
        $prestamos = Prestamo::with(['pagos'])
            ->where('estado', 'Activo')
            ->get();

        return view('pagos.index', compact('prestamos'));
    }

public function plan($id)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($id);

    $frecuencia   = strtolower($prestamo->periodo); // mensual, quincenal o semanal
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes; // ej. 7

    // Número de cuotas reales según frecuencia y plazo
    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $numeroCuotas = $plazoMeses * ($map[$frecuencia] ?? 1);

    // Capital por cuota
    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);

    // Interés total del préstamo
    $interesTotal = $capitalTotal * ($tasa / 100);

    // Interés por cuota según frecuencia (regla PRAGA)
    if ($frecuencia === 'mensual') {
        $interesPorCuota = round($interesTotal / $numeroCuotas, 2);
    } elseif ($frecuencia === 'quincenal') {
        $interesPorCuota = round($interesTotal / 2, 2); // quincenal = mensual × 2 cuotas al mes
    } else { // semanal
        $interesPorCuota = round($interesTotal / 4, 2); // semanal = mensual × 4 semanas
    }

    // Construir plan
    $saldo  = $capitalTotal;
    $inicio = \Carbon\Carbon::parse($prestamo->created_at);
    $cuotas = [];
    $sumCap = $sumInt = $sumTot = 0;

    for ($i = 1; $i <= $numeroCuotas; $i++) {
        // Fecha de vencimiento: ahora la primera es después de 1 intervalo
        $vence = match($frecuencia) {
            'quincenal' => $inicio->copy()->addDays(15 * $i),
            'semanal'   => $inicio->copy()->addDays(7 * $i),
            default     => $inicio->copy()->addMonths($i)
        };

        // Total por cuota
        $total = $capitalPorCuota + $interesPorCuota;

        // Guardar cuota con saldo ANTES de restar
        $cuotas[] = [
            'nro'      => $i,
            'vence'    => $vence->format('d/m/Y'),
            'capital'  => $capitalPorCuota,
            'interes'  => $interesPorCuota,
            'recargos' => 0,
            'mora'     => 0,
            'total'    => $total,
            'saldo'    => $saldo, // saldo actual antes de pagar esta cuota
        ];

        // Restar capital al saldo para la siguiente cuota
        $saldo -= $capitalPorCuota;

        // Acumular totales
        $sumCap += $capitalPorCuota;
        $sumInt += $interesPorCuota;
        $sumTot += $total;
    }

    $totales = [
        'capital' => $sumCap,
        'interes' => $sumInt,
        'total'   => $sumTot
    ];

    return view('pagos.plan', compact('prestamo', 'cuotas', 'totales'));
}
}