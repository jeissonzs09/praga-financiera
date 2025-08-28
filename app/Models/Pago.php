<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    // Nombre de la clave primaria real en la tabla
    protected $primaryKey = 'id_pago';

    // Si no es incrementing o no es int, aquí lo ajustas, pero en tu caso sí lo es
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'prestamo_id',
        'fecha_pago',
        'monto',
        'observaciones',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id', 'id');
    }
}