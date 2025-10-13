<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DetallePago;
use App\Models\Recibo;
use App\Models\ReciboPago;

class PagoController extends Controller
{
public function index(Request $request)
{
    $prestamos = Prestamo::with(['pagos', 'cliente']) // 👈 importante para buscar por cliente
        ->where('estado', 'Activo')
        ->when($request->filled('buscar'), function ($query) use ($request) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->buscar . '%');
            });
        })
        ->latest() // 👈 ordena por fecha de creación, más nuevos primero
        ->get(); // 👈 solo 10 por página

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




public function createPago($prestamoId)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($prestamoId);

    return view('pagos.create', compact('prestamo'));
}

public function storePago(Request $request, $prestamoId)
{
    $request->validate([
        'monto'         => 'required|numeric|min:0.01',
        'metodo_pago'   => 'required|string',
        'observaciones' => 'nullable|string|max:255',
        'cuotas'        => 'nullable|array',
        'fecha_pago'    => 'required|date',
    ]);

    $prestamo = Prestamo::with('pagos')->findOrFail($prestamoId);
    $cuotas = $this->generarPlan($prestamo);

    $montoIngresado = floatval($request->monto);

    // 🔹 Calcular total original pendiente
    $totalPendiente = collect($cuotas)->sum(function($c) {
        return $c['capital'] + $c['interes'];
    });

    if ($montoIngresado > $totalPendiente) {
        return back()->with('error', 'El monto ingresado excede el total pendiente del préstamo.');
    }

    if ($montoIngresado < $totalPendiente && empty($request->cuotas)) {
        return back()->with('error', 'Debe distribuir todo el pago entre las cuotas.');
    }

    // 🔹 Crear recibo
    $recibo = Recibo::create([
        'prestamo_id'   => $prestamo->id,
        'monto_total'   => $montoIngresado,
        'metodo_pago'   => $request->metodo_pago,
        'observaciones' => $request->observaciones,
        'fecha'         => $request->fecha_pago,
    ]);

    $montoRestante = $montoIngresado;

    foreach ($cuotas as $cuota) {
        if ($montoRestante <= 0) break;

        $capital = $cuota['capital'];
        $interes = $cuota['interes'];
        $totalCuota = $capital + $interes;
        $atrasada = $cuota['estado'] === 'Pendiente' && $cuota['fecha_vencimiento'] < now();

        if ($montoRestante >= $totalCuota) {
            // Pago completo de la cuota
            $detalleCapital = $capital;
            $detalleInteres = $interes;
            $excedente = $montoRestante - $totalCuota;
        } else {
            // Pago parcial
            if ($atrasada) {
                $detalleInteres = min($interes, $montoRestante);
                $detalleCapital = max(0, $montoRestante - $detalleInteres);
            } else {
                $detalleCapital = min($capital, $montoRestante);
                $detalleInteres = max(0, $montoRestante - $detalleCapital);
            }
            $excedente = 0;
        }

        // 🔹 Guardar detalle de pago
        DetallePago::create([
            'id_recibo'     => $recibo->id_recibo,
            'cuota_numero'  => $cuota['nro'],
            'capital'       => round($detalleCapital, 2),
            'interes'       => round($detalleInteres, 2),
            'recargo'       => 0,
            'mora'          => 0,
            'total'         => round($detalleCapital + $detalleInteres, 2),
            'fecha_pago'    => $request->fecha_pago,
        ]);

        $montoRestante -= ($detalleCapital + $detalleInteres);
    }

    // 🔹 Actualizar estado del préstamo
    $totalPagado = DetallePago::whereIn('id_recibo', Recibo::where('prestamo_id', $prestamo->id)->pluck('id_recibo'))
                    ->sum('total');

    if ($totalPagado >= $totalPendiente) {
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

        // Obtener todos los pagos registrados para esta cuota
        $pagos = DetallePago::where('prestamo_id', $prestamo->id)
                    ->where('cuota_numero', $cuotaNum)
                    ->orderBy('created_at')
                    ->get();
        $capitalPagado = $pagos->sum('capital');
$interesPagado = $pagos->sum('interes');
        $fechaPago = optional($pagos->first())->created_at;

        // Cálculo de capital pagado e interés restante
        $capitalRestante   = max($cuota['capital'] - $capitalPagado, 0);
$interesRestante   = max($cuota['interes'] - $interesPagado, 0);
$totalRestante     = $capitalRestante + $interesRestante;

        // Estado de la cuota
        if ($capitalRestante == 0 && $interesRestante == 0) {
    $estado = 'Pagada';
} elseif ($capitalPagado > 0 || $interesPagado > 0) {
    $estado = 'Parcial';
} elseif (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($cuota['vence']))) {
    $estado = 'Vencida';
} else {
    $estado = 'Pendiente';
}

        // Evaluar si el pago fue tardío
        $vence = \Carbon\Carbon::parse($cuota['vence']);
        $esTardio = $fechaPago && \Carbon\Carbon::parse($fechaPago)->gt($vence);

        // Guardar la cuota con el saldo actual (antes de aplicar el capital)
        $cuotas[] = [
            'nro'        => $cuotaNum,
            'vence'      => $cuota['vence'],
            'capital'    => round($capitalRestante, 2),
            'interes'    => round($interesRestante, 2),
            'recargos'   => 0,
            'mora'       => 0,
            'total'      => round($totalRestante, 2),
            'saldo'      => round($saldo, 2),
            'estado'     => $estado,
            'es_tardio'  => $esTardio,
        ];

        // Aplicar el capital pagado para la siguiente cuota
        $saldo -= $capitalPagado;
    }

    return $cuotas;
}


