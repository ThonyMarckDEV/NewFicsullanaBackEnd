<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'rango_tasa',
    ];

    /**
     * Define los tipos de datos para la serialización
     * Aunque no se requiere para este caso, es una buena práctica si tuviéramos campos de fecha específicos.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}