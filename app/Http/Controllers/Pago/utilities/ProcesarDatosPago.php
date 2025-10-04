<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesarDatosPago
{
    protected $aplicadorExcedente;

    /**
     * Inyectamos el servicio para aplicar excedentes.
     */
    public function __construct(AplicarExcedente $aplicadorExcedente)
    {
        $this->aplicadorExcedente = $aplicadorExcedente;
    }

    public function execute(array $validatedData, Cuota $cuota): void
    {
        DB::transaction(function () use ($validatedData, $cuota) {
            
            $prestamo = $cuota->prestamo;
            $montoPagadoHoy = (float) $validatedData['monto_pagado'];

            $deudaNetaCuota = max(0, ($cuota->monto + $cuota->cargo_mora) - $cuota->excedente_anterior);
            $nuevoExcedente = max(0, $montoPagadoHoy - $deudaNetaCuota);

            Pago::create([
                'id_Cuota' => $cuota->id,
                'monto_pagado' => $montoPagadoHoy,
                'fecha_pago' => $validatedData['fecha_pago'],
                'modalidad' => $validatedData['modalidad'],
                'numero_operacion' => $validatedData['numero_operacion'] ?? null,
                'observaciones' => $validatedData['observaciones'] ?? null,
                'id_Usuario' => Auth::id(),
            ]);

            if ($montoPagadoHoy >= $deudaNetaCuota) {
                $cuota->estado = 2; // Pagado
                $cuota->save();

                if ($nuevoExcedente > 0) {
                    $this->aplicadorExcedente->execute($nuevoExcedente, $prestamo);
                }
            }

            if ($prestamo->cuota()->where('estado', '!=', 2)->count() === 0) {
                $prestamo->estado = 2; // Marcar préstamo como Pagado
                $prestamo->save();
            }
        });
    }
}