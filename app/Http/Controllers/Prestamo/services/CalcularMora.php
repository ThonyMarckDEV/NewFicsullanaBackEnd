<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\CargoMora;
use Carbon\Carbon;

class CalcularMora
{
    /**
     * Ejecuta la lógica de cálculo de mora para una cuota.
     *
     * @param Cuota $cuota La cuota a procesar.
     * @return Cuota La cuota actualizada.
     */
    public function execute(Cuota $cuota): Cuota
    {
        // No hacer nada si la cuota ya está pagada o prepagada.
        if (in_array($cuota->estado, [2, 5])) {
            return $cuota;
        }

        $hoy = Carbon::today();
        $fechaVencimiento = Carbon::parse($cuota->fecha_vencimiento);

        // --- Caso 1: La cuota vence hoy ---
        if ($fechaVencimiento->isToday()) {
            $cuota->estado = 3; // 3: vence_hoy
            $cuota->dias_mora = 0;
            $cuota->cargo_mora = 0.00;
            $cuota->save();
            return $cuota;
        }

        // --- Caso 2: La cuota está vencida ---
        if ($fechaVencimiento->isPast()) {
            // Si la mora ya fue aplicada hoy, no hagas nada hasta mañana.
            if ($cuota->fecha_mora_aplicada && Carbon::parse($cuota->fecha_mora_aplicada)->isToday()) {
                return $cuota;
            }

            // Calculamos los días de mora transcurridos.
            $diasDeMora = $fechaVencimiento->diffInDays($hoy);
            
            // Obtenemos el monto del préstamo asociado a la cuota.
            $montoPrestamo = $cuota->prestamo->monto;

            // Buscamos la regla de cargo por mora que corresponde a los días de vencimiento.
            $reglaCargoMora = $this->findCargoMoraRule($diasDeMora);

            $cargoAAplicar = 0.00;
            if ($reglaCargoMora) {
                // Obtenemos el nombre de la columna correcta según el monto del préstamo.
                $columnaMonto = $this->getMontoColumn($montoPrestamo);
                $cargoAAplicar = $reglaCargoMora->{$columnaMonto} ?? 0.00;
            }

            // Actualizamos la cuota con los nuevos valores.
            $cuota->estado = 4; // 4: vencido
            $cuota->dias_mora = $diasDeMora;
            $cuota->cargo_mora = $cargoAAplicar;
            $cuota->mora_aplicada = true;
            $cuota->fecha_mora_aplicada = Carbon::now();
            $cuota->save();

            return $cuota;
        }

        // Si la cuota aún no vence, no se hace nada.
        return $cuota;
    }
    
    /**
     * Determina el nombre de la columna de monto a usar según el valor del préstamo.
     * (Esta función no necesita cambios)
     */
    private function getMontoColumn(float $monto): string
    {
        if ($monto >= 300 && $monto <= 1000) {
            return 'monto_300_1000';
        }
        if ($monto >= 1001 && $monto <= 2000) {
            return 'monto_1001_2000';
        }
        if ($monto >= 2001 && $monto <= 3000) {
            return 'monto_2001_3000';
        }
        if ($monto >= 3001 && $monto <= 4000) {
            return 'monto_3001_4000';
        }
        if ($monto >= 4001 && $monto <= 5000) {
            return 'monto_4001_5000';
        }
        if ($monto >= 5001 && $monto <= 6000) {
            return 'monto_5001_6000';
        }
        if ($monto > 6000) {
            return 'monto_mas_6000';
        }
        return 'monto_300_1000'; // Fallback
    }

    /**
     * === FUNCIÓN CORREGIDA ===
     * Busca la regla de cargo por mora en la BD según los días de atraso.
     *
     * @param int $diasDeMora
     * @return CargoMora|null
     */
    private function findCargoMoraRule(int $diasDeMora): ?CargoMora
    {
        $etiquetaDias = '';

        if ($diasDeMora == 1) {
            $etiquetaDias = '1 día';
        } elseif ($diasDeMora >= 2 && $diasDeMora <= 3) {
            $etiquetaDias = '2-3 días';
        } elseif ($diasDeMora >= 4 && $diasDeMora <= 7) {
            $etiquetaDias = '4-7 días';
        } elseif ($diasDeMora >= 8 && $diasDeMora <= 10) {
            $etiquetaDias = '8-10 días';
        } elseif ($diasDeMora >= 11 && $diasDeMora <= 15) {
            $etiquetaDias = '10-15 días';
        } elseif ($diasDeMora >= 16 && $diasDeMora <= 30) {
            $etiquetaDias = '16-30 días';
        } elseif ($diasDeMora >= 31 && $diasDeMora <= 40) {
            $etiquetaDias = '31-40 días';
        } elseif ($diasDeMora >= 41 && $diasDeMora <= 50) {
            $etiquetaDias = '41-50 días';
        } elseif ($diasDeMora >= 51 && $diasDeMora <= 60) {
            $etiquetaDias = '51-60 días';
        } elseif ($diasDeMora >= 61 && $diasDeMora <= 90) {
            $etiquetaDias = '61-90 días';
        } elseif ($diasDeMora >= 91 && $diasDeMora <= 120) {
            $etiquetaDias = '91-120 días';
        } elseif ($diasDeMora > 120) {
            $etiquetaDias = 'más de 120 días';
        }

        // Si encontramos una etiqueta válida, buscamos en la base de datos.
        if (!empty($etiquetaDias)) {
            return CargoMora::where('dias', $etiquetaDias)->first();
        }

        // Si no se encuentra ninguna regla, devuelve null.
        return null;
    }
}