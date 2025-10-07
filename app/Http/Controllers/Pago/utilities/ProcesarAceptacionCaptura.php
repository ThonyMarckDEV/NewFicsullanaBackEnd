<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Http\Controllers\Pago\services\ActualizarEstadoCuota;
use App\Http\Controllers\Pago\services\FinalizarPrestamo;
use App\Http\Controllers\Pago\services\GenerarComprobantePago;
use App\Http\Controllers\Pago\services\GenerarNumeroOperacion;

use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcesarAceptacionCaptura
{
    protected $generadorNumeroOperacion;
    protected $actualizadorCuota;
    protected $finalizadorPrestamo;
    protected $generadorComprobante;

    public function __construct(
        GenerarNumeroOperacion $generadorNumeroOperacion,
        ActualizarEstadoCuota $actualizadorCuota,
        FinalizarPrestamo $finalizadorPrestamo,
        GenerarComprobantePago $generadorComprobante
    ) {
        $this->generadorNumeroOperacion = $generadorNumeroOperacion;
        $this->actualizadorCuota = $actualizadorCuota;
        $this->finalizadorPrestamo = $finalizadorPrestamo;
        $this->generadorComprobante = $generadorComprobante;
    }

    /**
     * Procesa la aceptación de una captura de pago virtual.
     *
     * @param Cuota $cuota La cuota que está en estado 'Procesando'.
     * @return void
     * @throws Exception Si no se encuentra un pago asociado.
     */
    public function execute(Cuota $cuota): void
    {
        DB::transaction(function () use ($cuota) {
            
            // 1. Encontrar el último pago asociado a la cuota (el que tiene la captura).
            $pago = $cuota->pagos()->latest()->first();

            if (!$pago) {
                // Si no hay pago, no se puede continuar. Lanza un error.
                throw new Exception("Error: No se encontró un pago pendiente para la cuota {$cuota->id}.");
            }

            // 3. Actualizar el estado de la Cuota a 'Pagada' (2).
            $this->actualizadorCuota->execute($cuota, 2);

            // 4. Verificar y finalizar el préstamo si todas las cuotas están pagadas.
            $this->finalizadorPrestamo->execute($cuota->prestamo);

            // 5. Generar el comprobante de pago oficial para el pago aceptado.
            $this->generadorComprobante->execute($pago);
        });
    }
}