public function historial($prestamoId)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($prestamoId);

    $detalles = DetallePago::where('prestamo_id', $prestamo->id)
                           ->orderBy('cuota_numero', 'asc')
                           ->get();

    return view('pagos.historial', compact('prestamo', 'detalles'));
}

private function generarPlanOriginal(Prestamo $prestamo)
{
    $frecuencia   = strtolower($prestamo->periodo);
    $plazoMeses   = (int) $prestamo->plazo;
    $capitalTotal = (float) $prestamo->valor_prestamo;
    $tasa         = (float) $prestamo->porcentaje_interes;

    $map = ['mensual' => 1, 'quincenal' => 2, 'semanal' => 4];
    $pagosPorMes = $map[$frecuencia] ?? 1;
    $numeroCuotas = $plazoMeses * $pagosPorMes;

    $capitalPorCuota = round($capitalTotal / $numeroCuotas, 2);
    $interesTotal    = $capitalTotal * ($tasa / 100) * $plazoMeses;
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

        // 👇 Guardamos el saldo ANTES de aplicar el capital
        $cuotas[] = [
            'nro'       => $i,
            'vence'     => $vence->format('d/m/Y'),
            'capital'   => $capitalPorCuota,
            'interes'   => $interesPorCuota,
            'recargos'  => 0,
            'mora'      => 0,
            'total'     => $capitalPorCuota + $interesPorCuota,
            'saldo'     => round($saldo, 2),
            'estado'    => 'Pendiente'
        ];

        // 👇 Luego descontamos el capital para la siguiente cuota
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
            'fecha_inicio' => $request->fecha_inicio, // ✅ ahora coincide con el name del input

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
    // Traer recibos del préstamo con sus detalles
    $recibos = Recibo::with('detalles')
        ->where('prestamo_id', $prestamo->id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Agregar totales de capital e intereses a cada recibo
    $recibos->transform(function ($recibo) {
        $recibo->total_capital = $recibo->detalles->sum('capital');
        $recibo->total_interes = $recibo->detalles->sum('interes');
        return $recibo;
    });

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
    $recibo = ReciboPago::findOrFail($idRecibo);

    // 2️⃣ Eliminar los detalles del recibo
    DetallePago::where('id_recibo', $idRecibo)->delete();

    // 3️⃣ Eliminar el recibo en sí
    $recibo->delete();

    return back()->with('success', 'Pago eliminado correctamente');
}

public function pdfPlanOriginal($prestamoId)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($prestamoId);
    $cuotas = $this->generarPlanOriginal($prestamo);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pagos.plan_original_pdf', [
        'prestamo' => $prestamo,
        'cuotas'   => $cuotas,
    ])->setPaper('letter', 'portrait');

    $nombreCliente = preg_replace('/[^A-Za-z0-9]/', '_', $prestamo->cliente->nombre_completo);
$nombreArchivo = 'Plan_de_pago_' . $nombreCliente . '.pdf';

    return $pdf->download($nombreArchivo);
}

