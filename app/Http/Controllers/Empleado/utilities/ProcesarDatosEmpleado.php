<?php

namespace App\Http\Controllers\Empleado\utilities;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProcesarDatosEmpleado
{

    public function actualizarEmpleado(User $usuario, array $data)
    {
        return DB::transaction(function () use ($usuario, $data) {
            
            // 1. Manejar la actualización del Modelo User (username, password, id_Rol)
            $userUpdates = [];

            // A) Actualizar Rol (id_Rol)
            if (isset($data['id_Rol'])) {
                $userUpdates['id_Rol'] = $data['id_Rol'];
            }

            // B) Actualizar Username
            if (isset($data['username'])) {
                $userUpdates['username'] = $data['username'];
            }

            // C) Actualizar Contraseña (Solo si se proporciona)
            // Esto es crucial para la edición, ya que el campo es opcional
            if (isset($data['password']) && !empty($data['password'])) {
                // Aquí podrías agregar una validación adicional si 'password_confirmation' es requerido
                if (!isset($data['password_confirmation']) || $data['password'] !== $data['password_confirmation']) {
                     // Lanzar una excepción de validación si las contraseñas no coinciden.
                     throw ValidationException::withMessages([
                         'password' => ['Las contraseñas no coinciden.'],
                     ]);
                }
                
                $userUpdates['password'] = Hash::make($data['password']);
            }

            // Aplicar las actualizaciones al modelo User principal
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