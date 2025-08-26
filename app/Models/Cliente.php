<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente'; // clave primaria real en tu BD
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        // Datos personales
        'nombre_completo',
        'fecha_nacimiento',
        'edad',
        'nacionalidad',
        'celular',
        'identificacion',
        'rtn',
        'sexo',
        'estado_civil',
        'direccion',

        // Cónyuge
        'conyuge_nombre',
        'conyuge_telefono',
        'conyuge_celular',

        // Laboral
        'correo',
        'hijos',
        'profesion',
        'negocio',
        'actividad_economica',
        'cargo',
        'tipo_labor',
        'direccion_empresa',
        'telefono_trabajo',

        // Referencias
        'referencia1',
        'referencia2',

        // Ingresos
        'ingresos',

        // Declaración
        'declaracion',
    ];

    // Relación con préstamos
    public function prestamos()
    {
        // cliente_id en préstamos → id_cliente en clientes
        return $this->hasMany(Prestamo::class, 'cliente_id', 'id_cliente');
    }
}