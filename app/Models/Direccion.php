<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'direcciones';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_Datos',
        'direccionFiscal',
        'direccionCorrespondencia',
        'departamento',
        'provincia',
        'distrito',
        'tipoVivienda',
        'tiempoResidencia',
        'referenciaDomicilio',
    ];

    /**
     * Obtiene los datos personales asociados a la dirección.
     */
    public function dato(): BelongsTo
    {
        return $this->belongsTo(Datos::class, 'id_Datos' , 'id');
    }
}