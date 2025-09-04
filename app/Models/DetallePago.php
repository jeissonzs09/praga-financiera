<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePago extends Model
{
    protected $table = 'detalle_pagos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_recibo',
        'cuota_numero',
        'capital',
        'interes',
        'recargo',
        'mora',
        'total'
    ];

    // Relación inversa con pago
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }
}