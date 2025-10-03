<?php

namespace App\Http\Controllers\Empleado\utilities;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;  

class ProcesarDatosEmpleado
{


     /**
     * Crea un nuevo registro de Datos y User para un empleado.
     * @param array $data Datos validados del request.
     * @return User El modelo User creado con la relación 'datos' cargada.
     * @throws Exception
     */
    public function crearNuevoEmpleado(array $data)
    {
        // 🛑 Usamos una transacción para garantizar la integridad de Datos y User
        return DB::transaction(function () use ($data) {
            
            // 1. Crear el registro de Datos. 
            // Los campos de datos personales vienen directamente en el array $data (sin anidamiento)
            $datos = Datos::create($data);

            // 2. Crear el registro de Usuario (Empleado)
            $empleado = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'id_Datos' => $datos->id,
                'id_Rol' => $data['id_Rol'],
                'estado' => 1, // Por defecto activo
            ]);

            // Devolvemos el empleado con la relación 'datos' cargada
            return $empleado->load('datos');
        });
    }

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
            if (isset($data['password']) && !empty($data['password'])) {
                // La validación de 'confirmed' debería haber ocurrido en UpdateEmpleadoRequest
                // Esta verificación es redundante pero segura
                if (!isset($data['password_confirmation']) || $data['password'] !== $data['password_confirmation']) {
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
            // ¡ESTO AHORA FUNCIONA porque prepareForValidation() anidó los datos!
            if (isset($data['datos'])) {
                $usuario->datos->update($data['datos']);
            }
            
            // 3. Devolver el modelo de User actualizado y con la relación 'datos' cargada
            return $usuario->fresh()->load('datos');
        });
    }
}