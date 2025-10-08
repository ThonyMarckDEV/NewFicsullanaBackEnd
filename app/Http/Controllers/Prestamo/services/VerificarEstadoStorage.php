<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Config;

class VerificarEstadoStorage
{
    /**
     * Determina qué disco de almacenamiento se debe usar basado en la configuración de la BD.
     * Realiza la consulta directamente sin usar caché.
     *
     * @return string El nombre del disco a utilizar ('minio' o 'public').
     */
    public function obtenerDisco(): string
    {
        // 1. Consulta la configuración de almacenamiento directamente en la base de datos.
        $storageConfig = Config::where('tipo', 'storage')->first();
        
        // 2. Determina el disco a usar. Si la config existe y su estado es 2, usa 'minio'.
        //    De lo contrario, usa 'public' por defecto.
        return ($storageConfig && $storageConfig->estado == 2) ? 'minio' : 'public';
    }
}