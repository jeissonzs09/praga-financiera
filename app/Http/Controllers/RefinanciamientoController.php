<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use App\Models\DetallePago;
use Carbon\Carbon;

class RefinanciamientoController extends Controller
{
    public function index()
    {
        $prestamos = Prestamo::with('cliente')
            ->where('estado', 'Activo')
            ->get();

        return view('refinanciamientos.index', compact('prestamos'));
    }

    private function generarPlan(Prestamo $prestamo)
    {
        $frecuencia   = strtolower($prestamo->periodo); // 'mensual', 'quincenal', 'semanal'
        $plazoMeses   = (int) $prestamo->plazo;
        $capitalTotal = (float) $prestamo->valor_prestamo;
        $tasaAnual    = (float) $prestamo->porcentaje_interes;

        $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
        $pagosPorMes = $map[$frecuencia] ?? 1;
        $numeroCuotas = $plazoMeses * $pagosPorMes;

        $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);

        // Interés proporcional según tasa anual y duración del préstamo en meses
        $interesTotal = $capitalTotal * ($tasaAnual / 100) * ($plazoMeses / 12);
        $interesPorCuota = round($interesTotal / $numeroCuotas, 2);

        $inicio = Carbon::parse($prestamo->fecha_inicio);
        $cuotas = [];

        // Determinar días entre cuotas
        $diasPorCuota = match($frecuencia) {
            'semanal' => 7,
            'quincenal' => 15,
            default => 30, // mensual
        };

        $saldo = $capitalTotal;

        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $vence = $inicio->copy()->addDays($diasPorCuota * $i);
            $saldo -= $capitalPorCuota;

            $cuotas[] = [
                'nro'         => $i,
                'vence'       => $vence->format('Y-m-d'),
                'capital'     => $capitalPorCuota,
                'interes'     => $interesPorCuota,
                'total'       => round($capitalPorCuota + $interesPorCuota, 2),
                'saldo'       => round($saldo, 2),
                'estado'      => 'Pendiente',
                'es_tardio'   => false,
                'dias_periodo'=> $diasPorCuota,
            ];
        }

        return $cuotas;
    }

    private function generarPlanPagos(Prestamo $prestamo)
    {
        $cuotasBase = $this->generarPlan($prestamo);
        $cuotas = [];
        $saldo = $prestamo->valor_prestamo;
        $hoy = Carbon::now()->startOfDay();

        foreach ($cuotasBase as $index => $cuota) {
            $cuotaNum = $cuota['nro'];

            $pagos = DetallePago::where('prestamo_id', $prestamo->id)
                        ->where('cuota_numero', $cuotaNum)
                        ->orderBy('created_at')
                        ->get();

            $capitalPagado = $pagos->sum('capital');
            $interesPagado = $pagos->sum('interes');

            $capitalRestante = max($cuota['capital'] - $capitalPagado, 0);
            $interesRestante = max($cuota['interes'] - $interesPagado, 0);
            $totalRestante = $capitalRestante + $interesRestante;

            $vence = Carbon::parse($cuota['vence'])->startOfDay();

            // Estado de la cuota
            $estado = ($capitalRestante == 0 && $interesRestante == 0) ? 'Pagada' :
                      (($capitalPagado > 0 || $interesPagado > 0) ? 'Parcial' :
                      ($hoy->gt($vence) ? 'Vencida' : 'Pendiente'));

            $fechaPago = optional($pagos->first())->created_at;
            $esTardio = $fechaPago && Carbon::parse($fechaPago)->startOfDay()->gt($vence);

            // 🔹 Calcular interés al día correctamente
// 🔹 Calcular interés al día acumulado
$interesDia = 0;

// 1. Sumar interés completo de la última cuota vencida
if ($estado === 'Pendiente') {
    $cuotaAnterior = $cuotas[$index - 1] ?? null;
    if ($cuotaAnterior && $cuotaAnterior['estado'] === 'Vencida') {
        $interesDia += $cuotaAnterior['interes'];
    }

    // 2. Sumar interés proporcional de esta cuota
    $inicioPeriodo = Carbon::parse($cuota['vence'])->subDays($cuota['dias_periodo'])->addDay()->startOfDay();
    $diasTranscurridos = min($hoy->diffInDays($inicioPeriodo), $cuota['dias_periodo']);
    $interesDia += round(($cuota['interes'] / $cuota['dias_periodo']) * $diasTranscurridos, 2);
}


            $cuotas[] = [
                'nro'         => $cuotaNum,
                'vence'       => $cuota['vence'],
                'capital'     => round($capitalRestante, 2),
                'interes'     => round($interesRestante, 2),
                'interes_dia' => $interesDia,
                'total'       => round($totalRestante, 2),
                'saldo'       => round($saldo, 2),
                'estado'      => $estado,
                'es_tardio'   => $esTardio,
            ];

            $saldo -= $capitalRestante;
        }

        return $cuotas;
    }

    public function create($id)
    {
        $prestamo = Prestamo::findOrFail($id);
        $cliente = $prestamo->cliente;

        $planPagos = $this->generarPlanPagos($prestamo);

        $capitalPendiente = collect($planPagos)
                            ->where('estado', '!=', 'Pagada')
                            ->sum('capital');

        $interesAlDia = collect($planPagos)
                         ->where('estado', '!=', 'Pagada')
                         ->sum('interes_dia');

        return view('refinanciamientos.create', compact(
            'prestamo',
            'cliente',
            'planPagos',
            'capitalPendiente',
            'interesAlDia'
        ));
    }
}