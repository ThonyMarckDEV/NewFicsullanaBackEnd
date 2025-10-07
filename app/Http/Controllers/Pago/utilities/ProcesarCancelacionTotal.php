<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Http\Controllers\Pago\services\GenerarComprobantePago;

use App\Models\Prestamo;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesarCancelacionTotal
{
    protected $generadorComprobante;

    public function __construct(GenerarComprobantePago $generadorComprobante)
    {
        $this->generadorComprobante = $generadorComprobante;
    }

    public function execute(array $validatedData, Prestamo $prestamo): void
    {
        DB::transaction(function () use ($validatedData, $prestamo) {
            
            $cuotasPendientes = $prestamo->cuota()->where('estado', '!=', 2)->orderBy('numero_cuota', 'asc')->get();
            $ultimaCuota = $cuotasPendientes->last();

            $pagoMaestro = Pago::create([
                'id_Cuota' => $ultimaCuota->id,
                'monto_pagado' => $validatedData['monto_pagado'],
                'excedente' => 0,
                'fecha_pago' => $validatedData['fecha_pago'],
                'modalidad' => $validatedData['modalidad'],
                'observaciones' => 'CANCELACIÓN TOTAL DE PRÉSTAMO. ' . ($validatedData['observaciones'] ?? ''),
                'id_Usuario' => Auth::id(),
            ]);
            $pagoMaestro->numero_operacion = str_pad($pagoMaestro->id, 8, '0', STR_PAD_LEFT);
            $pagoMaestro->save();
            
            // --- LLAMADA ACTUALIZADA ---
            // Le pasamos el pago, un 'true' para indicar que es cancelación, y la colección de cuotas
            $this->generadorComprobante->execute($pagoMaestro, true, $cuotasPendientes);

            foreach ($cuotasPendientes as $cuota) {
                $cuota->estado = 2;
                $cuota->save();
            }

            $prestamo->estado = 2;
            $prestamo->save();
        });
    }
}