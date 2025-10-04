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
        // --- NUEVOS CAMPOS ---
'domicilio' => 'nullable|string|max:255',
'ciudad' => 'nullable|string|max:100',
'departamento' => 'nullable|string|max:100',

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
'referencia1_nombre' => 'nullable|string|max:150',
'referencia1_telefono' => 'nullable|string|max:20',
'referencia2_nombre' => 'nullable|string|max:150',
'referencia2_telefono' => 'nullable|string|max:20',

        // --- INGRESOS Y GARANTÍAS ---
        'ingresos' => 'nullable|array',
        'nivel_ingreso' => 'nullable|array',
        'garantia' => 'nullable|array',

        // --- ARCHIVOS ---
        'identidad_img.*' => 'nullable|image|mimes:jpg,jpeg,png|max:20480',
        'fotos_garantias.*' => 'nullable|image|mimes:jpg,jpeg,png|max:20480',
        'contrato_pdf.*' => 'nullable|mimes:pdf|max:5120',

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
    $contratos = [];
    foreach ($request->file('contrato_pdf') as $archivo) {
        $nombreOriginal = time().'_'.$archivo->getClientOriginalName();
        $ruta = $archivo->storeAs('clientes/contratos', $nombreOriginal, 'public');
        $contratos[] = $ruta;
    }
    $data['contrato_pdf'] = json_encode($contratos);
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

    public function edit($id)
{
    $cliente = Cliente::findOrFail($id);
    return view('clientes.edit', compact('cliente'));
}

public function update(Request $request, $id)
{
    $cliente = Cliente::findOrFail($id);

    // Validación
    $request->validate([
        // Solicitud / Crédito
        'fecha_solicitud' => 'nullable|date',
        'fecha_aprobacion' => 'nullable|date',
        'motivo_credito' => 'nullable|string|max:255',

        // Datos personales
        'nombre_completo' => 'required|string|max:150',
        'fecha_nacimiento' => 'nullable|date',
        'edad' => 'nullable|integer',
        'nacionalidad' => 'nullable|string|max:50',
        'celular' => 'nullable|string|max:20',
        'telefono_residencia' => 'nullable|string|max:20',
        'identificacion' => 'nullable|string|max:50',
        'rtn' => 'nullable|string|max:50',
        'sexo' => 'nullable|string|max:15',
        'estado_civil' => 'nullable|string|max:50',
        'domicilio' => 'nullable|string|max:255',
        'ciudad' => 'nullable|string|max:100',
        'departamento' => 'nullable|string|max:100',
        'direccion' => 'nullable|string|max:100',
        'hijos' => 'nullable|integer',
        'hijas' => 'nullable|integer',

        // Tipo de identificación (JSON)
        'tipo_identificacion' => 'nullable|array',

        // Datos del cónyuge
        'conyuge_nombre' => 'nullable|string|max:150',
        'conyuge_telefono' => 'nullable|string|max:20',
        'conyuge_celular' => 'nullable|string|max:20',

        // Información laboral
        'correo' => 'nullable|email|max:150',
        'profesion' => 'nullable|string|max:150',
        'negocio' => 'nullable|string|max:150',
        'actividad_economica' => 'nullable|string|max:150',
        'cargo' => 'nullable|string|max:100',
        'tipo_labor' => 'nullable|string|max:50',
        'empresa' => 'nullable|string|max:150',
        'direccion_empresa' => 'nullable|string|max:255',
        'telefono_trabajo' => 'nullable|string|max:20',

        // Referencias
        'referencia1_nombre' => 'nullable|string|max:150',
        'referencia1_telefono' => 'nullable|string|max:20',
        'referencia2_nombre' => 'nullable|string|max:150',
        'referencia2_telefono' => 'nullable|string|max:20',

        // Origen de ingresos / Nivel de ingresos / Garantías
        'ingresos' => 'nullable|array',
        'nivel_ingreso' => 'nullable|array',
        'garantia' => 'nullable|array',

        // Declaración
        'declaracion' => 'nullable|string',

        // Archivos
        'identidad_img.*' => 'nullable|image|max:2048',
        'fotos_garantias.*' => 'nullable|image|max:2048',
        'contrato_pdf.*' => 'nullable|mimes:pdf|max:5120',
    ]);

    $data = $request->all();

    // Convertir arrays a JSON
    $data['tipo_identificacion'] = json_encode($request->tipo_identificacion ?? []);
    $data['nivel_ingreso'] = json_encode($request->nivel_ingreso ?? []);
    $data['ingresos'] = json_encode($request->ingresos ?? []);
    $data['garantia'] = json_encode($request->garantia ?? []);

    // --- Archivos ---

    // Identidad
    if($request->hasFile('identidad_img')){
        $identidadImgs = json_decode($cliente->identidad_img ?? '[]', true) ?: [];
        foreach($request->file('identidad_img') as $img){
            $ruta = $img->store('clientes/identidad', 'public');
            $identidadImgs[] = $ruta;
        }
        $data['identidad_img'] = json_encode($identidadImgs);
    }

    // Fotos de garantías
    if($request->hasFile('fotos_garantias')){
        $fotosGarantias = json_decode($cliente->fotos_garantias ?? '[]', true) ?: [];
        foreach($request->file('fotos_garantias') as $img){
            $ruta = $img->store('clientes/garantias', 'public');
            $fotosGarantias[] = $ruta;
        }
        $data['fotos_garantias'] = json_encode($fotosGarantias);
    }

    // Contratos
    $contratosExistentes = json_decode($cliente->contrato_pdf ?? '[]', true) ?: [];

    // Eliminar archivos seleccionados
    $eliminados = json_decode($request->input('contratos_eliminados', '[]'), true) ?: [];
    foreach($eliminados as $index){
        if(isset($contratosExistentes[$index])){
            \Storage::disk('public')->delete($contratosExistentes[$index]);
            unset($contratosExistentes[$index]);
        }
    }

// Subir nuevos contratos con nombre original
if($request->hasFile('contrato_pdf')){
    foreach($request->file('contrato_pdf') as $archivo){
        $nombreOriginal = time() . '_' . $archivo->getClientOriginalName(); // evita sobrescribir
        $ruta = $archivo->storeAs('clientes/contratos', $nombreOriginal, 'public');
        $contratosExistentes[] = $ruta;
    }
}

    $data['contrato_pdf'] = json_encode(array_values($contratosExistentes));

    // Actualizar cliente
    $cliente->update($data);

    return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
}

public function destroy($id)
{
    $cliente = Cliente::findOrFail($id);
    $cliente->delete();

    return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
}


}