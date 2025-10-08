<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CrearCronograma
{
    // /**
    //  * Genera un PDF del cronograma de pagos y lo guarda en el almacenamiento público.
    //  *
    //  * @param Prestamo $prestamo El modelo del préstamo con sus relaciones cargadas.
    //  * @return string La ruta donde se guardó el archivo PDF.
    //  */
    // public function generar(Prestamo $prestamo): string
    // {
    //     // 1. Asegurarse de que todas las relaciones necesarias estén cargadas
    //     $prestamo->loadMissing(['cliente.datos', 'asesor.datos', 'cuota', 'producto']);

    //     // 2. Cargar la vista de Blade y pasarle los datos del préstamo
    //     $pdf = Pdf::loadView('pdfs.prestamos.cronograma_pdf', ['prestamo' => $prestamo]);

    //     // 3. Definir el nombre y la ruta del archivo
    //     $prestamoId = $prestamo->id;
        
    //     // ===== INICIO DE LA CORRECCIÓN =====
    //     $idCliente = $prestamo->id_Cliente; // Obtenemos el ID del cliente desde el préstamo
    //     // ===== FIN DE LA CORRECCIÓN =====

    //     $timestamp = now()->format('Ymd-His');
    //     $fileName = "cronograma-{$timestamp}.pdf";
        
    //     // Usamos la variable $idCliente que acabamos de definir
    //     $filePath = "clientes/{$idCliente}/prestamos/{$prestamoId}/cronograma/{$fileName}";

    //     // 4. Guardar el PDF en el disco 'public'
    //     Storage::disk('public')->put($filePath, $pdf->output());

    //     // 5. Devolver la ruta del archivo guardado
    //     return $filePath;
    // }

     /**
     * @var VerificarEstadoStorage
     */
    protected $verificadorStorage;

    /**
     * CrearCronograma constructor.
     *
     * @param VerificarEstadoStorage $verificadorStorage
     */
    public function __construct(VerificarEstadoStorage $verificadorStorage)
    {
        $this->verificadorStorage = $verificadorStorage;
    }

    /**
     * Genera un PDF del cronograma de pagos y lo guarda en el almacenamiento correspondiente.
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
        $idCliente = $prestamo->id_Cliente;
        $timestamp = now()->format('Ymd-His');
        $fileName = "cronograma-{$timestamp}.pdf";
        
        // La ruta será la misma independientemente del disco
        $filePath = "clientes/{$idCliente}/prestamos/{$prestamoId}/cronograma/{$fileName}";

        // 4. Determinar en qué disco guardar el archivo
        // Si el estado es 2, se usará 'minio' y guardará en el bucket 'ficsullana'.
        // De lo contrario, usará el disco 'public'.
        $disk = $this->verificadorStorage->obtenerDisco();

        // 5. Guardar el PDF en el disco determinado ('public' o 'minio')
        Storage::disk($disk)->put($filePath, $pdf->output());

        // 6. Devolver la ruta del archivo guardado
        return $filePath;
    }
}