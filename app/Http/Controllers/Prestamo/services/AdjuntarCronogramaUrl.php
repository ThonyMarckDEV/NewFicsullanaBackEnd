<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\Storage;

class AdjuntarCronogramaUrl
{
    /**
     * Busca el PDF del cronograma más reciente y añade su URL al modelo del préstamo.
     */
    public function execute(Prestamo $prestamo): void
    {
        $directorio = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cronograma";
        $archivos = Storage::disk('public')->files($directorio);
        
        if (!empty($archivos)) {
            rsort($archivos);
            $prestamo->cronograma_url = Storage::url($archivos[0]);
        } else {
            $prestamo->cronograma_url = null;
        }
    }
}