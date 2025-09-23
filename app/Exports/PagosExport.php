<?php

namespace App\Exports;

use App\Models\ReciboPago;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PagosExport implements FromCollection, WithHeadings
{
    protected $inicio;
    protected $fin;
    protected $prestamo_id;

    public function __construct($inicio, $fin, $prestamo_id)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
        $this->prestamo_id = $prestamo_id;
    }

    public function collection()
    {
        return ReciboPago::where('prestamo_id', $this->prestamo_id)
            ->whereBetween('fecha_pago', [$this->inicio, $this->fin])
            ->get(['cliente_id', 'cuota_numero', 'capital', 'interes', 'total']);
    }

    public function headings(): array
    {
        return ['Cliente', 'N° Cuota', 'Capital', 'Interés', 'Total'];
    }
}