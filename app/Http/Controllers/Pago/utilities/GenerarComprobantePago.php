<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Pago;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarComprobantePago
{
    /**
     * Genera y guarda un PDF del comprobante de pago en formato ticket.
     *
     * @param Pago $pago El modelo del pago recién creado.
     * @return string La ruta donde se guardó el archivo.
     */
    public function execute(Pago $pago): string
    {
        // 1. Cargar las relaciones necesarias.
        $pago->load(['cuota.prestamo.cliente.datos', 'usuario.datos']);

        // --- CORRECCIÓN AQUÍ ---
        // 2. Apuntar a la nueva vista de ticket.
        $pdf = Pdf::loadView('pdfs.pagos.comprobante_pago_ticket', ['pago' => $pago]);
        
        // Configurar el tamaño del papel para tiquetera (58mm de ancho)
        $pdf->setPaper([0, 0, 164.4, 841.89], 'portrait'); // 58mm en puntos

        // 3. Definir la ruta y el nombre del archivo.
        $cuota = $pago->cuota;
        $prestamo = $cuota->prestamo;

        $fileName = "comprobante-{$pago->numero_operacion}.pdf";
        $filePath = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/{$fileName}";

        // 4. Guardar el PDF en el disco 'public'.
        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }
}