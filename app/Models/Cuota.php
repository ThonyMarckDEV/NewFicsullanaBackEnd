<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     * @var string
     */
    protected $table = 'cuotas';

    /**
     * Atributos que son asignables masivamente (Mass Assignable).
     * Esto protege contra asignación masiva de campos sensibles.
     * @var array
     */
    protected $fillable = [
        'id_Prestamo',
        'numero_cuota',
        'monto',
        'capital',
        'interes',
        'otros',
        'excedente_anterior',
        'fecha_vencimiento',
        'estado',
        'dias_mora',
        'cargo_mora',
        'mora_aplicada',
        'mora_reducida',
        'reduccion_mora_aplicada',
        'fecha_mora_aplicada',
        'observaciones',
    ];

    /**
     * Conversiones automáticas de tipos de datos (Casting).
     * @var array
     */
    protected $casts = [
        'fecha_vencimiento' => 'date',
        'mora_aplicada' => 'boolean',
        'reduccion_mora_aplicada' => 'boolean',
        'fecha_mora_aplicada' => 'datetime',
    ];
    
    // --- RELACIONES ---

    /**
     * Define la relación inversa de UNO a MUCHOS con el modelo Prestamo.
     * Una Cuota pertenece a un solo Préstamo.
     */
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_Prestamo');
    }
}