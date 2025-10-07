<?php

namespace App\Http\Controllers\Empleado\utilities;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreEmpleado
{
    /**
     * Crea un nuevo registro de Datos y User para un empleado dentro de una transacción.
     *
     * @param array $data Datos validados del request.
     * @return User El modelo User creado con la relación 'datos' cargada.
     * @throws \Exception
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear el registro de Datos.
            $datos = Datos::create($data);

            // 2. Crear el registro de Usuario (Empleado)
            $empleado = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'id_Datos' => $datos->id,
                'id_Rol'   => $data['id_Rol'],
                'estado'   => 1, // Por defecto activo
            ]);

            // Devolvemos el empleado con la relación 'datos' cargada
            return $empleado->load('datos');
        });
    }
}
