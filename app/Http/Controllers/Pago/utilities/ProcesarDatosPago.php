<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesarDatosPago
{
    protected $aplicadorExcedente;
    protected $generadorComprobante;

    /**
     * Inyectamos los servicios para aplicar excedentes y generar comprobantes.
     */
    public function __construct(AplicarExcedente $aplicadorExcedente, GenerarComprobantePago $generadorComprobante)
    {
        $this->aplicadorExcedente = $aplicadorExcedente;
        $this->generadorComprobante = $generadorComprobante;
    }

    /**
     * Procesa y registra un nuevo pago, aplicando excedentes y generando el comprobante.
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

            // 1. Calcular la deuda neta de la cuota actual.
            $deudaNetaCuota = max(0, ($cuota->monto + $cuota->cargo_mora) - $cuota->excedente_anterior);

            // 2. Calcular si el pago de hoy genera un nuevo excedente.
            $nuevoExcedente = max(0, $montoPagadoHoy - $deudaNetaCuota);

            // 3. Crear el registro del pago.
            $pago = Pago::create([
                'id_Cuota' => $cuota->id,
                'monto_pagado' => $montoPagadoHoy,
                'fecha_pago' => $validatedData['fecha_pago'],
                'modalidad' => $validatedData['modalidad'],
                'observaciones' => $validatedData['observaciones'] ?? null,
                'id_Usuario' => Auth::id(),
            ]);

            // 4. Generar y guardar el número de operación.
            $pago->numero_operacion = str_pad($pago->id, 8, '0', STR_PAD_LEFT);
            $pago->save();
            
            // 5. Generar el comprobante de pago en PDF.
            $this->generadorComprobante->execute($pago);

            // 6. Si el pago cubre la deuda neta, actualizar la cuota y aplicar excedente.
            if ($montoPagadoHoy >= $deudaNetaCuota) {
                $cuota->estado = 2; // Marcar cuota como Pagada
                $cuota->save();

                // Si se generó un nuevo excedente, aplicarlo a la siguiente cuota.
                if ($nuevoExcedente > 0) {
                    $this->aplicadorExcedente->execute($nuevoExcedente, $prestamo);
                }
            }

            // 7. Verificar si el préstamo se completó.
            if ($prestamo->cuota()->where('estado', '!=', 2)->count() === 0) {
                $prestamo->estado = 2; // Marcar préstamo como Pagado
                $prestamo->save();
            }
        });
    }
}