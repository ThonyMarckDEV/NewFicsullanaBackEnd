<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Prestamo;
use App\Models\Cuota; 
use Carbon\Carbon;
use Exception;

class CrearCuotasPrestamo
{
    /**
     * Genera y guarda las cuotas para un préstamo.
     * Esta versión corrige los errores de redondeo y calcula el campo 'otros'.
     */
    public function generarCuotas(Prestamo $prestamo, int $cuotasPagadas = 0, ?int $cuotasAGenerar = null): void
    {
        $capitalTotal = $prestamo->monto;
        $interesTotal = $prestamo->total - $prestamo->monto;
        $numeroCuotas = $cuotasAGenerar ?? $prestamo->cuotas;
        
        if ($numeroCuotas <= 0) {
            return;
        }

        $montoCuotaFija = round($prestamo->valor_cuota, 2);
        $capitalFijo = round($capitalTotal / $numeroCuotas, 2);
        $interesFijo = round($interesTotal / $numeroCuotas, 2);
        
        $fechaVencimiento = Carbon::parse($prestamo->fecha_inicio);
        $cuotas = [];

        for ($i = 1; $i <= $numeroCuotas; $i++) {
            
            switch (strtoupper($prestamo->frecuencia)) {
                case 'SEMANAL': $fechaVencimiento->addWeek(); break;
                case 'CATORCENAL': $fechaVencimiento->addWeeks(2); break;
                case 'MENSUAL': $fechaVencimiento->addMonth(); break;
                default: throw new Exception("Frecuencia no soportada: {$prestamo->frecuencia}");
            }

            if ($i === $numeroCuotas) {
                $capitalAcumulado = $capitalFijo * ($numeroCuotas - 1);
                $interesAcumulado = $interesFijo * ($numeroCuotas - 1);

                $capitalCuota = $capitalTotal - $capitalAcumulado;
                $interesCuota = $interesTotal - $interesAcumulado;
                $montoCuota = $capitalCuota + $interesCuota;
            } else {
                $capitalCuota = $capitalFijo;
                $interesCuota = $interesFijo;
                $montoCuota = $montoCuotaFija;
            }

            // --- INICIO DE LA CORRECCIÓN ---
            // 1. Calcular el valor de 'otros' para cada cuota.
            // Es la diferencia que queda para que (capital + interes + otros) sea igual al monto de la cuota.
            $otrosCuota = round($montoCuota - $capitalCuota - $interesCuota, 2);
            // --- FIN DE LA CORRECCIÓN ---

            $cuotas[] = [
                'id_Prestamo' => $prestamo->id,
                'numero_cuota' => $cuotasPagadas + $i,
                'monto' => $montoCuota,
                'capital' => $capitalCuota,
                'interes' => $interesCuota,
                'excedente_anterior' => 0,
                'otros' => $otrosCuota, // 2. Añadir el valor calculado.
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'estado' => 1, // 1: pendiente
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (!empty($cuotas)) {
            $prestamo->cuota()->insert($cuotas);
        }
    }
}