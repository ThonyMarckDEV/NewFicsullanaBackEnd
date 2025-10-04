<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;

class VerificarCuotasPagadas
{
    /**
     * Verifica si un préstamo tiene cuotas pagadas y añade una bandera al modelo.
     */
    public function execute(Prestamo $prestamo): void
    {
        $prestamo->tiene_cuotas_pagadas = $prestamo->cuota()->where('estado', 2)->exists();
    }
}