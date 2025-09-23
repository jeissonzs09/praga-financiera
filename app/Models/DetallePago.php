<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePago extends Model
{
    protected $table = 'detalle_pagos';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'cuota_numero',
        'prestamo_id',
        'capital',
        'interes',
        'recargo',
        'mora',
        'total',
        'id_recibo',
    ];

    public function recibo()
    {
        // detalle_pagos.id_recibo → recibo_pagos.id
        return $this->belongsTo(ReciboPago::class, 'id_recibo', 'id');
    }

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }
}