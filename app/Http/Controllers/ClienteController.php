<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // Mostrar listado de clientes
public function index(Request $request)
{
    $query = Cliente::query();

    // Filtro por nombre si se recibe search
    if ($request->has('search') && $request->search != '') {
        $query->where('nombre_completo', 'like', '%' . $request->search . '%');
    }

    // Ordenar por fecha de creación descendente
    $clientes = $query->orderBy('created_at', 'desc')->get();

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
        // --- SOLICITUD ---
        'fecha_solicitud' => 'nullable|date',
        'fecha_aprobacion' => 'nullable|date',
        'motivo_credito' => 'nullable|string|max:255',

        // --- DATOS PERSONALES ---
        'nombre_completo' => 'required|string|max:255',
        'fecha_nacimiento' => 'nullable|date',
        'edad' => 'nullable|integer',
        'nacionalidad' => 'nullable|string|max:100',
        'celular' => 'nullable|string|max:20',
        'identificacion' => 'nullable|string|max:50',
        'rtn' => 'nullable|string|max:50',
        'sexo' => 'nullable|string',
        'tipo_identificacion' => 'nullable|array',
        'estado_civil' => 'nullable|string',
        'direccion' => 'nullable|string',
        'telefono_residencia' => 'nullable|string|max:20',

        // --- DATOS DEL CÓNYUGE ---
        'conyuge_nombre' => 'nullable|string|max:255',
        'conyuge_telefono' => 'nullable|string|max:20',
        'conyuge_celular' => 'nullable|string|max:20',

        // --- INFORMACIÓN LABORAL ---
        'correo' => 'nullable|string|max:150',
        'hijos' => 'nullable|string|max:100',
        'hijas' => 'nullable|string|max:100',
        'profesion' => 'nullable|string|max:150',
        'negocio' => 'nullable|string|max:150',
        'actividad_economica' => 'nullable|string|max:150',
        'cargo' => 'nullable|string|max:100',
        'tipo_labor' => 'nullable|string|max:100',
        'empresa' => 'nullable|string|max:150',
        'direccion_empresa' => 'nullable|string',
        'telefono_trabajo' => 'nullable|string|max:20',

        // --- REFERENCIAS ---
        'referencia1' => 'nullable|string|max:150',
        'referencia2' => 'nullable|string|max:150',

        // --- INGRESOS Y GARANTÍAS ---
        'ingresos' => 'nullable|array',
        'nivel_ingreso' => 'nullable|array',
        'garantia' => 'nullable|array',

        // --- ARCHIVOS ---
        'identidad_img.*' => 'nullable|image|mimes:jpg,jpeg,png|max:20480',
        'fotos_garantias.*' => 'nullable|image|mimes:jpg,jpeg,png|max:20480',
        'contrato_pdf' => 'nullable|mimes:pdf|max:5120',

        // --- DECLARACIÓN ---
        'declaracion' => 'nullable|string',
    ]);

    // Convertir arrays a string/JSON
    foreach (['ingresos', 'nivel_ingreso', 'garantia', 'tipo_identificacion'] as $campo) {
        if (isset($data[$campo]) && is_array($data[$campo])) {
            $data[$campo] = json_encode($data[$campo]);
        }
    }

    // Procesar archivos
    if ($request->hasFile('identidad_img')) {
        $paths = [];
        foreach ($request->file('identidad_img') as $file) {
            $paths[] = $file->store('clientes/identidad', 'public');
        }
        $data['identidad_img'] = json_encode($paths);
    }

    if ($request->hasFile('fotos_garantias')) {
        $paths = [];
        foreach ($request->file('fotos_garantias') as $file) {
            $paths[] = $file->store('clientes/garantias', 'public');
        }
        $data['fotos_garantias'] = json_encode($paths);
    }

    if ($request->hasFile('contrato_pdf')) {
        $data['contrato_pdf'] = $request->file('contrato_pdf')->store('clientes/contratos', 'public');
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