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
        'cargo_mora',
        'mora_reducida',
        'reduccion_mora_aplicada',
    ];
    
    // Agrega el nuevo campo a la serialización del modelo
    protected $appends = ['original_mora'];

    /**
     * Calcula y devuelve la mora original si se aplicó una reducción.
     *
     * @return float|null
     */
    public function getOriginalMoraAttribute(): ?float
    {
        if ($this->reduccion_mora_aplicada && $this->mora_reducida > 0) {
            // Fórmula inversa para obtener el valor original
            // NuevaMora = OriginalMora * (1 - (Porcentaje / 100))
            // OriginalMora = NuevaMora / (1 - (Porcentaje / 100))
            $original = $this->cargo_mora / (1 - ($this->mora_reducida / 100));
            return round($original, 2);
        }
        return null; // Si no hay reducción, no hay mora original que mostrar
    }

    /**
     * Define la relación inversa de UNO a MUCHOS con el modelo Prestamo.
     * Una Cuota pertenece a un solo Préstamo.
     */
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_Prestamo');
    }

    
    /**
     * Define la relación inversa de UNO a MUCHOS con el modelo Prestamo.
     * Una Cuota pertenece a un solo Préstamo.
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_Cuota');
    }

}