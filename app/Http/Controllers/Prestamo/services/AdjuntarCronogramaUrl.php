<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\Storage;

class AdjuntarCronogramaUrl
{
    // 2. Inyectar el servicio en el constructor
    protected $storageService;

    public function __construct(VerificarEstadoStorage $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Busca el PDF del cronograma más reciente y añade su URL al modelo del préstamo.
     */
    public function execute(Prestamo $prestamo): void
    {
        // 3. Usar el servicio para obtener el disco
        $disk = $this->storageService->obtenerDisco();
        $storage = Storage::disk($disk);

        $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cronograma";
        
        // El resto de la lógica no cambia, solo que ahora usa el disco correcto
        $archivos = $storage->files($directorio);
        
        if (!empty($archivos)) {
            rsort($archivos);
            $prestamo->cronograma_url = $storage->url($archivos[0]);
        } else {
            $prestamo->cronograma_url = null;
        }
    }
}