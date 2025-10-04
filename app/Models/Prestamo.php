<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'id_Cliente',
        'monto',
        'interes',
        'total',
        'cuotas',
        'valor_cuota',
        'frecuencia',
        'modalidad',
        'fecha_generacion', // Se puede establecer automáticamente en la utilidad
        'fecha_inicio',     // Se puede establecer automáticamente en la utilidad
        'id_Asesor',
        'id_Producto',
        'abonado_por',
        'estado',           // 1:vigente, 2:cancelado, 3:liquidado
    ];
    
    // Casteos para asegurar tipos de datos correctos
    protected $casts = [
        'monto' => 'decimal:2',
        'interes' => 'decimal:2',
        'total' => 'decimal:2',
        'valor_cuota' => 'decimal:2',
        'cuotas' => 'integer',
        'fecha_generacion' => 'date',
        'fecha_inicio' => 'date',
        'estado' => 'integer',
    ];

    // Relaciones
    public function cliente()
    {
        // Asumiendo que 'usuarios' es el modelo de cliente/usuario
        return $this->belongsTo(User::class, 'id_Cliente' , 'id');
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_Asesor' , 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_Producto' , 'id');
    }
}