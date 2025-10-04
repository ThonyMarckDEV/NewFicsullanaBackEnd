<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CrearCronograma
{
    /**
     * Genera un PDF del cronograma de pagos y lo guarda en el almacenamiento público.
     *
     * @param Prestamo $prestamo El modelo del préstamo con sus relaciones cargadas.
     * @return string La ruta donde se guardó el archivo PDF.
     */
    public function generar(Prestamo $prestamo): string
    {
        // 1. Asegurarse de que todas las relaciones necesarias estén cargadas
        $prestamo->loadMissing(['cliente.datos', 'asesor.datos', 'cuota', 'producto']);

        // 2. Cargar la vista de Blade y pasarle los datos del préstamo
        $pdf = Pdf::loadView('pdfs.prestamos.cronograma_pdf', ['prestamo' => $prestamo]);

        // 3. Definir el nombre y la ruta del archivo
        $prestamoId = $prestamo->id;
        
        // ===== INICIO DE LA CORRECCIÓN =====
        $idCliente = $prestamo->id_Cliente; // Obtenemos el ID del cliente desde el préstamo
        // ===== FIN DE LA CORRECCIÓN =====

        $timestamp = now()->format('Ymd-His');
        $fileName = "cronograma-{$timestamp}.pdf";
        
        // Usamos la variable $idCliente que acabamos de definir
        $filePath = "clientes/{$idCliente}/prestamos/{$prestamoId}/cronograma/{$fileName}";

        // 4. Guardar el PDF en el disco 'public'
        Storage::disk('public')->put($filePath, $pdf->output());

        // 5. Devolver la ruta del archivo guardado
        return $filePath;
    }
}