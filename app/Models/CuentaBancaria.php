<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaBancaria extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cuentas_bancarias';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_Datos',
        'ctaAhorros',
        'cci',
        'entidadFinanciera',
    ];

    /**
     * Obtiene los datos personales asociados a la cuenta bancaria.
     */
    public function dato(): BelongsTo
    {
        return $this->belongsTo(Datos::class, 'id_Datos' , 'id');
    }
}