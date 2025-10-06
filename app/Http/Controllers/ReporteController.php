<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetallePago;
use Symfony\Component\HttpFoundation\StreamedResponse; // ✅ Importar correctamente
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use App\Models\Prestamo;

class ReporteController extends Controller
{
public function index()
{
    // Recupera todos los reportes guardados en sesión
    $reportes = session('reportes', []);

    // Retorna la vista con los reportes
    return view('reportes.pagos', compact('reportes'));
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

public function generarReporte(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin'    => 'required|date',
    ]);

    $reportes = session('reportes', []);

    $nuevoReporte = [
        'inicio' => $request->inicio,
        'fin'    => $request->fin,
        'tipo'   => 'cuotas', // <- clave importante para la vista
    ];

    array_unshift($reportes, $nuevoReporte); // Nuevo al inicio
    session(['reportes' => $reportes]);

    return redirect()->route('reportes.index')
                     ->with('mensaje', 'Reporte de cuotas generado correctamente');
}


public function exportarExcel(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    $inicio = Carbon::parse($request->inicio)->startOfDay();
    $fin = Carbon::parse($request->fin)->endOfDay();

    // Obtenemos todos los pagos del rango
    $pagos = DetallePago::with('prestamo.cliente')
                ->whereBetween('fecha_pago', [$inicio, $fin])
                ->get();

    if ($pagos->isEmpty()) {
        return back()->with('mensaje', 'No hay pagos registrados en ese rango de fechas.');
    }

    // 🔹 Agrupar por cliente
    $pagosPorCliente = $pagos->groupBy(function($pago) {
        return $pago->prestamo->cliente->nombre_completo;
    });

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configuración general
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

    // Encabezado principal
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'Inversiones PRAGA S.A.');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Reporte de Cuotas');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:E3');
    $sheet->setCellValue('A3', "Desde: {$request->inicio} | Hasta: {$request->fin}");
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Encabezados de tabla
    $sheet->setCellValue('A5', 'Cliente');
    $sheet->setCellValue('B5', 'N° Cuotas');
    $sheet->setCellValue('C5', 'Capital');
    $sheet->setCellValue('D5', 'Interés');
    $sheet->setCellValue('E5', 'Total');

    $sheet->getStyle('A5:E5')->getFont()->setBold(true);
    $sheet->getStyle('A5:E5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5:E5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $row = 6;
    $totalCapital = 0;
    $totalInteres = 0;
    $totalGeneral = 0;

    // 🔹 Recorrer clientes agrupados
    foreach ($pagosPorCliente as $cliente => $pagosCliente) {
        $numCuotas = $pagosCliente->count();
        $capital = $pagosCliente->sum('capital');
        $interes = $pagosCliente->sum('interes');
        $total = $capital + $interes;

        $sheet->setCellValue("A{$row}", $cliente);
        $sheet->setCellValue("B{$row}", $numCuotas);
        $sheet->setCellValue("C{$row}", $capital);
        $sheet->setCellValue("D{$row}", $interes);
        $sheet->setCellValue("E{$row}", $total);

        $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $totalCapital += $capital;
        $totalInteres += $interes;
        $totalGeneral += $total;

        $row++;
    }

    // Totales generales
    $sheet->setCellValue("A{$row}", 'Totales');
    $sheet->mergeCells("A{$row}:B{$row}");
    $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("C{$row}", $totalCapital);
    $sheet->setCellValue("D{$row}", $totalInteres);
    $sheet->setCellValue("E{$row}", $totalGeneral);
    $sheet->getStyle("C{$row}:E{$row}")->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Formato de números
    $sheet->getStyle("C6:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

    // Autoajustar columnas
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = "Reporte_Cuotas_{$request->inicio}_al_{$request->fin}.xlsx";

    $response = new StreamedResponse(function() use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', "attachment;filename=\"$fileName\"");
    $response->headers->set('Cache-Control','max-age=0');

    return $response;
}


public function exportarPDF(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    $inicio = Carbon::parse($request->inicio)->startOfDay();
    $fin = Carbon::parse($request->fin)->endOfDay();

    // 🔹 Agrupar pagos por cliente y sumar capital, interés y total
$pagos = DB::table('detalle_pagos')
    ->join('prestamos', 'detalle_pagos.prestamo_id', '=', 'prestamos.id')
    ->join('clientes', 'prestamos.cliente_id', '=', 'clientes.id_cliente')
    ->select(
        'clientes.nombre_completo as cliente',
        DB::raw('COUNT(detalle_pagos.cuota_numero) as numero_cuotas'),
        DB::raw('SUM(detalle_pagos.capital) as capital_total'),
        DB::raw('SUM(detalle_pagos.interes) as interes_total'),
        DB::raw('SUM(detalle_pagos.total) as total_general')
    )
    ->whereBetween('detalle_pagos.fecha_pago', [$inicio, $fin])
    ->groupBy('clientes.nombre_completo')
    ->get();

    if ($pagos->isEmpty()) {
        return back()->with('mensaje', 'No hay pagos registrados en ese rango de fechas.');
    }

    $pdf = Pdf::loadView('reportes.pdf', compact('pagos', 'inicio', 'fin'))
              ->setPaper('A4', 'portrait');

    return $pdf->download("Reporte_Pagos_{$request->inicio}_al_{$request->fin}.pdf");
}

public function eliminarReporte($index)
{
    $reportes = session('reportes', []);

    if(isset($reportes[$index])) {
        unset($reportes[$index]);
        // Reindexar el array
        $reportes = array_values($reportes);
        session(['reportes' => $reportes]);
        return back()->with('mensaje', 'Reporte eliminado correctamente');
    }

    return back()->with('mensaje', 'El reporte no existe');
}

public function generarCreditos(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin'    => 'required|date',
    ]);

    $clientes = Cliente::with('prestamos')->get();

    $reporte = [
        'inicio' => $request->inicio,
        'fin'    => $request->fin,
        'tipo'   => 'creditos',
        'clientes' => $clientes->map(function($cliente){
            $capitalPendiente = 0;
            $interesPendiente = 0;

            foreach ($cliente->prestamos as $prestamo) {
                $plan = $this->generarPlanPagos($prestamo);

                foreach ($plan as $cuota) {
                    if ($cuota['estado'] !== 'Pagada') {
                        $capitalPendiente += $cuota['capital'];
                        $interesPendiente += $cuota['interes'];
                    }
                }
            }

            return [
                'nombre'  => $cliente->nombre_completo,
                'capital' => round($capitalPendiente, 2),
                'interes' => round($interesPendiente, 2),
                'total'   => round($capitalPendiente + $interesPendiente, 2),
            ];
        })->toArray(),
    ];

    $reportes = session('reportes', []);
    array_unshift($reportes, $reporte);
    session(['reportes' => $reportes]);

    return redirect()->route('reportes.index')
                     ->with('mensaje', 'Reporte de créditos generado correctamente');
}


