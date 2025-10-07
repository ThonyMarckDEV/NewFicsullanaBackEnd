<?php

namespace App\Http\Controllers\Cliente\utilities;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreCliente
{
    /**
     * Crea un nuevo cliente con todos sus datos relacionados dentro de una transacción.
     *
     * @param array $data Datos validados del request.
     * @return User El modelo User creado con la relación 'datos' cargada.
     * @throws \Exception
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear los datos personales
            $datos = Datos::create($data['datos']);

            // 2. Crear el usuario asociado a los datos
            $usuario = $datos->usuario()->create([
                'username' => $datos->dni,
                'password' => Hash::make($datos->dni),
                'id_Rol' => 2, // 2 = Rol Cliente Sistema
            ]);

            // 3. Crear los registros relacionados
            if (!empty($data['direcciones'])) {
                $datos->direcciones()->create($data['direcciones']);
            }
            if (!empty($data['contactos'])) {
                $datos->contactos()->create($data['contactos']);
            }
            if (!empty($data['empleo']['centroLaboral'])) {
                $datos->empleos()->create($data['empleo']);
            }
            if (!empty($data['cuentasBancarias']['ctaAhorros'])) {
                $datos->cuentasBancarias()->create($data['cuentasBancarias']);
            }
            
            // 4. Crear los avales
            if (!empty($data['avales'])) {
                foreach ($data['avales'] as $avalData) {
                    $usuario->avales()->create($avalData);
                }
            }

            return $usuario->load('datos');
        });
    }
}
