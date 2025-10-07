<?php

namespace App\Http\Controllers\Pago\services;

use App\Models\Cuota;

class ActualizarEstadoCuota
{
    /**
     * Actualiza el estado de la cuota y guarda el cambio.
     *
     * @param Cuota $cuota El modelo de cuota a actualizar.
     * @param int $estado El nuevo ID de estado (ej: 2=Pagada, 5=Prepagada).
     * @return void
     */
    public function execute(Cuota $cuota, int $estado): void
    {
        $cuota->estado = $estado;
        $cuota->save();
    }
}