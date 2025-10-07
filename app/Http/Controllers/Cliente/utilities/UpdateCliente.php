<?php

namespace App\Http\Controllers\Cliente\utilities;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateCliente
{
    /**
     * Actualiza un cliente existente y sus datos relacionados.
     *
     * @param User $usuario El usuario a actualizar.
     * @param array $data Los datos validados del request.
     * @return User El modelo User actualizado con sus relaciones cargadas.
     * @throws \Exception
     */
    public function execute(User $usuario, array $data): User
    {
        return DB::transaction(function () use ($usuario, $data) {
            // 1. Actualizar los datos personales principales
            if (isset($data['datos'])) {
                $usuario->datos->update($data['datos']);
            }

            // 2. Actualizar/Crear registros relacionados
            if (isset($data['direcciones'])) {
                $usuario->datos->direcciones()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['direcciones']);
            }
            if (isset($data['contactos'])) {
                $usuario->datos->contactos()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['contactos']);
            }
            if (isset($data['empleo'])) {
                $usuario->datos->empleos()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['empleo']);
            }
            if (isset($data['cuentasBancarias'])) {
                $usuario->datos->cuentasBancarias()->updateOrCreate(['id_Datos' => $usuario->datos->id], $data['cuentasBancarias']);
            }

            // 3. Sincronizar avales: borrar antiguos y crear nuevos
            if (isset($data['avales'])) {
                $usuario->avales()->delete();
                if (!empty($data['avales'])) {
                    foreach ($data['avales'] as $avalData) {
                        $usuario->avales()->create($avalData);
                    }
                }
            }

            // Refrescamos el modelo para devolver todos los datos actualizados
            return $usuario->fresh()->load([
                'datos.direcciones',
                'datos.contactos',
                'datos.empleos',
                'datos.cuentasBancarias',
                'avales'
            ]);
        });
    }
}
