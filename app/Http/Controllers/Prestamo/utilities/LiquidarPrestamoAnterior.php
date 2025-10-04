<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Exception;

class LiquidarPrestamoAnterior
{
    /**
     * Busca y liquida el préstamo vigente de un cliente para una operación RCS.
     *
     * Este método encuentra el préstamo activo, actualiza su última cuota a 'Pagado'
     * y el estado general del préstamo a 'Liquidado'.
     *
     * @param int $clienteId El ID del cliente cuyo préstamo se liquidará.
     * @return void
     * @throws \Exception si no se encuentra un préstamo vigente para liquidar.
     */
    public function execute(int $clienteId): void
    {
        // 1. Buscar el préstamo anterior que esté vigente
        $prestamoAnterior = Prestamo::where('id_Cliente', $clienteId)
                                    ->where('estado', 1) // 1 = Vigente
                                    ->first();

        // Si para una operación RCS no se encuentra un préstamo anterior, es un error de lógica.
        // Lanzamos una excepción para detener la transacción de forma segura.
        if (!$prestamoAnterior) {
            throw new Exception("Error de Lógica: No se encontró un préstamo vigente para liquidar para el cliente ID: {$clienteId}. La operación RCS no puede continuar.");
        }

        // 2. Buscar la última cuota pendiente de ese préstamo
        // Se busca cualquier cuota que no esté en estado 'Pagado' (2)
        $ultimaCuota = $prestamoAnterior->cuota()
                                        ->where('estado', '!=', 2)
                                        ->orderBy('fecha_vencimiento', 'desc')
                                        ->first();

        if ($ultimaCuota) {
            // 3. Actualizar la cuota
            $ultimaCuota->estado = 2; // 2: Pagado
            $ultimaCuota->observaciones = 'Liquidado por nuevo préstamo RCS.';
            $ultimaCuota->save();
        }
        
        // 4. Actualizar el estado del préstamo anterior
        $prestamoAnterior->estado = 3; // 3: Liquidado
        $prestamoAnterior->save();
    }
}