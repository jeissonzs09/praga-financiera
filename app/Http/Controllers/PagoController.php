<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DetallePago;
use App\Models\Recibo;

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
    $prestamo = Prestamo::with(['cliente', 'pagos'])->findOrFail($id);

    // ✅ Usamos el plan dinámico que toma en cuenta los pagos
    $cuotas = $this->generarPlanPagos($prestamo);

    // Totales
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

    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal = $capitalTotal * ($tasa / 100) * $plazoMeses;
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

        $cuotas[] = [
            'nro'      => $i,
            // 👇 Guardamos en formato ISO (seguro para Carbon y MySQL)
            'vence'    => $vence->format('Y-m-d'),
            'capital'  => $capitalPorCuota,
            'interes'  => $interesPorCuota,
            'recargos' => 0,
            'mora'     => 0,
            'total'    => $capitalPorCuota + $interesPorCuota,
            'estado'   => 'Pendiente',
            'saldo'    => round($saldo, 2),
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
    $request->validate([
        'monto' => 'required|numeric|min:0.01',
        'observaciones' => 'nullable|string|max:255',
    ]);

    $prestamo = Prestamo::with('pagos')->findOrFail($prestamoId);
    $montoRestante = $request->monto;

    $recibo = Recibo::create([
        'prestamo_id'  => $prestamo->id,
        'monto_total'  => $request->monto,
        'observaciones'=> $request->observaciones,
    ]);

    $cuotas = $this->generarPlan($prestamo); // solo valores base

    foreach ($cuotas as $cuota) {
        $cuotaNum = $cuota['nro'];
        $pagadoEnBD = $prestamo->pagos()->where('cuota_numero', $cuotaNum)->sum('monto');
        $totalCuota = $cuota['capital'] + $cuota['interes'];
        $pendienteReal = $totalCuota - $pagadoEnBD;

        if ($pendienteReal <= 0) continue;

        $montoAplicar = min($montoRestante, $pendienteReal);

        // Guardar en historial
        Pago::create([
            'prestamo_id'  => $prestamo->id,
            'cuota_numero' => $cuotaNum,
            'monto'        => $montoAplicar,
            'observaciones'=> $request->observaciones,
        ]);

        // Detalle pago: primero interés, luego capital
        if ($montoAplicar < $totalCuota) {
            $interesAplicado = min($cuota['interes'], $montoAplicar);
            $capitalAplicado = $montoAplicar - $interesAplicado;
        } else {
            $interesAplicado = $cuota['interes'];
            $capitalAplicado = $cuota['capital'];
        }

        DetallePago::create([
            'id_recibo'    => $recibo->id_recibo,
            'cuota_numero' => $cuotaNum,
            'capital'      => round($capitalAplicado, 2),
            'interes'      => round($interesAplicado, 2),
            'recargo'      => 0,
            'mora'         => 0,
            'total'        => round($montoAplicar, 2),
        ]);

        $montoRestante -= $montoAplicar;

        if ($montoRestante <= 0) break;
    }

    // Verificar si préstamo liquidado
    $totalPagado = $prestamo->pagos()->sum('monto');
    $totalOriginal = array_sum(array_column($cuotas, 'capital')) +
                     array_sum(array_column($cuotas, 'interes'));

    if ($totalPagado >= $totalOriginal) {
        $prestamo->estado = 'Finalizado';
        $prestamo->save();
    }

    return redirect()->route('pagos.plan', $prestamo->id)
        ->with('success', 'Pago registrado y aplicado correctamente.');
}

// Controlador para mostrar plan
public function mostrarPlan($prestamoId)
{
    $prestamo = Prestamo::with('pagos')->findOrFail($prestamoId);
    $cuotas = $this->generarPlanPagos($prestamo); // <-- aquí siempre se actualiza con los pagos reales

    return view('pagos.plan', compact('prestamo', 'cuotas'));
}

private function generarPlanPagos(Prestamo $prestamo)
{
    $cuotasBase = $this->generarPlan($prestamo);
    $cuotas = [];
    $saldo = $prestamo->valor_prestamo;

    foreach ($cuotasBase as $cuota) {
        $cuotaNum = $cuota['nro'];

        $pagado = $prestamo->pagos()->where('cuota_numero', $cuotaNum)->sum('monto');

        $interesRestante = max($cuota['interes'] - $pagado, 0);
        $capitalRestante = max($cuota['capital'] - max($pagado - $cuota['interes'], 0), 0);
        $totalRestante = $interesRestante + $capitalRestante;

        if ($totalRestante == 0) {
            $estado = 'Pagada';
        } elseif ($pagado > 0) {
            $estado = 'Parcial';
        } elseif (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($cuota['vence']))) {
            $estado = 'Vencida';
        } else {
            $estado = 'Pendiente';
        }

        $cuotas[] = [
            'nro'       => $cuotaNum,
            // sigue en ISO
            'vence'     => $cuota['vence'],
            'capital'   => round($capitalRestante, 2),
            'interes'   => round($interesRestante, 2),
            'recargos'  => 0,
            'mora'      => 0,
            'total'     => round($totalRestante, 2),
            'saldo'     => round($saldo, 2),
            'estado'    => $estado,
        ];

        $saldo -= $capitalRestante;
    }

    return $cuotas;
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
        // Creamos un objeto Prestamo temporal para la simulación
        $prestamo = new \App\Models\Prestamo([
            'valor_prestamo'     => $request->valor_prestamo,
            'porcentaje_interes' => $request->porcentaje_interes,
            'plazo'              => $request->plazo,
            'periodo'            => $request->periodo,
            'created_at'         => now()
        ]);

        // En simulación no hay pagos, así que forzamos una colección vacía
        $prestamo->setRelation('pagos', collect());

        // Generamos el plan
        $cuotas = $this->generarPlan($prestamo);

        // Aseguramos que las fechas estén en formato 'd/m/Y' para Blade
        foreach ($cuotas as &$cuota) {
            $cuota['vence'] = \Carbon\Carbon::parse($cuota['vence'])->format('d/m/Y');
        }

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

public function listarPagos(Prestamo $prestamo)
{
    // Traer recibos con su total y fecha
    $recibos = Recibo::where('prestamo_id', $prestamo->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('pagos.listar', compact('prestamo', 'recibos'));
}


public function eliminarPago(Pago $pago)
{
    // Si quieres, aquí puedes eliminar también el detalle del recibo
    DetallePago::where('id_recibo', $pago->id_recibo)
        ->where('cuota_numero', $pago->cuota_numero)
        ->delete();

    $pago->delete();

    return back()->with('success', 'Pago eliminado correctamente.');
}

public function eliminarRecibo($idRecibo)
{
    // 1️⃣ Buscar el recibo
    $recibo = Recibo::findOrFail($idRecibo);

    // 2️⃣ Obtener las cuotas afectadas por ese recibo
    $cuotas = DetallePago::where('id_recibo', $idRecibo)->pluck('cuota_numero');

    // 3️⃣ Eliminar los pagos por cuota que correspondan a esas cuotas
    Pago::where('prestamo_id', $recibo->prestamo_id)
        ->whereIn('cuota_numero', $cuotas)
        ->delete();

    // 4️⃣ Eliminar los detalles del recibo
    DetallePago::where('id_recibo', $idRecibo)->delete();

    // 5️⃣ Eliminar el recibo en sí
    $recibo->delete();

    return back()->with('success', 'Pago eliminado correctamente y plan restaurado.');
}

}