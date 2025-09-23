<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $table = 'recibo_pagos';
    protected $primaryKey = 'id_recibo';
    public $timestamps = true;

    protected $fillable = [
        'prestamo_id',
        'monto_total',
        'observaciones'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePago::class, 'id_recibo', 'id_recibo');
    }

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id', 'id');
    }
}
