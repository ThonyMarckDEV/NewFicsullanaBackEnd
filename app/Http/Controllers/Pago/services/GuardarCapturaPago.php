<?php

namespace App\Http\Controllers\Pago\services;

use App\Models\Cuota;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuardarCapturaPago
{
    /**
     * Guarda el archivo de captura de pago del cliente y devuelve la ruta.
     *
     * @param UploadedFile $file El archivo del comprobante subido.
     * @param Cuota $cuota La cuota asociada al pago.
     * @return string La ruta relativa del archivo guardado.
     * @throws \Exception Si el archivo subido no es válido o si falla al guardar.
     */
    public function execute(UploadedFile $file, Cuota $cuota): string // CAMBIO: Ahora retorna STRING
    {
        // 1. Obtener los IDs
        $prestamo = $cuota->prestamo;
        $clienteId = $prestamo->id_Cliente;
        $prestamoId = $prestamo->id;
        $cuotaId = $cuota->id;

        // 2. VERIFICACIÓN CRÍTICA
        if (!$file || !$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            Log::error("GuardarCapturaPago: Archivo inválido o error en la subida.", ['error_code' => $file->getError()]);
            throw new \Exception("El archivo subido no es válido o falló en el servidor. Código de error: " . $file->getError());
        }

        // 3. Generar nombre de archivo único y seguro.
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($originalName) . '-' . time() . '.' . $extension;

        // 4. Definir la ruta de destino (el directorio).
        $path = "clientes/{$clienteId}/prestamos/{$prestamoId}/cuotas/{$cuotaId}/capturapago";
        
      //  Log::info("GuardarCapturaPago: Intentando guardar archivo.", ['destino_path' => $path, 'filename' => $filename]);

        try {
            // 5. Guardar el archivo en el disco 'public'.
            $filePath = Storage::disk('public')->putFileAs(
                $path,
                $file,
                $filename
            );

            if ($filePath === false) {
                throw new \Exception("Fallo desconocido al mover el archivo a la ruta de almacenamiento.");
            }
            
          //  Log::info("GuardarCapturaPago: Archivo guardado y cuota actualizada.", ['file_path' => $filePath]);
            
            // 7. DEVOLVER LA RUTA
            return $filePath; // <<-- ¡AHORA DEVOLVEMOS LA RUTA!

        } catch (\Exception $e) {
            Log::error("GuardarCapturaPago: Error al guardar archivo.", ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}