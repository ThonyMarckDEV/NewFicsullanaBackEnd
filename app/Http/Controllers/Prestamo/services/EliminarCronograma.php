<?php

namespace App\Http\Controllers\Prestamo\services;

use Illuminate\Support\Facades\Storage;

class EliminarCronograma
{
    /**
     * Elimina el directorio completo de un préstamo, incluyendo sus cronogramas.
     *
     * @param int $clienteId El ID del cliente.
     * @param int $prestamoId El ID del préstamo.
     * @return void
     */
    public function execute(int $clienteId, int $prestamoId): void
    {
        // 1. Construir la ruta del directorio del préstamo.
        $directorioPrestamo = "clientes/{$clienteId}/prestamos/{$prestamoId}";

        // 2. Verificar si el directorio existe antes de intentar borrarlo.
        if (Storage::disk('public')->exists($directorioPrestamo)) {
            // 3. Eliminar el directorio completo del almacenamiento 'public'.
            Storage::disk('public')->deleteDirectory($directorioPrestamo);
        }
    }
}