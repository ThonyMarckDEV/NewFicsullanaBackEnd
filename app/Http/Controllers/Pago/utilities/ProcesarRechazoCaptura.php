<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Http\Controllers\Prestamo\services\VerificarEstadoStorage; 
use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcesarRechazoCaptura
{
    /**
     * @var VerificarEstadoStorage
     */
    protected $storageService;

    /**
     * Inyecta el servicio para verificar el estado del storage.
     */
    public function __construct(VerificarEstadoStorage $storageService)
    {
        $this->storageService = $storageService;
    }

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
            
            // ... (La lógica para encontrar el pago y el préstamo no cambia)
            $pago = $cuota->pagos()->latest()->first();
            $prestamo = $cuota->prestamo;

            if (!$pago || !$prestamo) {
                throw new Exception("No se encontró un pago o préstamo asociado para rechazar en la cuota {$cuota->id}.");
            }

            // 4. Obtiene el disco correcto (local o minio) dinámicamente.
            $disk = $this->storageService->obtenerDisco();

            // 5. Construye la ruta del directorio y elimina la carpeta completa del disco correcto.
            $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/capturapago";
            Storage::disk($disk)->deleteDirectory($directorio);

            // 6. Elimina el registro del pago de la base de datos.
            $pago->delete();

            // 7. Reviertir el estado de la cuota a 'Pendiente' (1).
            $cuota->estado = 1;
            $cuota->save();
        });
    }
}