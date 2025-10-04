<?php

namespace App\Http\Controllers\Cliente\utilities;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ProcesarDatosCliente
{
    public function crearNuevoCliente(array $data)
    {
        // Usamos una transacción para asegurar la integridad de los datos
        return DB::transaction(function () use ($data) {
            // 1. Crear los datos personales
            $datos = Datos::create($data['datos']);

            // 2. Crear el usuario asociado a los datos
            // La contraseña y el usuario son el DNI, como solicitaste
            $usuario = $datos->usuario()->create([
                'username' => $datos->dni,
                'password' => Hash::make($datos->dni),
                'id_Rol' => 2, // 2 = Rol Cliente Sistema (ajusta si es necesario)
            ]);

            // 3. Crear los registros relacionados (1 a muchos)
            if (!empty($data['direcciones'])) {
                $datos->direcciones()->create($data['direcciones']);
            }
            if (!empty($data['contactos'])) {
                $datos->contactos()->create($data['contactos']);
            }
            if (!empty($data['empleo']['centroLaboral'])) { // Validamos que no esté vacío
                $datos->empleos()->create($data['empleo']);
            }
            if (!empty($data['cuentasBancarias']['ctaAhorros'])) { // Validamos que no esté vacío
                $datos->cuentasBancarias()->create($data['cuentasBancarias']);
            }
            
            // 4. Crear los avales (asociados al usuario)
            if (!empty($data['avales'])) {
                foreach ($data['avales'] as $avalData) {
                    $usuario->avales()->create($avalData);
                }
            }

            // Devolvemos el usuario con sus datos para la respuesta
            return $usuario->load('datos');
        });
    }

    public function actualizarCliente(User $usuario, array $data)
    {
        return DB::transaction(function () use ($usuario, $data) {
            // 1. Actualizar los datos personales principales
            $usuario->datos->update($data['datos']);

            // 2. Actualizar/Crear registros relacionados (updateOrCreate es perfecto para esto)
            $usuario->datos->direcciones()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['direcciones'] ?? []);
            $usuario->datos->contactos()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['contactos'] ?? []);
            $usuario->datos->empleos()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['empleo'] ?? []);
            $usuario->datos->cuentasBancarias()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['cuentasBancarias'] ?? []);

            // 3. Borrar los avales antiguos y crear los nuevos
            $usuario->avales()->delete();
            if (!empty($data['avales'])) {
                foreach ($data['avales'] as $avalData) {
                    $usuario->avales()->create($avalData);
                }
            }

            // Refrescamos el modelo para devolver los datos actualizados
            return $usuario->fresh()->load('datos');
        });
    }


    
}