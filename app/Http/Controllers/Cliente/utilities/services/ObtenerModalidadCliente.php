<?php

namespace App\Http\Controllers\Cliente\utilities\services;

use App\Models\User;

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

        // Verificación de Historial (RSS): Ahora buscamos préstamos en estado 2 (Pagado) o 3 (Liquidado).
        $tienePrestamosCompletados = $cliente->prestamos->contains(function ($prestamo) {
            return in_array($prestamo->estado, [2, 3]); // Se busca si el estado es 2 O 3
        });
        
        // Si NO hay préstamo activo, verificamos el historial.
        if (!$prestamoActivo) {
            // Si tiene préstamos completados, es candidato para RSS.
            return $tienePrestamosCompletados ? 'RSS' : 'NUEVO';
        }
    
        // Contar cuotas pendientes (estado diferente de 'Pagado')
        $cuotasPendientes = $prestamoActivo->cuota() 
            ->where('estado', '!=', 2)
            ->count(); 

        // Si solo queda 1 cuota pendiente, es RCS.
        if ($cuotasPendientes === 1) {
            return 'RCS';
        }
        
        // Si tiene más de una cuota pendiente, es un préstamo activo normal.
        return 'PRESTAMO_ACTIVO';
    }
}