public function distribuir(Request $request, $prestamoId)
{
    $prestamo = Prestamo::findOrFail($prestamoId);

    // Aseguramos que fecha_pago siempre tenga un valor
    $fechaPago = $request->input('fecha_pago') ?: now()->format('Y-m-d');

    return view('pagos.distribuir', [
        'prestamo'      => $prestamo,
        'monto'         => $request->monto,
        'metodo_pago'   => $request->metodo_pago,
        'observaciones' => $request->observaciones,
        'fecha_pago'    => $fechaPago,
        'cuotas'        => $this->generarPlanPagos($prestamo),
    ]);
}

public function guardarDistribucion(Request $request, $prestamoId)
{
    $prestamo = Prestamo::findOrFail($prestamoId);
    $cuotas = $request->input('cuotas');
    $montoTotal = floatval($request->input('monto_total'));

    // 🔹 Asegurarse de tener la fecha de pago
    $fechaPago = $request->input('fecha_pago') ?? now()->format('Y-m-d');

    // 🔹 Crear recibo general
    $recibo = ReciboPago::create([
        'prestamo_id' => $prestamo->id,
        'monto' => $montoTotal,
        'metodo_pago' => $request->input('metodo_pago'),
        'observaciones' => $request->input('observaciones'),
        'fecha_pago' => $fechaPago,
    ]);

    // 🔹 Registrar cada cuota con la fecha correcta
    foreach ($cuotas as $nro => $datos) {
        $capital = floatval($datos['capital']);
        $interes = floatval($datos['interes']);
        $recargo = floatval($datos['recargo']);
        $total = $capital + $interes + $recargo;

        DetallePago::create([
            'prestamo_id' => $prestamo->id,
            'cuota_numero' => $nro,
            'capital' => $capital,
            'interes' => $interes,
            'recargo' => $recargo,
            'mora' => 0,
            'total' => $total,
            'id_recibo' => $recibo->id,
            'fecha_pago' => $fechaPago,
        ]);
    }

    // 🔹 Actualizar totales en el recibo usando los detalles
    $totales = \DB::table('detalle_pagos')
        ->where('id_recibo', $recibo->id)
        ->select(
            \DB::raw('SUM(capital) as total_capital'),
            \DB::raw('SUM(interes) as total_interes')
        )
        ->first();

    $recibo->capital = $totales->total_capital ?? 0;
    $recibo->interes = $totales->total_interes ?? 0;
    $recibo->save();

    return redirect()->route('recibos.index', $prestamo->id)
                     ->with('success', 'Pago Registrado Correctamente');
}

public function getCalendarioPagos()
{
    // Traemos solo los préstamos activos
    $prestamos = Prestamo::with('cliente')->where('estado', 'Activo')->get();

    $dias = []; // agrupamos pagos por fecha

    foreach ($prestamos as $prestamo) {
        $cuotas = $this->generarPlanPagos($prestamo);

        foreach ($cuotas as $cuota) {
            $fechaPago = \Carbon\Carbon::parse($cuota['vence'])->format('Y-m-d');

            if (!isset($dias[$fechaPago])) {
                $dias[$fechaPago] = [
                    'title' => 'Ver Pagos',
                    'start' => $fechaPago,
                    'allDay' => true,
                    'color' => 'blue',
                    'extendedProps' => ['pagos' => []]
                ];
            }

            $dias[$fechaPago]['extendedProps']['pagos'][] = [
                'cliente'  => $prestamo->cliente ? $prestamo->cliente->nombre_completo : 'Cliente Desconocido',
                'capital'  => $cuota['capital'],
                'interes'  => $cuota['interes'],
                'total'    => $cuota['total'],
                'estado'   => $cuota['estado'],
                'es_tardio'=> $cuota['es_tardio']
            ];

            if (in_array($cuota['estado'], ['Vencida', 'Parcial']) || $cuota['es_tardio']) {
                $dias[$fechaPago]['color'] = 'red';
            }
        }
    }

    $events = array_values($dias);

    return response()->json($events);
}

