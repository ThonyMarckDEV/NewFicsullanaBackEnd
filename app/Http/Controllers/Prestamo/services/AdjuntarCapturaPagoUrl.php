<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class AdjuntarCapturaPagoUrl
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
     * Itera sobre una colección de cuotas y adjunta la URL del comprobante más reciente.
     *
     * @param Collection $cuotas La colección de cuotas de un préstamo.
     * @param Prestamo $prestamo El préstamo padre.
     * @return void
     */
    public function execute(Collection $cuotas, Prestamo $prestamo): void
    {

        // 4. Obtiene el disco y la instancia de Storage una sola vez
        $disk = $this->storageService->obtenerDisco();
        $storage = Storage::disk($disk);

        $cuotas->each(function ($cuota) use ($prestamo, $storage) { // <-- Pasa $storage al closure
            // Por defecto, la URL es nula
            $cuota->captura_pago_url = null;

            // Si la cuota está en estado de "procesando" (5)
            if ($cuota->estado == 5) {
                $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/capturapago";
                
                // Busca todos los archivos usando la instancia de Storage correcta
                $archivos = $storage->files($directorio);

                if (!empty($archivos)) {
                    // Ordena para encontrar el más reciente y asignar su URL
                    rsort($archivos);
                    // Genera la URL usando la instancia de Storage correcta
                    $cuota->captura_pago_url = $storage->url($archivos[0]);
                }
            }
        });

  
    }
}