public function exportExcelCreditos(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    // Obtener todos los préstamos activos
    $prestamosActivos = Prestamo::where('estado', 'activo')->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configuración general
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

    // Encabezado principal
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'Inversiones PRAGA S.A.');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Reporte de Créditos');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2')->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:E3');
    $sheet->setCellValue('A3', "Desde: {$request->inicio} | Hasta: {$request->fin}");
    $sheet->getStyle('A3')->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Encabezados de tabla
    $sheet->setCellValue('A5', '#');
    $sheet->setCellValue('B5', 'Cliente');
    $sheet->setCellValue('C5', 'Capital Pendiente');
    $sheet->setCellValue('D5', 'Interés Pendiente');
    $sheet->setCellValue('E5', 'Total');

    $sheet->getStyle('A5:E5')->getFont()->setBold(true);
    $sheet->getStyle('A5:E5')->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5:E5')->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $row = 6;
    $i = 1;
    $totalCapital = 0;
    $totalInteres = 0;
    $totalGeneral = 0;

    foreach ($prestamosActivos as $prestamo) {
        $planPagos = $this->generarPlanPagos($prestamo);

        // Sumar TODO el capital e interés del plan (no solo las fechas del rango)
        $capitalPendiente = collect($planPagos)->sum('capital');
        $interesPendiente = collect($planPagos)->sum('interes');
        $total = $capitalPendiente + $interesPendiente;

        if ($total <= 0) continue;

        $sheet->setCellValue("A{$row}", $i++);
        $sheet->setCellValue("B{$row}", $prestamo->cliente->nombre_completo);
        $sheet->setCellValue("C{$row}", $capitalPendiente);
        $sheet->setCellValue("D{$row}", $interesPendiente);
        $sheet->setCellValue("E{$row}", $total);

        $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $totalCapital += $capitalPendiente;
        $totalInteres += $interesPendiente;
        $totalGeneral += $total;

        $row++;
    }

    // Totales generales
    $sheet->setCellValue("A{$row}", 'Totales');
    $sheet->mergeCells("A{$row}:B{$row}");
    $sheet->setCellValue("C{$row}", $totalCapital);
    $sheet->setCellValue("D{$row}", $totalInteres);
    $sheet->setCellValue("E{$row}", $totalGeneral);

    $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $sheet->getStyle("C6:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = "Reporte_Creditos_{$request->inicio}_al_{$request->fin}.xlsx";

    $response = new StreamedResponse(function() use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', "attachment;filename=\"$fileName\"");
    $response->headers->set('Cache-Control','max-age=0');

    return $response;
}

public function generarReporteCreditosPDF(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    $inicio = $request->inicio;
    $fin = $request->fin;

    // Obtener todos los préstamos activos
    $prestamosActivos = Prestamo::where('estado', 'activo')->get();

    $creditos = collect();
    $totalCapital = 0;
    $totalInteres = 0;
    $totalGeneral = 0;

    foreach ($prestamosActivos as $prestamo) {
        $planPagos = $this->generarPlanPagos($prestamo); // tu función de Excel

        $capitalPendiente = collect($planPagos)->sum('capital');
        $interesPendiente = collect($planPagos)->sum('interes');
        $total = $capitalPendiente + $interesPendiente;

        if ($total <= 0) continue;

        $creditos->push([
            'cliente' => $prestamo->cliente->nombre_completo,
            'capital' => $capitalPendiente,
            'interes' => $interesPendiente,
            'total' => $total,
        ]);

        $totalCapital += $capitalPendiente;
        $totalInteres += $interesPendiente;
        $totalGeneral += $total;
    }

    $pdf = Pdf::loadView('reportes.reporte_creditos_pdf', [
        'creditos' => $creditos,
        'inicio' => $inicio,
        'fin' => $fin,
        'totalCapital' => $totalCapital,
        'totalInteres' => $totalInteres,
        'totalGeneral' => $totalGeneral,
    ])
    ->setPaper('letter');
    return $pdf->download("Reporte_Creditos_{$inicio}_al_{$fin}.pdf");
}
}