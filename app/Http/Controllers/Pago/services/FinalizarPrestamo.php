<?php

namespace App\Http\Controllers\Pago\services;
use App\Models\Prestamo;

class FinalizarPrestamo
{
    /**
     * Verifica si todas las cuotas del préstamo están pagadas y actualiza el estado del préstamo.
     *
     * @param Prestamo $prestamo El modelo de préstamo a verificar.
     * @return void
     */
    public function execute(Prestamo $prestamo): void
    {
        // Verificar si existen cuotas en estado diferente a 2 (Pagada).
        if ($prestamo->cuota()->where('estado', '!=', 2)->count() === 0) {
            $prestamo->estado = 2; // Marcar préstamo como Pagado
            $prestamo->save();
        }
    }
}