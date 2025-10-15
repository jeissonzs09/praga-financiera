<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\DetallePago;
use Illuminate\Http\Request;
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
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    // Definir pagos por mes
    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal    = $capitalTotal * ($tasa / 100) * $plazoMeses;
    $interesPorCuota = round($interesTotal / $numeroCuotas, 2);

    $saldo  = $capitalTotal;
    $inicio = Carbon::createFromFormat('Y-m-d', $prestamo->fecha_inicio, 'America/Tegucigalpa');
    $cuotas = [];

    // Definir días por cuota según frecuencia
    $diasPorCuota = match($frecuencia) {
        'semanal' => 7,
        'quincenal' => 15,
        default => 30, // mensual
    };

    for ($i = 1; $i <= $numeroCuotas; $i++) {
        // 🔹 Fecha de vencimiento: la primera cuota después del periodo
        $vence = match($frecuencia) {
            'semanal'   => $inicio->copy()->addWeeks($i),
            'quincenal' => $inicio->copy()->addDays(15 * $i),
            default     => $inicio->copy()->addMonths($i),
        };

        $saldo -= $capitalPorCuota;

        $cuota = [
            'nro'          => $i,
            'vence'        => $vence->format('Y-m-d'),
            'capital'      => $capitalPorCuota,
            'interes'      => $interesPorCuota,
            'recargos'     => 0,
            'mora'         => 0,
            'total'        => round($capitalPorCuota + $interesPorCuota, 2),
            'estado'       => 'Pendiente',
            'saldo'        => round($saldo, 2),
            'es_tardio'    => false,
            'dias_periodo' => $diasPorCuota,
        ];

        // Marcar pagos existentes
        if ($prestamo->exists) {
            $pago = \App\Models\Pago::where('prestamo_id', $prestamo->id)
                ->where('cuota_numero', $i)
                ->first();

            if ($pago) {
                $cuota['estado'] = 'Pagada';
                $venceDate       = Carbon::createFromFormat('Y-m-d', $cuota['vence'], 'America/Tegucigalpa');
                $fechaPago       = Carbon::parse($pago->created_at, 'America/Tegucigalpa');
                $cuota['es_tardio'] = $fechaPago->gt($venceDate);
            }
        }

        $cuotas[] = $cuota;
    }

    return $cuotas;
}

private function generarPlanPagos(Prestamo $prestamo)
{
    $cuotasBase = $this->generarPlan($prestamo);
    $cuotas = [];
    $saldo = $prestamo->valor_prestamo;
    $hoy = Carbon::now('America/Tegucigalpa')->startOfDay();

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
        $totalRestante   = $capitalRestante + $interesRestante;

        $vence = Carbon::parse($cuota['vence'], 'America/Tegucigalpa')->startOfDay();

        $estado = ($capitalRestante == 0 && $interesRestante == 0) ? 'Pagada' :
                  (($capitalPagado > 0 || $interesPagado > 0) ? 'Parcial' :
                  ($hoy->gt($vence) ? 'Vencida' : 'Pendiente'));

        $fechaPago = optional($pagos->first())->created_at;
        $esTardio = $fechaPago && Carbon::parse($fechaPago, 'America/Tegucigalpa')->startOfDay()->gt($vence);

        // Calcular interés al día
        $interesDia = 0;
        if ($estado === 'Pendiente') {
            $cuotaAnterior = $cuotas[$index - 1] ?? null;
            if ($cuotaAnterior && $cuotaAnterior['estado'] === 'Vencida') {
                $interesDia += $cuotaAnterior['interes'];
            }

            $inicioPeriodo = Carbon::parse($cuota['vence'], 'America/Tegucigalpa')->subDays($cuota['dias_periodo'])->addDay()->startOfDay();
            $diasTranscurridos = min($hoy->diffInDays($inicioPeriodo), $cuota['dias_periodo']);
            $interesDia += round(($cuota['interes'] / $cuota['dias_periodo']) * $diasTranscurridos, 2);
        }

        $cuotas[] = [
            'nro'          => $cuotaNum,
            'vence'        => $cuota['vence'],
            'capital'      => round($capitalRestante, 2),
            'interes'      => round($interesRestante, 2),
            'interes_dia'  => $interesDia,
            'total'        => round($totalRestante, 2),
            'saldo'        => round($saldo, 2),
            'estado'       => $estado,
            'es_tardio'    => $esTardio,
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

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id_cliente',
            'tipo_prestamo' => 'required|string',
            'tipo_interes' => 'required|string',
            'porcentaje_interes' => 'required|numeric',
            'plazo' => 'required|integer',
            'valor_prestamo' => 'required|numeric',
            'periodo' => 'required|string',
        ]);

        \DB::transaction(function() use ($request) {
            $prestamoOriginal = Prestamo::findOrFail($request->prestamo_id ?? 0);
            if ($prestamoOriginal) {
                $prestamoOriginal->estado = 'Inactivo';
                $prestamoOriginal->save();
            }

            Prestamo::create([
                'cliente_id' => $request->cliente_id,
                'valor_prestamo' => $request->valor_prestamo,
                'tipo_interes' => $request->tipo_interes,
                'porcentaje_interes' => $request->porcentaje_interes,
                'plazo' => $request->plazo,
                'periodo' => $request->periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'estado' => 'Activo',
                'tipo_prestamo' => $request->tipo_prestamo,
            ]);
        });

        return redirect()->route('refinanciamientos.index')->with('success', 'Préstamo refinanciado correctamente');
    }
}