<?php

namespace App\Http\Controllers\Cliente\utilities\services;

use App\Models\User;

class ObtenerModalidadCliente
{
    /**
     * Determina la modalidad del cliente (NUEVO o RSS) basándose en sus préstamos.
     * @param User $cliente El modelo User con la relación 'prestamos' cargada.
     * @return string La modalidad del cliente (NUEVO, RSS, o VIGENTE).
     */
    public function obtenerModalidad(User $cliente): string
    {
        // 1. Si no tiene préstamos, la modalidad es NUEVO.
        if ($cliente->prestamos->isEmpty()) {
            return 'NUEVO';
        }

        // 2. Buscamos si existe al menos un préstamo en estado 'Cancelado' (valor 2).
        $tienePrestamosCancelados = $cliente->prestamos->contains(function ($prestamo) {
            // CORRECCIÓN CLAVE: Se usa el operador de comparación estricta (===) 
            // para verificar el valor numérico 2, que es 'Cancelado'.
            return $prestamo->estado === 2; 
        });

        // 3. Si tiene préstamos y al menos uno está cancelado, la modalidad es RSS.
        if ($tienePrestamosCancelados) {
            return 'RSS'; // Coincide con tu lógica "RSS osea 2"
        }

        // 4. Si tiene historial de préstamos, pero no hay ninguno cancelado, se considera VIGENTE.
        return 'RCS';
    }
}