private function generarPlanPagadas(Prestamo $prestamo)
{
    // Obtenemos todas las cuotas del plan original
    $cuotas = $this->generarPlanPagos($prestamo);
    $cuotasPagadas = [];

    foreach ($cuotas as $cuota) {
        // Obtenemos los pagos registrados para esta cuota
        $pagos = DetallePago::where('prestamo_id', $prestamo->id)
                    ->where('cuota_numero', $cuota['nro'])
                    ->get();

        // Si hay pagos registrados, calculamos lo pagado
        $capitalPagado = $pagos->sum('capital');
        $interesPagado = $pagos->sum('interes');
        $recargosPagado = $pagos->sum('recargos');
        $moraPagado     = $pagos->sum('mora');

        // Solo agregamos la cuota si tiene algún pago (incluso parcial)
        if ($capitalPagado > 0 || $interesPagado > 0 || $recargosPagado > 0 || $moraPagado > 0) {
            $cuotasPagadas[] = [
                'nro'      => $cuota['nro'],
                'vence'    => $cuota['vence'],
                'capital'  => round($capitalPagado, 2),
                'interes'  => round($interesPagado, 2),
                'recargos' => round($recargosPagado, 2),
                'mora'     => round($moraPagado, 2),
                'total'    => round($capitalPagado + $interesPagado + $recargosPagado + $moraPagado, 2),
                'saldo'    => round($cuota['saldo'], 2),
                'estado'   => 'Pagada',
                'es_tardio'=> $cuota['es_tardio'] ?? false,
            ];
        }
    }

    return $cuotasPagadas;
}

public function mostrarPlanAjax(Request $request, $id)
{
    $estadoSeleccionado = $request->query('estado', 'Todas');

    $prestamo = Prestamo::with('cliente')->findOrFail($id);

    // 🔹 Seleccionar método según el filtro
    if ($estadoSeleccionado === 'Pagadas') {
        $cuotas = $this->generarPlanPagadas($prestamo); // método nuevo para mostrar cuotas pagadas
    } else {
        $cuotas = $this->generarPlanPagos($prestamo);  // tu método existente
    }

    // 🔹 Inicializar totales
    $totales = [
        'capital'  => 0,
        'interes'  => 0,
        'recargos' => 0,
        'mora'     => 0,
        'total'    => 0,
    ];

    // 🔹 Sumar solo las cuotas que correspondan al filtro
    foreach ($cuotas as $cuota) {
        if ($estadoSeleccionado === 'Todas' ||
            ($estadoSeleccionado === 'Pendientes' && in_array($cuota['estado'], ['Pendiente','Parcial','Vencida'])) ||
            ($estadoSeleccionado === 'Pagadas' && $cuota['estado'] === 'Pagada'))
        {
            $totales['capital']  += $cuota['capital'];
            $totales['interes']  += $cuota['interes'];
            $totales['recargos'] += $cuota['recargos'];
            $totales['mora']     += $cuota['mora'];
            $totales['total']    += $cuota['total'];
        }
    }

    return view('prestamos.partials.plan', compact('prestamo', 'cuotas', 'totales', 'estadoSeleccionado'));
}

public function descargarEstadoCuentaPDF($id)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($id);

    // 🔹 Plan original completo
    $cuotasOriginales = $this->generarPlanOriginal($prestamo);

    foreach ($cuotasOriginales as &$cuota) {
        $cuota['capital_original'] = $cuota['capital'] ?? 0;
        $cuota['interes_original'] = $cuota['interes'] ?? 0;
        $cuota['vence'] = $this->formatearFechaSeguro($cuota['vence'] ?? null);
    }
    unset($cuota);

    // 🔹 Cuotas pendientes
    $pendientes = $this->generarPlanPagos($prestamo);

    foreach ($pendientes as &$cuota) {
        $cuota['vence'] = $this->formatearFechaSeguro($cuota['vence'] ?? null);
    }
    unset($cuota);

    // 🔹 Filtrar solo las cuotas no pagadas o parciales
    $pendientes = collect($pendientes)
        ->filter(fn($cuota) => ($cuota['capital'] ?? 0) + ($cuota['interes'] ?? 0) + ($cuota['recargos'] ?? 0) + ($cuota['mora'] ?? 0) > 0)
        ->values()
        ->toArray();

    // 🔹 Cuotas pagadas como recibos
