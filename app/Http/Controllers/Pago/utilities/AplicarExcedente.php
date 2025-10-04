<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Prestamo;

class AplicarExcedente
{
    public function execute(float $excedente, Prestamo $prestamo): void
    {
        $siguienteCuota = $prestamo->cuota()
            ->where('estado', '!=', 2)
            ->orderBy('numero_cuota', 'asc')
            ->first();

        if ($siguienteCuota) {
            $siguienteCuota->excedente_anterior += $excedente;
            $siguienteCuota->save();
        }
    }
}