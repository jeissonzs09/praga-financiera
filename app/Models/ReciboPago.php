<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboPago extends Model
{
    protected $table = 'recibo_pagos';
    protected $primaryKey = 'id'; // tu tabla usa 'id' como PK
    public $incrementing = true;

    protected $fillable = [
        'prestamo_id',
        'monto',
        'metodo_pago',
        'observaciones',
        'fecha_pago',
    ];

    public function detalles()
    {
        // FK en detalle_pagos es 'id_recibo'
        return $this->hasMany(DetallePago::class, 'id_recibo', 'id');
    }

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }
}

