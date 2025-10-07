<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class AdjuntarCapturaPagoUrl
{
    /**
     * Itera sobre una colección de cuotas y adjunta la URL del comprobante más reciente a las que están pagadas.
     *
     * @param Collection $cuotas La colección de cuotas de un préstamo.
     * @param Prestamo $prestamo El préstamo padre.
     * @return void
     */
    public function execute(Collection $cuotas, Prestamo $prestamo): void
    {
        $cuotas->each(function ($cuota) use ($prestamo) {
            // Por defecto, la URL es nula
            $cuota->captura_pago_url = null;

            // Si la cuota está prepagada (estado 5)
            if ($cuota->estado == 5) {
                $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/capturapago";
                
                // Buscar todos los archivos en ese directorio
                $archivos = Storage::disk('public')->files($directorio);

                if (!empty($archivos)) {
                    // Ordenar para encontrar el más reciente y asignar su URL
                    rsort($archivos);
                    $cuota->captura_pago_url = Storage::url($archivos[0]);
                }
            }
        });
    }
}