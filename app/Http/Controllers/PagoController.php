<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;

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
    $prestamo = Prestamo::with(['cliente','pagos'])->findOrFail($id);

    // Usamos el método centralizado
    $cuotas = $this->generarPlan($prestamo);

    // Totales: sumamos capital, interés, total
    $totales = [
        'capital' => array_sum(array_column($cuotas, 'capital')),
        'interes' => array_sum(array_column($cuotas, 'interes')),
        'total'   => array_sum(array_column($cuotas, 'total')),
    ];

    return view('pagos.plan', compact('prestamo', 'cuotas', 'totales'));
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
            // Pagada completamente
            $estado = 'Pagada';
            $pagado = $totalCuota;
            $pendiente = 0;
            $capitalRestante = 0;
            $interesRestante = 0;
            $totalPagos -= $totalCuota;
        } elseif ($totalPagos > 0) {
            // Parcial: restar primero a interés
            $estado = 'Parcial';
            $pagado = $totalPagos;

            if ($totalPagos >= $interesRestante) {
                // Cubrió todo el interés
                $totalPagos -= $interesRestante;
                $interesRestante = 0;

                // Lo que sobre se descuenta del capital
                if ($totalPagos >= $capitalRestante) {
                    $totalPagos -= $capitalRestante;
                    $capitalRestante = 0;
                } else {
                    $capitalRestante -= $totalPagos;
                    $totalPagos = 0;
                }
            } else {
                // Solo cubrió parte del interés
                $interesRestante -= $totalPagos;
                $totalPagos = 0;
            }

            $pendiente = $capitalRestante + $interesRestante;
        } else {
            // Pendiente completa
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
            'total'     => $pendiente,
            'saldo'     => $saldo,
            'estado'    => $estado,
            'pagado'    => $pagado,
            'pendiente' => $pendiente,
        ];

        $saldo -= $capitalPorCuota;
    }

    return $cuotas;
}

public function createPago($prestamoId)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($prestamoId);

    return view('pagos.create', compact('prestamo'));
}

public function storePago(Request $request, $prestamoId)
{
    // 1️⃣ Validación del formulario
    $request->validate([
        'monto' => 'required|numeric|min:0.01',
        'observaciones' => 'nullable|string|max:255',
    ]);

    // 2️⃣ Traer el préstamo con sus pagos
    $prestamo = Prestamo::with('pagos')->findOrFail($prestamoId);
    $montoRestante = $request->monto;

    // 3️⃣ Datos base para calcular las cuotas originales
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $numeroCuotas = $plazoMeses * ($map[$frecuencia] ?? 1);

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal    = $capitalTotal * ($tasa / 100);

    if ($frecuencia === 'mensual') {
        $interesPorCuota = round($interesTotal / $numeroCuotas, 2);
    } elseif ($frecuencia === 'quincenal') {
        $interesPorCuota = round($interesTotal / 2, 2);
    } else {
        $interesPorCuota = round($interesTotal / 4, 2);
    }

    // 4️⃣ Repartir el pago en las cuotas reales según BD
    for ($nro = 1; $nro <= $numeroCuotas; $nro++) {

        // Total original de esta cuota
        $totalOriginalCuota = $capitalPorCuota + $interesPorCuota;

        // Pagado real en esta cuota según registros
        $pagadoReal = $prestamo->pagos()
            ->where('cuota_numero', $nro)
            ->sum('monto');

        // Saldo pendiente real
        $pendienteReal = $totalOriginalCuota - $pagadoReal;

        if ($pendienteReal > 0 && $montoRestante > 0) {

            // Monto que se aplicará aquí
            $montoAplicar = min($pendienteReal, $montoRestante);

            // Guardar registro
            Pago::create([
                'prestamo_id'  => $prestamo->id,
                'cuota_numero' => $nro,
                'monto'        => $montoAplicar,
                'observaciones'=> $request->observaciones,
            ]);

            $montoRestante -= $montoAplicar;

            // Si ya se aplicó todo el pago, detenemos
            if ($montoRestante <= 0) {
                break;
            }
        }
    }

    // 5️⃣ Verificar si el préstamo ya está liquidado
    $totalPagado = $prestamo->pagos()->sum('monto');
    $totalOriginal = $capitalTotal + $interesTotal;

    if ($totalPagado >= $totalOriginal) {
        $prestamo->estado = 'Finalizado';
        $prestamo->save();
    }

    // 6️⃣ Redirigir al historial
    return redirect()->route('pagos.plan', $prestamo->id)
        ->with('success', 'Pago registrado y aplicado correctamente.');
}

public function historial($prestamoId)
{
    $prestamo = Prestamo::with(['cliente', 'pagos' => function($q) {
        $q->orderBy('cuota_numero')->orderBy('created_at');
    }])->findOrFail($prestamoId);

    return view('pagos.historial', compact('prestamo'));
}

