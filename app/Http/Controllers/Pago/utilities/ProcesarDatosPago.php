<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesarDatosPago
{
    /**
     * Procesa y registra un nuevo pago, aplicando excedentes a la siguiente cuota.
     *
     * @param array $validatedData Datos del pago ya validados.
     * @param Cuota $cuota La cuota que se está pagando.
     * @return void
     * @throws \Exception Si ocurre un error.
     */
    public function execute(array $validatedData, Cuota $cuota): void
    {
        DB::transaction(function () use ($validatedData, $cuota) {
            
            $prestamo = $cuota->prestamo;
            $montoPagadoHoy = (float) $validatedData['monto_pagado'];

            // 1. Calcular la deuda NETA de ESTA cuota.
            // (Monto de la cuota + Mora) - Crédito de pagos anteriores
            $deudaNetaCuota = max(0, ($cuota->monto + $cuota->cargo_mora) - $cuota->excedente_anterior);

            // 2. Calcular si el pago de HOY genera un nuevo excedente.
            $nuevoExcedente = max(0, $montoPagadoHoy - $deudaNetaCuota);

            // 3. Crear el registro del pago.
            Pago::create([
                'id_Cuota' => $cuota->id,
                'monto_pagado' => $montoPagadoHoy,
                'fecha_pago' => $validatedData['fecha_pago'],
                'modalidad' => $validatedData['modalidad'],
                'numero_operacion' => $validatedData['numero_operacion'] ?? null,
                'observaciones' => $validatedData['observaciones'] ?? null,
                'id_Usuario' => Auth::id(),
            ]);

            // 4. Marcar la cuota actual como PAGADA.
            $cuota->estado = 2; // 2 = Pagado
            $cuota->save();

            // 5. Si se generó un nuevo excedente, aplicarlo a la siguiente cuota.
            if ($nuevoExcedente > 0) {
                $siguienteCuota = $prestamo->cuota()
                    ->where('estado', '!=', 2) // Buscar la próxima no pagada
                    ->orderBy('numero_cuota', 'asc')
                    ->first();

                if ($siguienteCuota) {
                    // Sumar el excedente al crédito que ya pudiera tener la siguiente cuota.
                    $siguienteCuota->excedente_anterior += $nuevoExcedente;
                    $siguienteCuota->save();
                }
            }

            // 6. Verificar si el préstamo se completó.
            if ($prestamo->cuota()->where('estado', '!=', 2)->count() === 0) {
                $prestamo->estado = 2; // Marcar préstamo como Pagado
                $prestamo->save();
            }
        });
    }
}