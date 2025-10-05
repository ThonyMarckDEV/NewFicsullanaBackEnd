<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'numero_operacion',
        'id_Cuota',
        'monto_pagado',
        'excedente',
        'fecha_pago',
        'observaciones',
        'id_Usuario',
        'modalidad'
    ];

    public function cuota()
    {
        return $this->belongsTo(Cuota::class, 'id_Cuota');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_Usuario');
    }
}