private function generarPlanOriginal(Prestamo $prestamo)
{
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $numeroCuotas = $plazoMeses * ($map[$frecuencia] ?? 1);

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal    = $capitalTotal * ($tasa / 100);

    if ($frecuencia === 'mensual') {
        $interesPorCuota = round($interesTotal / $numeroCuotas, 2);
    } elseif ($frecuencia === 'quincenal') {
        $interesPorCuota = round($interesTotal / 2, 2);
    } else {
        $interesPorCuota = round($interesTotal / 4, 2);
    }

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

public function planOriginal($id)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($id);
    $cuotas = $this->generarPlanOriginal($prestamo);

    $totales = [
        'capital' => array_sum(array_column($cuotas, 'capital')),
        'interes' => array_sum(array_column($cuotas, 'interes')),
        'total'   => array_sum(array_column($cuotas, 'total')),
    ];

    return view('pagos.plan_original', compact('prestamo', 'cuotas', 'totales'));
}


public function recibo($pagoId)
{
    $pago = Pago::with(['prestamo.cliente'])->findOrFail($pagoId);
    $prestamo = $pago->prestamo;
    $cliente  = $prestamo->cliente;

    // Cálculos
    $saldo_actual  = $prestamo->valor_prestamo - $prestamo->pagos()->where('id_pago', '<=', $pago->id_pago)->sum('monto');
    $abono_capital = $pago->monto;
    $saldo_anterior = $saldo_actual + $abono_capital;
    $monto_letras = ucfirst($this->numeroALetras($pago->monto));

    $detalle_cuotas = [[
        'cuota'   => $pago->cuota_numero,
        'interes' => 0.00,
        'capital' => $pago->monto,
        'recargo' => 0.00,
        'total'   => $pago->monto
    ]];

    // Renderizar vista en PDF
    $pdf = Pdf::loadView('pagos.recibo', compact(
        'pago',
        'prestamo',
        'cliente',
        'saldo_anterior',
        'abono_capital',
        'saldo_actual',
        'monto_letras',
        'detalle_cuotas'
    ))->setPaper('letter');

    // Descargar directamente
    $nombreArchivo = 'Recibo-'.$cliente->nombre_completo.'-'.$pago->id_pago.'.pdf';
    return $pdf->download($nombreArchivo);
}

// Helper rápido dentro del mismo controlador (o mejor en uno global)
private function numeroALetras($numero)
{
    $unidades = [
        '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis',
        'siete', 'ocho', 'nueve', 'diez', 'once', 'doce',
        'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
        'dieciocho', 'diecinueve', 'veinte'
    ];

    $decenas = [
        2 => 'veinti',
        3 => 'treinta',
        4 => 'cuarenta',
        5 => 'cincuenta',
        6 => 'sesenta',
        7 => 'setenta',
        8 => 'ochenta',
        9 => 'noventa'
    ];

    $centenas = [
        '', 'cien', 'doscientos', 'trescientos', 'cuatrocientos',
        'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'
    ];

    $numero = number_format($numero, 2, '.', '');
    [$entero, $decimal] = explode('.', $numero);
    $entero = intval($entero);
    $texto = '';

    if ($entero == 0) {
        $texto = 'cero';
    } elseif ($entero <= 20) {
        $texto = $unidades[$entero];
    } elseif ($entero < 100) {
        $d = intval($entero / 10);
        $u = $entero % 10;
        if ($d == 2 && $u != 0) {
            $texto = $decenas[$d] . $unidades[$u];
        } else {
            $texto = $decenas[$d] . ($u ? ' y ' . $unidades[$u] : '');
        }
    } elseif ($entero < 1000) {
        $c = intval($entero / 100);
        $r = $entero % 100;
        if ($r == 0) {
            $texto = $centenas[$c];
        } else {
            if ($c == 1) {
                $texto = 'ciento ' . $this->numeroALetras($r);
            } else {
                $texto = $centenas[$c] . ' ' . $this->numeroALetras($r);
            }
        }
    } else {
        // Para miles y millones
        if ($entero < 1000000) {
            $miles = intval($entero / 1000);
            $r = $entero % 1000;
            if ($miles == 1) {
                $texto = 'mil ' . ($r > 0 ? $this->numeroALetras($r) : '');
            } else {
                $texto = $this->numeroALetras($miles) . ' mil ' . ($r > 0 ? $this->numeroALetras($r) : '');
            }
        } else {
            $millones = intval($entero / 1000000);
            $r = $entero % 1000000;
            if ($millones == 1) {
                $texto = 'un millón ' . ($r > 0 ? $this->numeroALetras($r) : '');
            } else {
                $texto = $this->numeroALetras($millones) . ' millones ' . ($r > 0 ? $this->numeroALetras($r) : '');
            }
        }
    }

    return trim($texto) . " lempiras con {$decimal}/100";
}

public function simularPlan(Request $request)
{
    try {
        $prestamo = new \App\Models\Prestamo([
            'valor_prestamo'     => $request->valor_prestamo,
            'porcentaje_interes' => $request->porcentaje_interes,
            'plazo'              => $request->plazo,
            'periodo'            => $request->periodo,
            'created_at'         => now()
        ]);

        // En simulación no hay pagos, así que forzamos una colección vacía
        $prestamo->setRelation('pagos', collect());

        $cuotas = $this->generarPlan($prestamo);

        return response()->json($cuotas);
    } catch (\Throwable $e) {
        return response()->json([
            'error'   => 'Error interno',
            'mensaje' => $e->getMessage(),
            'linea'   => $e->getLine(),
            'archivo' => $e->getFile()
        ], 500);
    }
}

}