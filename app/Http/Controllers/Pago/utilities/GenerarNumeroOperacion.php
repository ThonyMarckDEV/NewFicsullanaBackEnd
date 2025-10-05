<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Pago;

class GenerarNumeroOperacion
{
    /**
     * Genera un número de operación formateado y lo asigna al modelo Pago.
     *
     * @param Pago $pago El modelo de pago que necesita el número de operación.
     * @return void
     */
    public function execute(Pago $pago): void
    {
        $pago->numero_operacion = str_pad($pago->id, 8, '0', STR_PAD_LEFT);
        $pago->save();
    }
}