<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Cliente;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    // Listado de préstamos activos
    public function index()
    {
        $prestamos = Prestamo::where('estado', 'Activo')
                             ->with('cliente')
                             ->latest()
                             ->get();

        return view('prestamos.index', compact('prestamos'));
    }

    // Formulario para crear un préstamo
    public function create()
    {
        $clientes = Cliente::all();
        return view('prestamos.create', compact('clientes'));
    }

    // Guardar un préstamo nuevo
public function store(Request $request)
{
    $validated = $request->validate([
        'cliente_id' => 'required|exists:clientes,id_cliente',
        'tipo_prestamo' => 'required',
        'tipo_interes' => 'required',
        'porcentaje_interes' => 'required|numeric|min:0',
        'plazo' => 'required|integer|min:1',
        'valor_prestamo' => 'required|numeric|min:0',
        'periodo' => 'required'
    ]);

    Prestamo::create($validated + [
        'estado' => 'Activo'
    ]);

    return redirect()->route('prestamos.index')
                     ->with('success', 'Préstamo guardado correctamente.');
}
}