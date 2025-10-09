<?php

namespace App\Http\Controllers\Prestamo\services;

use Illuminate\Support\Facades\Storage;

class EliminarCronograma
{
    /**
     * Instancia del servicio para verificar el estado del almacenamiento.
     *
     * @var VerificarEstadoStorage
     */
    private $verificadorStorage;

    /**
     * Constructor que inyecta el servicio de verificación de almacenamiento.
     *
     * @param VerificarEstadoStorage $verificadorStorage
     */
    public function __construct(VerificarEstadoStorage $verificadorStorage)
    {
        $this->verificadorStorage = $verificadorStorage;
    }

    /**
     * Elimina el directorio completo de un préstamo, incluyendo sus cronogramas,
     * determinando primero el disco de almacenamiento correcto.
     *
     * @param int $clienteId El ID del cliente.
     * @param int $prestamoId El ID del préstamo.
     * @return void
     */
    public function execute(int $clienteId, int $prestamoId): void
    {
        // 1. Determinar qué disco se está utilizando ('minio' o 'public').
        $disco = $this->verificadorStorage->obtenerDisco();

        // 2. Construir la ruta del directorio del préstamo.
        $directorioPrestamo = "clientes/{$clienteId}/prestamos/{$prestamoId}";

        // 3. Verificar si el directorio existe en el disco correspondiente.
        if (Storage::disk($disco)->exists($directorioPrestamo)) {
            // 4. Eliminar el directorio completo del almacenamiento correcto.
            Storage::disk($disco)->deleteDirectory($directorioPrestamo);
        }
    }
}