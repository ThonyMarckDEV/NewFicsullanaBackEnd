<?php

namespace App\Http\Controllers\Cliente\utilities\services;

use App\Models\User;
use App\Models\Prestamo; 

class ObtenerModalidadCliente
{
    /**
     * Determina la modalidad del cliente (NUEVO, RSS, RCS, o PRESTAMO_ACTIVO) basándose en sus préstamos.
     * @param User $cliente El modelo User. Se asume que la relación 'prestamos' está cargada.
     * @return string La modalidad del cliente.
     */
    public function obtenerModalidad(User $cliente): string
    {
        if ($cliente->prestamos->isEmpty()) {
            return 'NUEVO';
        }

        // Buscamos el préstamo activo (estado 1: vigente)
        $prestamoActivo = $cliente->prestamos->firstWhere('estado', 1);

        // Verificación de Historial (RSS)
        $tienePrestamosCancelados = $cliente->prestamos->contains(function ($prestamo) {
            return $prestamo->estado === 2; // Estado 2 = Cancelado
        });

        if (!$prestamoActivo) {
            // Si NO hay préstamo activo, verificamos historial
            return $tienePrestamosCancelados ? 'RSS' : 'NUEVO';
        }

        // -----------------------------------------------------------------
        // CORRECCIÓN: Usamos cuota() para llamar a la relación y usar el Query Builder.
        
        // Contar cuotas pendientes (1: pendiente, 3: vence_hoy, 4: vencido)
        $cuotasPendientes = $prestamoActivo->cuota() 
            ->whereIn('estado', [1, 3, 4])
            ->count(); 

        // Si solo queda 1 cuota pendiente, es RCS.
        if ($cuotasPendientes === 1) {
            return 'RCS';
        }
        
        // Si tiene más de una cuota pendiente, es un préstamo activo normal.
        return 'PRESTAMO_ACTIVO';
    }
}