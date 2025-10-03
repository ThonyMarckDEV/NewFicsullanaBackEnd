<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datos extends Model
{
    use HasFactory;

    protected $table = 'datos';

    protected $fillable = [
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'apellidoConyuge',
        'estadoCivil',
        'sexo',
        'dni',
        'fechaCaducidadDni',
        'fechaNacimiento',
        'nacionalidad',
        'residePeru',
        'expuestaPoliticamente',
        'nivelEducativo',
        'profesion',
        'enfermedadesPreexistentes',
        'ruc',
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'id_Datos', 'id');
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class, 'id_Datos' , 'id');
    }


    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_Datos' , 'id');
    }

    public function empleos()
    {
        return $this->hasMany(ClienteEmpleo::class, 'id_Datos' , 'id');
    }

     public function cuentasBancarias()
    {
        return $this->hasMany(CuentaBancaria::class, 'id_Datos' , 'id');
    }

    
}
