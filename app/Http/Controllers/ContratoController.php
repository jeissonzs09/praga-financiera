<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Barryvdh\DomPDF\Facade\Pdf;

class ContratoController extends Controller
{
    public function index()
    {
        // Traemos los préstamos con cliente, igual que en prestamos.index
        $prestamos = Prestamo::with('cliente')->latest()->paginate(10);

        return view('contratos.index', compact('prestamos'));
    }

    public function generarPdf(Prestamo $prestamo)
    {
        $prestamo->load('cliente');

        // Cargar la plantilla Blade del contrato
        $pdf = Pdf::loadView('contratos.plantilla', compact('prestamo'))
                  ->setPaper('letter');

        return $pdf->download("Contrato-Prestamo-{$prestamo->id}.pdf");
    }
}
