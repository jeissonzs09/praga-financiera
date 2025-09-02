<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Cliente;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    // Listado de préstamos activos
    public function index(Request $request)
{
    $query = Prestamo::with('cliente')->latest();

    // 📌 Filtro por nombre de cliente
    if ($request->filled('buscar')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_completo', 'like', '%' . $request->buscar . '%');
        });
    }

    // 📌 Filtro por estado
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    $prestamos = $query->get();

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

public function destroy(Prestamo $prestamo)
{
    // Si quieres, puedes validar aquí también que no esté finalizado
    if (strtolower($prestamo->estado) === 'finalizado') {
        return redirect()->route('prestamos.index')
            ->with('error', 'No se puede eliminar un préstamo finalizado.');
    }

    $prestamo->delete();

    return redirect()->route('prestamos.index')
        ->with('success', 'Préstamo eliminado correctamente.');
}

}