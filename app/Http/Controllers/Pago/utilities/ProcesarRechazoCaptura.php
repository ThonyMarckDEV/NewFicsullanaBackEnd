<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcesarRechazoCaptura
{
    /**
     * Rechaza la captura de pago, elimina el directorio de capturas, borra el registro del pago
     * y revierte la cuota al estado 'Pendiente' (1).
     *
     * @param Cuota $cuota La cuota que está en estado 'Procesando' (5).
     * @return void
     * @throws Exception Si no se encuentra un pago o préstamo asociado.
     */
    public function execute(Cuota $cuota): void
    {
        DB::transaction(function () use ($cuota) {
            
            // 1. Encontrar el último pago y el préstamo asociado.
            $pago = $cuota->pagos()->latest()->first();
            $prestamo = $cuota->prestamo;

            // 2. Validar que exista un pago y un préstamo para rechazar.
            if (!$pago || !$prestamo) {
                throw new Exception("No se encontró un pago o préstamo asociado para rechazar en la cuota {$cuota->id}.");
            }

            // --- INICIO DE LA CORRECCIÓN ---
            // 3. Construir la ruta del directorio y eliminar la carpeta completa.
            $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/capturapago";
            Storage::disk('public')->deleteDirectory($directorio);
            // --- FIN DE LA CORRECCIÓN ---

            // 4. Eliminar el registro del pago de la base de datos.
            $pago->delete();

            // 5. Revertir el estado de la cuota a 'Pendiente' (1).
            $cuota->estado = 1;
            $cuota->save();
        });
    }
}