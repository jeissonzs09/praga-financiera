<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportePagosExport implements FromArray, WithHeadings
{
    protected $inicio;
    protected $fin;

    public function __construct($inicio, $fin)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
    }

    public function array(): array
    {
        $pagos = Pago::whereBetween('fecha_pago', [$this->inicio, $this->fin])
            ->orderBy('fecha_pago')
            ->get();

        $data = [];
        $capitalTotal = 0;
        $interesTotal = 0;

        foreach ($pagos as $i => $pago) {
            $capital = $pago->capital;
            $interes = $pago->interes;
            $total = $capital + $interes;

            $capitalTotal += $capital;
            $interesTotal += $interes;

            $data[] = [
                $i + 1,
                $pago->numero_cuota,
                number_format($capital, 2),
                number_format($interes, 2),
                number_format($total, 2),
            ];
        }

        // 🔹 Totales al final
        $data[] = ['', '', '', '', ''];
        $data[] = ['TOTAL', '', number_format($capitalTotal, 2), number_format($interesTotal, 2), number_format($capitalTotal + $interesTotal, 2)];

        return $data;
    }

    public function headings(): array
    {
        return ['#', 'Número de Cuota', 'Capital', 'Interés', 'Total'];
    }
}

