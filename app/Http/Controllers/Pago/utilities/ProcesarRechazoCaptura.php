<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcesarRechazoCaptura
{
    /**
     * Rechaza la captura de pago, elimina el archivo y revierte la cuota a 'Pendiente' (1).
     * @param Cuota $cuota
     * @return void
     */
    public function execute(Cuota $cuota): void
    {
        DB::transaction(function () use ($cuota) {
            
            // 1. Encontrar y ELIMINAR el registro de Pago (Si el pago fue VIRTUAL con monto=0 y solo para guardar el recibo)
            $pago = Pago::where('id_Cuota', $cuota->id)

            if ($pago) {
                // 2. Eliminar la captura del almacenamiento
                if ($pago->ruta_comprobante_cliente) {
                    Storage::disk('public')->delete($pago->ruta_comprobante_cliente);
                }
                $pago->delete();
            }

            // 3. Actualizar el estado de la Cuota a 'Pendiente' (1).
            $cuota->comprobante_url = null;
            $cuota->estado = 1;
            $cuota->save();
        });
    }
}