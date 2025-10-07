<?php

namespace App\Http\Controllers\Empleado\utilities;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdateEmpleado
{
    /**
     * Actualiza un registro de User y sus Datos relacionados para un empleado.
     *
     * @param User $usuario El usuario a actualizar.
     * @param array $data Los datos validados del request.
     * @return User El modelo User actualizado con la relación 'datos' cargada.
     * @throws \Exception
     */
    public function execute(User $usuario, array $data): User
    {
        return DB::transaction(function () use ($usuario, $data) {
            // 1. Manejar la actualización del Modelo User
            $userUpdates = [];

            if (isset($data['id_Rol'])) {
                $userUpdates['id_Rol'] = $data['id_Rol'];
            }

            if (isset($data['username'])) {
                $userUpdates['username'] = $data['username'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                if (!isset($data['password_confirmation']) || $data['password'] !== $data['password_confirmation']) {
                    throw ValidationException::withMessages([
                        'password' => ['Las contraseñas no coinciden.'],
                    ]);
                }
                $userUpdates['password'] = Hash::make($data['password']);
            }

            if (!empty($userUpdates)) {
                $usuario->update($userUpdates);
            }

            // 2. Actualizar los datos personales (tabla 'datos')
            if (isset($data['datos'])) {
                $usuario->datos->update($data['datos']);
            }

            // 3. Devolver el modelo de User actualizado y con la relación 'datos' cargada
            return $usuario->fresh()->load('datos');
        });
    }
}
