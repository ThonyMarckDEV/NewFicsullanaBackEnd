<?php

namespace App\Http\Controllers\Pago\services;

use App\Models\Prestamo;

class AplicarExcedente
{
    /**
     * Aplica un monto excedente a la siguiente cuota pendiente de un préstamo.
     *
     * @param float $excedente El monto del excedente a aplicar.
     * @param Prestamo $prestamo El préstamo al que pertenece la cuota.
     * @return void
     */
    public function execute(float $excedente, Prestamo $prestamo): void
    {
        // 1. Buscar la siguiente cuota que no esté pagada.
        $siguienteCuota = $prestamo->cuota()
            ->where('estado', '!=', 2) // Que no esté pagada
            ->orderBy('numero_cuota', 'asc') // En orden
            ->first();

        // 2. Si se encuentra una siguiente cuota, se le añade el excedente.
        if ($siguienteCuota) {
            // Sumamos el nuevo excedente al que ya pudiera tener.
            $siguienteCuota->excedente_anterior += $excedente;
            $siguienteCuota->save();
        }
    }
}