$pagadas = Recibo::with('detalles')
    ->where('prestamo_id', $prestamo->id)
    ->orderBy('fecha_pago', 'asc')
    ->get()
    ->map(function($recibo) {
        return [
            'nro' => $recibo->id,
            'vence' => \Carbon\Carbon::parse($recibo->fecha_pago)->format('d/m/Y'), // ← aquí
            'capital' => $recibo->capital,
            'interes' => $recibo->interes,
            'recargos' => $recibo->detalles->sum('recargo'),
            'mora' => $recibo->detalles->sum('mora'),
            'total' => $recibo->capital + $recibo->interes + $recibo->detalles->sum('recargo') + $recibo->detalles->sum('mora'),
        ];
    })->toArray();

    // 🔹 Totales
    $totalesOriginal = [
        'capital' => array_sum(array_column($cuotasOriginales, 'capital_original')),
        'interes' => array_sum(array_column($cuotasOriginales, 'interes_original')),
        'total'   => array_sum(array_map(fn($c) =>
            ($c['capital_original'] ?? 0) + ($c['interes_original'] ?? 0), $cuotasOriginales)),
    ];

    $totalesPendientes = [
        'capital'  => array_sum(array_column($pendientes, 'capital')),
        'interes'  => array_sum(array_column($pendientes, 'interes')),
        'recargos' => array_sum(array_column($pendientes, 'recargos')),
        'mora'     => array_sum(array_column($pendientes, 'mora')),
        'total'    => array_sum(array_map(fn($c) =>
            ($c['capital'] ?? 0) + ($c['interes'] ?? 0) + ($c['recargos'] ?? 0) + ($c['mora'] ?? 0), $pendientes)),
    ];

    $totalesPagadas = [
        'capital'  => array_sum(array_column($pagadas, 'capital')),
        'interes'  => array_sum(array_column($pagadas, 'interes')),
        'recargos' => array_sum(array_column($pagadas, 'recargos')),
        'mora'     => array_sum(array_column($pagadas, 'mora')),
        'total'    => array_sum(array_column($pagadas, 'total')),
    ];

    // 🔹 Generar PDF
    $pdf = Pdf::loadView('prestamos.pdf.estado_cuenta', [
        'prestamo'          => $prestamo,
        'cuotasOriginales'  => $cuotasOriginales,
        'pendientes'        => $pendientes,
        'pagadas'           => $pagadas,
        'totalesOriginal'   => $totalesOriginal,
        'totalesPendientes' => $totalesPendientes,
        'totalesPagadas'    => $totalesPagadas,
    ])->setPaper('letter', 'portrait');

    $nombreArchivo = 'Estado_de_Cuenta_' .
        str_replace(' ', '_', $prestamo->cliente->nombre_completo) .
        '_' . $prestamo->id . '.pdf';

    return $pdf->download($nombreArchivo);
}

/**
 * 🔧 Formatea fechas sin errores de formato.
 */
private function formatearFechaSeguro($fecha)
{
    if (empty($fecha)) {
        return '-';
    }

    try {
        // Detecta formato y convierte
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $fecha)) {
            // formato Y-m-d
            return \Carbon\Carbon::createFromFormat('Y-m-d', $fecha)->format('d/m/Y');
        } elseif (preg_match('/\d{2}\/\d{2}\/\d{4}/', $fecha)) {
            // formato d/m/Y
            return $fecha; // ya está formateada
        } else {
            // Intentar parsear automáticamente
            return \Carbon\Carbon::parse($fecha)->format('d/m/Y');
        }
    } catch (\Exception $e) {
        return '-';
    }
}

