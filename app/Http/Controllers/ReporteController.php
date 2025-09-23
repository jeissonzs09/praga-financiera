<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportePagosExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;
use App\Models\Prestamo;



class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function generar(Request $request)
    {
        $inicio = $request->input('inicio');
        $fin = $request->input('fin');

        return Excel::download(new ReportePagosExport($inicio, $fin), "Reporte-Pagos-{$inicio}_{$fin}.xlsx");
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


public function generarExcel(Request $request)
{
    $inicio = $request->input('inicio');
    $fin = $request->input('fin');
    $prestamoId = $request->input('prestamo_id');

    $prestamo = Prestamo::findOrFail($prestamoId);
    $cuotas = $this->generarPlan($prestamo);

    // 🔹 Filtrar cuotas por fecha de vencimiento
    $filtradas = collect($cuotas)->filter(function ($cuota) use ($inicio, $fin) {
        return $cuota['vence'] >= $inicio && $cuota['vence'] <= $fin;
    });

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 🔹 Encabezados
    $sheet->setCellValue('A1', '#');
    $sheet->setCellValue('B1', 'Número de Cuota');
    $sheet->setCellValue('C1', 'Fecha Vencimiento');
    $sheet->setCellValue('D1', 'Capital');
    $sheet->setCellValue('E1', 'Interés');
    $sheet->setCellValue('F1', 'Total');
    $sheet->setCellValue('G1', 'Estado');
    $sheet->setCellValue('H1', 'Tardío');

    $fila = 2;
    $capitalTotal = 0;
    $interesTotal = 0;

    foreach ($filtradas as $i => $cuota) {
        $sheet->setCellValue("A{$fila}", $i + 1);
        $sheet->setCellValue("B{$fila}", $cuota['nro']);
        $sheet->setCellValue("C{$fila}", $cuota['vence']);
        $sheet->setCellValue("D{$fila}", $cuota['capital']);
        $sheet->setCellValue("E{$fila}", $cuota['interes']);
        $sheet->setCellValue("F{$fila}", $cuota['total']);
        $sheet->setCellValue("G{$fila}", $cuota['estado']);
        $sheet->setCellValue("H{$fila}", $cuota['es_tardio'] ? 'Sí' : 'No');

        $capitalTotal += $cuota['capital'];
        $interesTotal += $cuota['interes'];
        $fila++;
    }

    // 🔹 Totales
    $sheet->setCellValue("C{$fila}", 'TOTAL');
    $sheet->setCellValue("D{$fila}", $capitalTotal);
    $sheet->setCellValue("E{$fila}", $interesTotal);
    $sheet->setCellValue("F{$fila}", $capitalTotal + $interesTotal);

    // 🔹 Descargar
    $writer = new Xlsx($spreadsheet);
    $filename = "Reporte-Pagos-{$inicio}_{$fin}.xlsx";
    $tempFile = tempnam(sys_get_temp_dir(), $filename);
    $writer->save($tempFile);

    return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
}


}
