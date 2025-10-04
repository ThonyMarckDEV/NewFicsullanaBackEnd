<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesarDatosPago
{
    /**
     * Procesa y registra un nuevo pago.
     *
     * @param array $validatedData Datos del pago ya validados.
     * @param Cuota $cuota La cuota que se está pagando.
     * @return void
     * @throws \Exception Si ocurre un error durante la transacción.
     */
    public function execute(array $validatedData, Cuota $cuota): void
    {
        DB::transaction(function () use ($validatedData, $cuota) {
            
            // 1. Crear el registro del pago
            Pago::create([
                'id_Cuota' => $cuota->id,
                'monto_pagado' => $validatedData['monto_pagado'],
                'fecha_pago' => $validatedData['fecha_pago'],
                'modalidad' => $validatedData['modalidad'],
                'numero_operacion' => $validatedData['numero_operacion'] ?? null,
                'observaciones' => $validatedData['observaciones'] ?? null,
                'id_Usuario' => Auth::id(), // ID del usuario autenticado
            ]);

            // 2. Actualizar el estado de la cuota
            $cuota->estado = 2; // 2 = Pagado
            $cuota->save();

            // 3. Verificar si el préstamo está completo
            $prestamo = $cuota->prestamo;
            $cuotasPendientes = $prestamo->cuota()->where('estado', '!=', 2)->count();

            if ($cuotasPendientes === 0) {
                // Si no hay cuotas pendientes, marcar el préstamo como 'Pagado'
                $prestamo->estado = 2; // 2 = Pagado
                $prestamo->save();
            }
        });
    }
}