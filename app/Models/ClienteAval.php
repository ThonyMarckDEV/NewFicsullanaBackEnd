<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteAval extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cliente_avales';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_Cliente',
        'dniAval',
        'apellidoPaternoAval',
        'apellidoMaternoAval',
        'nombresAval',
        'telefonoFijoAval',
        'telefonoMovilAval',
        'direccionAval',
        'referenciaDomicilioAval',
        'departamentoAval',
        'provinciaAval',
        'distritoAval',
        'relacionClienteAval',
    ];

    /**
     * Obtiene el usuario (cliente) que es avalado.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_Cliente' , 'id');
    }
}