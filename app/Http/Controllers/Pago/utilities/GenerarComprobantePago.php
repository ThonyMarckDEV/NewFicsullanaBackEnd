<?php

namespace App\Http\Controllers\Pago\utilities;

use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarComprobantePago
{
    /**
     * Genera y guarda un PDF de comprobante.
     *
     * @param Pago $pago El modelo del pago.
     * @param bool $esCancelacion Indica si es una cancelación total.
     * @param Collection|null $cuotasCanceladas Las cuotas que se cancelaron.
     * @return void
     */
    public function execute(Pago $pago, bool $esCancelacion = false, ?Collection $cuotasCanceladas = null): void
    {
        $pago->load(['usuario.datos']);
        $prestamo = $pago->cuota->prestamo->load('cliente.datos');

        // 1. Decidir qué vista y datos usar
        if ($esCancelacion) {
            $view = 'pdfs.pagos.comprobante_cancelacion_ticket';
            $data = ['pago' => $pago, 'prestamo' => $prestamo, 'cuotasCanceladas' => $cuotasCanceladas];
        } else {
            $pago->load('cuota');
            $view = 'pdfs.pagos.comprobante_pago_ticket';
            $data = ['pago' => $pago];
        }

        // 2. Generar el PDF
        $pdf = Pdf::loadView($view, $data)->setPaper([0, 0, 164.4, 841.89], 'portrait');
        $pdfOutput = $pdf->output();

        $fileName = "comprobante-{$pago->numero_operacion}.pdf";

        // 3. Decidir dónde guardar el/los archivo(s)
        if ($esCancelacion) {
            // Guardar el MISMO comprobante para CADA cuota cancelada
            foreach ($cuotasCanceladas as $cuota) {
                $filePath = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/{$fileName}";
                Storage::disk('public')->put($filePath, $pdfOutput);
            }
        } else {
            // Guardar el comprobante solo para la cuota pagada
            $cuota = $pago->cuota;
            $filePath = "clientes/{$prestamo->id_Cliente}/prestamos/{$prestamo->id}/cuotas/{$cuota->id}/{$fileName}";
            Storage::disk('public')->put($filePath, $pdfOutput);
        }
    }
}