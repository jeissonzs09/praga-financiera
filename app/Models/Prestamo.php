<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
    'cliente_id',
    'tipo_prestamo',
    'tipo_interes',
    'porcentaje_interes',
    'plazo',
    'valor_prestamo',
    'periodo',
    'estado',
];


    public function cliente()
    {
        // 'cliente_id' en prestamos -> 'id_cliente' en clientes
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }
}
