<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetallePago;
use Symfony\Component\HttpFoundation\StreamedResponse; // ✅ Importar correctamente
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
public function index()
{
    // Recupera todos los reportes guardados en sesión
    $reportes = session('reportes', []);

    // Retorna la vista con los reportes
    return view('reportes.pagos', compact('reportes'));
}

public function generarReporte(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    $reportes = session('reportes', []);

    $nuevoReporte = [
        'inicio' => $request->inicio,
        'fin' => $request->fin,
    ];

    array_unshift($reportes, $nuevoReporte); // Nuevo al inicio
    session(['reportes' => $reportes]);

    return redirect()->route('reportes.index')
                     ->with('mensaje', 'Reporte generado correctamente');
}


public function exportarExcel(Request $request)
{
    $request->validate([
        'inicio' => 'required|date',
        'fin' => 'required|date',
    ]);

    // Usar la tabla correcta
    $pagos = DetallePago::with('prestamo.cliente')
                ->whereBetween('created_at', [$request->inicio, $request->fin])
                ->get();

    if ($pagos->isEmpty()) {
        return back()->with('mensaje', 'No hay pagos registrados en ese rango de fechas.');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Fuente y tamaño general
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);

    // Encabezado del reporte
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

    // Encabezados de la tabla
    $sheet->setCellValue('A5', 'Cliente');
    $sheet->setCellValue('B5', 'N° Cuota');
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

    foreach ($pagos as $pago) {
        $sheet->setCellValue('A' . $row, $pago->prestamo->cliente->nombre_completo);
        $sheet->setCellValue('B' . $row, $pago->cuota_numero);
        $sheet->setCellValue('C' . $row, $pago->capital);
        $sheet->setCellValue('D' . $row, $pago->interes);
        $sheet->setCellValue('E' . $row, $pago->capital + $pago->interes);

        $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $totalCapital += $pago->capital;
        $totalInteres += $pago->interes;
        $totalGeneral += $pago->capital + $pago->interes;

        $row++;
    }

    // Fila de totales
    $sheet->setCellValue('A' . $row, 'Totales');
    $sheet->mergeCells("A{$row}:B{$row}");
    $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('C' . $row, $totalCapital);
    $sheet->setCellValue('D' . $row, $totalInteres);
    $sheet->setCellValue('E' . $row, $totalGeneral);
    $sheet->getStyle("C{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

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
    $inicio = Carbon::parse($request->inicio)->startOfDay();
    $fin = Carbon::parse($request->fin)->endOfDay();

    $pagos = DetallePago::with('prestamo.cliente')
             ->whereBetween('created_at', [$inicio, $fin])
             ->get();

    if($pagos->isEmpty()) {
        return back()->with('mensaje', 'No hay pagos registrados en ese rango de fechas.');
    }

    $pdf = Pdf::loadView('reportes.pdf', compact('pagos', 'inicio', 'fin'));

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

}