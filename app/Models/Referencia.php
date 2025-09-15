<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referencia extends Model
{
    use HasFactory;

    protected $table = 'referencias';
    protected $primaryKey = 'id_referencia';

    // Si solo tienes created_at y no manejas updated_at, lo ponemos así:
    public $timestamps = true;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'telefono',
    ];

    /**
     * Relación: una referencia pertenece a un cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }
}