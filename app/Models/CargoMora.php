<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoMora extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cargos_mora';

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * Tu migración no los incluye, por lo que lo ponemos en false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dias',
        'monto_300_1000',
        'monto_1001_2000',
        'monto_2001_3000',
        'monto_3001_4000',
        'monto_4001_5000',
        'monto_5001_6000',
        'monto_mas_6000',
    ];
}