public function verEstadoCuentaPDF($id)
{
    $prestamo = Prestamo::with('cliente')->findOrFail($id);

    // 🔹 Plan original completo
    $cuotasOriginales = $this->generarPlanOriginal($prestamo);
    foreach ($cuotasOriginales as &$cuota) {
        $cuota['capital_original'] = $cuota['capital'] ?? 0;
        $cuota['interes_original'] = $cuota['interes'] ?? 0;
        $cuota['vence'] = $this->formatearFechaSeguro($cuota['vence'] ?? null);
    }
    unset($cuota);

    // 🔹 Cuotas pendientes
    $pendientes = $this->generarPlanPagos($prestamo);
    foreach ($pendientes as &$cuota) {
        $cuota['vence'] = $this->formatearFechaSeguro($cuota['vence'] ?? null);
    }
    unset($cuota);

    // 🔹 Filtrar solo las cuotas no pagadas o parciales
    $pendientes = collect($pendientes)
        ->filter(fn($cuota) => ($cuota['capital'] ?? 0) + ($cuota['interes'] ?? 0) + ($cuota['recargos'] ?? 0) + ($cuota['mora'] ?? 0) > 0)
        ->values()
        ->toArray();

    // 🔹 Cuotas pagadas como recibos
    $pagadas = Recibo::with('detalles')
        ->where('prestamo_id', $prestamo->id)
        ->orderBy('fecha_pago', 'asc')
        ->get()
        ->map(function($recibo) {
            return [
                'nro' => $recibo->id,
                'vence' => \Carbon\Carbon::parse($recibo->fecha_pago)->format('d/m/Y'),
                'capital' => $recibo->capital,
                'interes' => $recibo->interes,
                'recargos' => $recibo->detalles->sum('recargo'),
                'mora' => $recibo->detalles->sum('mora'),
                'total' => $recibo->capital + $recibo->interes + $recibo->detalles->sum('recargo') + $recibo->detalles->sum('mora'),
            ];
        })->toArray();

    // 🔹 Totales
    $totalesOriginal = [
        'capital' => array_sum(array_column($cuotasOriginales, 'capital_original')),
        'interes' => array_sum(array_column($cuotasOriginales, 'interes_original')),
        'total'   => array_sum(array_map(fn($c) => ($c['capital_original'] ?? 0) + ($c['interes_original'] ?? 0), $cuotasOriginales)),
    ];

    $totalesPendientes = [
        'capital'  => array_sum(array_column($pendientes, 'capital')),
        'interes'  => array_sum(array_column($pendientes, 'interes')),
        'recargos' => array_sum(array_column($pendientes, 'recargos')),
        'mora'     => array_sum(array_column($pendientes, 'mora')),
        'total'    => array_sum(array_map(fn($c) => ($c['capital'] ?? 0) + ($c['interes'] ?? 0) + ($c['recargos'] ?? 0) + ($c['mora'] ?? 0), $pendientes)),
    ];

    $totalesPagadas = [
        'capital'  => array_sum(array_column($pagadas, 'capital')),
        'interes'  => array_sum(array_column($pagadas, 'interes')),
        'recargos' => array_sum(array_column($pagadas, 'recargos')),
        'mora'     => array_sum(array_column($pagadas, 'mora')),
        'total'    => array_sum(array_column($pagadas, 'total')),
    ];

    // 🔹 Generar PDF
    $pdf = Pdf::loadView('prestamos.pdf.estado_cuenta', [
        'prestamo'          => $prestamo,
        'cuotasOriginales'  => $cuotasOriginales,
        'pendientes'        => $pendientes,
        'pagadas'           => $pagadas,
        'totalesOriginal'   => $totalesOriginal,
        'totalesPendientes' => $totalesPendientes,
        'totalesPagadas'    => $totalesPagadas,
    ])->setPaper('letter', 'portrait');

    // 🔹 Mostrar en navegador (stream) en lugar de descargar
    return $pdf->stream('Estado_de_Cuenta_' . str_replace(' ', '_', $prestamo->cliente->nombre_completo) . '_' . $prestamo->id . '.pdf');
}


public function pagosHoy()
{
    $hoy = Carbon::today();
    $cuotasHoy = [];
    $cuotasAtrasadas = [];

    // Traer todos los prestamos activos
    $prestamos = Prestamo::with('cliente')->where('estado', 'Activo')->get();

    foreach ($prestamos as $prestamo) {
        // Generar las cuotas con tu método actual
        $cuotas = $this->generarPlanPagos($prestamo);

        foreach ($cuotas as $cuota) {
            $fechaVence = Carbon::parse($cuota['vence']);
            $estado = $cuota['estado'];

            // Pagos que vencen hoy
            if ($fechaVence->isToday() && $estado === 'Pendiente') {
                $cuotasHoy[] = array_merge($cuota, ['cliente' => $prestamo->cliente]);
            }

            // Pagos atrasados
            if ($fechaVence->isPast() && in_array($estado, ['Pendiente','Parcial','Vencida'])) {
                $cuotasAtrasadas[] = array_merge($cuota, ['cliente' => $prestamo->cliente]);
            }
        }
    }

    return view('pagos.hoy', compact('cuotasHoy', 'cuotasAtrasadas'));
}

}