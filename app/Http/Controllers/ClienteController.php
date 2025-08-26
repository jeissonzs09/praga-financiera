<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // Mostrar listado de clientes
    public function index()
    {
        $clientes = Cliente::orderBy('created_at', 'desc')->get();
        return view('clientes.index', compact('clientes'));
    }

    // Mostrar formulario de registro
    public function create()
    {
        return view('clientes.create');
    }

    // Guardar cliente en la base de datos
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'edad' => 'nullable|integer',
            'nacionalidad' => 'nullable|string|max:100',
            'celular' => 'nullable|string|max:20',
            'identificacion' => 'nullable|string|max:50',
            'rtn' => 'nullable|string|max:50',
            'sexo' => 'nullable|string',
            'estado_civil' => 'nullable|string',
            'direccion' => 'nullable|string',

            'conyuge_nombre' => 'nullable|string|max:255',
            'conyuge_telefono' => 'nullable|string|max:20',
            'conyuge_celular' => 'nullable|string|max:20',

            'correo' => 'nullable|string|max:150',
            'hijos' => 'nullable|string|max:100',
            'profesion' => 'nullable|string|max:150',
            'negocio' => 'nullable|string|max:150',
            'actividad_economica' => 'nullable|string|max:150',
            'cargo' => 'nullable|string|max:100',
            'tipo_labor' => 'nullable|string|max:100',
            'direccion_empresa' => 'nullable|string',
            'telefono_trabajo' => 'nullable|string|max:20',

            'referencia1' => 'nullable|string|max:150',
            'referencia2' => 'nullable|string|max:150',

            'ingresos' => 'nullable|array',
            'declaracion' => 'nullable|string',
        ]);

        // Convertir ingresos a string si es array
        if (isset($data['ingresos'])) {
            $data['ingresos'] = implode(',', $data['ingresos']);
        }

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    // Mostrar detalle de un cliente
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.show', compact('cliente'));
    }
}