<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        // --- SOLICITUD ---
        'fecha_solicitud',
        'fecha_aprobacion',
        'motivo_credito',

        // --- DATOS PERSONALES ---
        'nombre_completo',
        'fecha_nacimiento',
        'edad',
        'nacionalidad',
        'celular',
        'identificacion',
        'rtn',
        'sexo',
        'tipo_identificacion',
        'estado_civil',
        'direccion',
        'telefono_residencia',
        'domicilio',
        'ciudad',
        'departamento',

        // --- CÓNYUGE ---
        'conyuge_nombre',
        'conyuge_telefono',
        'conyuge_celular',

        // --- LABORAL ---
        'correo',
        'hijos',
        'hijas',
        'profesion',
        'negocio',
        'actividad_economica',
        'cargo',
        'tipo_labor',
        'empresa',
        'direccion_empresa',
        'telefono_trabajo',

        // --- REFERENCIAS ---
        'referencia1_nombre',
        'referencia1_telefono',
        'referencia2_nombre',
         'referencia2_telefono',

        // --- INGRESOS Y GARANTÍAS ---
        'ingresos',
        'nivel_ingreso',
        'garantia',
        'ingreso_mensual',

        // --- ARCHIVOS ---
        'identidad_img',
        'fotos_garantias',
        'contrato_pdf',

        // --- DECLARACIÓN ---
        'declaracion',

    ];

    // Relación con préstamos
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cliente_id', 'id_cliente');
    }

    // App\Models\Cliente.php
public function referencias()
{
    return $this->hasMany(Referencia::class, 'cliente_id', 'id_cliente');
}

}