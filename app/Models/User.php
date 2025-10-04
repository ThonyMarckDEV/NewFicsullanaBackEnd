<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar de manera masiva.
     *
     * @var array<string>
     */
    protected $fillable = [
        'username',
        'password',
        'id_Datos',
        'id_Rol',
        'estado'
    ];

    /**
     * Los atributos que deberían ser ocultados para la serialización.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Obtener los atributos que deben ser convertidos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'rol' => $this->rol()->first()->nombre,
            'username' => $this->username,
        ];
    }

     /**
     * Intenta resolver el Route Model Binding por ID o, si no lo encuentra, por DNI.
     * Esta solución asume que la columna 'dni' está en la tabla `users` o que 
     * puedes hacer un join simple a la tabla `datos`.
     * Usaremos el campo `dni` de la tabla `datos` a través de un JOIN.
   */public function resolveRouteBinding($value, $field = null)
    {
        // 1. Intentar buscar por ID (comportamiento por defecto)
        // $this->table es 'usuarios'
        $user = $this->where($this->getRouteKeyName(), $value)->first();

        if ($user) {
            return $user;
        }

        // 2. Intentar buscar por DNI/RUC en la tabla 'datos' (via id_Datos)
        // El JOIN debe ser: usuarios.id_Datos = datos.id
        
        $empleado = $this->join('datos', 'usuarios.id_Datos', '=', 'datos.id')
                        // Buscar el valor ($value) en la columna 'dni' de la tabla 'datos'
                        ->where('datos.dni', $value) 
                        // Importante: seleccionar solo las columnas de 'usuarios' para instanciar el modelo User
                        ->select('usuarios.*') 
                        ->first();

        if ($empleado) {
            return $empleado;
        }

        // Si no se encuentra por ID ni por DNI, falla.
        return null; 
    }

    /**
     * Relación con los datos personales
     */
    public function datos()
    {
        return $this->belongsTo(Datos::class, 'id_Datos', 'id');
    }

    /**
     * Relación con el rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_Rol', 'id');
    }

    /**
     * Relación con avales
     */
    public function avales()
    {
        return $this->hasMany(ClienteAval::class, 'id_Cliente', 'id');
    }


}