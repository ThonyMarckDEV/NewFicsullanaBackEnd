<?php

namespace App\Http\Controllers\Pago\services;

use App\Http\Controllers\Prestamo\services\VerificarEstadoStorage;
use App\Models\Cuota;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuardarCapturaPago
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
     * Guarda el archivo de captura de pago del cliente y devuelve la ruta.
     *
     * @param UploadedFile $file El archivo del comprobante subido.
     * @param Cuota $cuota La cuota asociada al pago.
     * @return string La ruta relativa del archivo guardado.
     * @throws \Exception Si el archivo subido no es válido o si falla al guardar.
     */
    public function execute(UploadedFile $file, Cuota $cuota): string
    {

        $prestamo = $cuota->prestamo;
        $clienteId = $prestamo->id_Cliente;
        $prestamoId = $prestamo->id;
        $cuotaId = $cuota->id;

        if (!$file || !$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            Log::error("GuardarCapturaPago: Archivo inválido o error en la subida.", ['error_code' => $file->getError()]);
            throw new \Exception("El archivo subido no es válido. Código de error: " . $file->getError());
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($originalName) . '-' . time() . '.' . $extension;

        $path = "clientes/{$clienteId}/prestamos/{$prestamoId}/cuotas/{$cuotaId}/capturapago";
        
        try {

            // 4. Obtiene el disco correcto (local o minio) dinámicamente
            $disk = $this->storageService->obtenerDisco();

            // 5. Guarda el archivo usando el disco determinado por el servicio
            $filePath = Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $filename
            );


            if ($filePath === false) {
                throw new \Exception("Fallo desconocido al mover el archivo a la ruta de almacenamiento.");
            }
            
            // 6. Devuelve la ruta relativa del archivo guardado
            return $filePath;

        } catch (\Exception $e) {
            Log::error("GuardarCapturaPago: Error al guardar archivo.", ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}