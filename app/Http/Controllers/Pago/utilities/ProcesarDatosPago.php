<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Importaciones de servicios refactorizados (asumiendo que existen o que los crearás)
use App\Http\Controllers\Pago\utilities\GuardarCapturaPago;
use App\Http\Controllers\Pago\utilities\GenerarNumeroOperacion;
use App\Http\Controllers\Pago\utilities\ActualizarEstadoCuota;
use App\Http\Controllers\Pago\utilities\FinalizarPrestamo;
use App\Http\Controllers\Pago\utilities\AplicarExcedente;
use App\Http\Controllers\Pago\utilities\GenerarComprobantePago;

class ProcesarDatosPago
{
    protected $aplicadorExcedente;
    protected $generadorComprobante;
    protected $guardarCapturaPago;
    protected $generadorNumeroOperacion; // NUEVO
    protected $actualizadorCuota;        // NUEVO
    protected $finalizadorPrestamo;      // NUEVO

    public function __construct(
        AplicarExcedente $aplicadorExcedente,
        GenerarComprobantePago $generadorComprobante,
        GuardarCapturaPago $guardarCapturaPago,
        GenerarNumeroOperacion $generadorNumeroOperacion, 
        ActualizarEstadoCuota $actualizadorCuota,         
        FinalizarPrestamo $finalizadorPrestamo          
    ) {
        $this->aplicadorExcedente = $aplicadorExcedente;
        $this->generadorComprobante = $generadorComprobante;
        $this->guardarCapturaPago = $guardarCapturaPago;
        $this->generadorNumeroOperacion = $generadorNumeroOperacion; 
        $this->actualizadorCuota = $actualizadorCuota;               
        $this->finalizadorPrestamo = $finalizadorPrestamo;          
    }

    /**
     * Procesa y registra un nuevo pago.
     */
    public function execute(array $validatedData, Cuota $cuota): void
    {
        DB::transaction(function () use ($validatedData, $cuota) {
            
            $prestamo = $cuota->prestamo;
            $montoPagadoHoy = (float) $validatedData['monto_pagado'];
            $modalidadPago = $validatedData['modalidad'];
            $pago = null; // Inicializar $pago

            if ($modalidadPago === 'VIRTUAL') {
                
                $rutaComprobanteCliente = null;
                
                // 1. Guardar la captura si existe.
                if (isset($validatedData['comprobante'])) {
                    $rutaComprobanteCliente = $this->guardarCapturaPago->execute(
                        $validatedData['comprobante'],
                        $cuota
                    );
                }

                // 2. Crear el registro del pago VIRTUAL.
                $pago = Pago::create([
                    'id_Cuota' => $cuota->id,
                    'monto_pagado' => $montoPagadoHoy,
                    'excedente' => 0,
                    'fecha_pago' => $validatedData['fecha_pago'],
                    'modalidad' => $modalidadPago,
                    'metodo_pago' => $validatedData['metodo_pago'] ?? null,
                    'ruta_comprobante_cliente' => $rutaComprobanteCliente,
                    'id_Usuario' => Auth::id(),
                ]);

                // 3. Generar y guardar el número de operación.
                $this->generadorNumeroOperacion->execute($pago);

                // 4. Actualizar el estado de la cuota (Prepagado = 5).
                $this->actualizadorCuota->execute($cuota, 5);

            } else { // Modalidad PRESENCIAL
                
                // 1. Calcular excedente.
                $deudaNetaCuota = max(0, ($cuota->monto + $cuota->cargo_mora) - $cuota->excedente_anterior);
                $nuevoExcedente = max(0, $montoPagadoHoy - $deudaNetaCuota);

                // 2. Crear el registro del pago PRESENCIAL.
                $pago = Pago::create([
                    'id_Cuota' => $cuota->id,
                    'monto_pagado' => $montoPagadoHoy,
                    'excedente' => $nuevoExcedente,
                    'fecha_pago' => $validatedData['fecha_pago'],
                    'modalidad' => $modalidadPago,
                    'observaciones' => $validatedData['observaciones'] ?? null,
                    'id_Usuario' => Auth::id(),
                ]);

                // 3. Generar y guardar el número de operación.
                $this->generadorNumeroOperacion->execute($pago);
                
                // 4. Lógica de liquidación.
                if ($montoPagadoHoy >= $deudaNetaCuota) {
                    
                    // a) Actualizar el estado de la cuota (Pagada = 2).
                    $this->actualizadorCuota->execute($cuota, 2); 

                    // b) Aplicar excedente (si existe).
                    if ($nuevoExcedente > 0) {
                        $this->aplicadorExcedente->execute($nuevoExcedente, $prestamo);
                    }
                    
                    // c) Verificar y finalizar el préstamo.
                    $this->finalizadorPrestamo->execute($prestamo);
                }
                
                // 5. Generar el comprobante de pago en PDF (asume que GenerarComprobantePago lo guarda).
                $this->generadorComprobante->execute($pago);
            }
        });
    }
}