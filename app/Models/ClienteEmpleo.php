<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteEmpleo extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cliente_empleos';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_Datos',
        'centroLaboral',
        'ingresoMensual',
        'inicioLaboral',
        'situacionLaboral',
    ];

    /**
     * Obtiene los datos personales asociados al empleo.
     */
    public function dato(): BelongsTo
    {
        return $this->belongsTo(Datos::class, 'id_Datos' ,'id');
    }
}