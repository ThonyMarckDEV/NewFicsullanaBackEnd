<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use App\Models\Cuota; 
use Carbon\Carbon;
use Exception;

class CrearCuotasPrestamo
{
    /**
     * Mapea la frecuencia a la unidad de tiempo para Carbon.
     * @param string $frecuencia
     * @return string
     */
    protected function getPeriodicidad(string $frecuencia): string
    {
        return match (strtoupper($frecuencia)) {
            'SEMANAL' => 'addWeek',
            'CATORCENAL' => 'addWeeks', // Usaremos addWeeks(2)
            'MENSUAL' => 'addMonth',
            default => throw new Exception("Frecuencia de pago no soportada: {$frecuencia}"),
        };
    }

    /**
     * Genera y guarda las cuotas para un préstamo (Lógica de Montos Fijos).
     *
     * Los montos fijos (capital e interés) se calculan usando los campos 'monto', 'total' y 'cuotas'
     * del modelo Prestamo.
     * @param Prestamo $prestamo El modelo de Préstamo con los datos necesarios.
     * @return void
     */
    public function generarCuotas(Prestamo $prestamo): void
    {
        // 1. Obtener y calcular los montos FIJOS
        $capitalTotal = $prestamo->monto;
        $totalPagar = $prestamo->total;
        $numeroCuotas = $prestamo->cuotas;
        $montoCuotaFija = $prestamo->valor_cuota;
        
        // CÁLCULO DEL INTERÉS TOTAL Y DISTRIBUCIÓN
        $interesTotal = $totalPagar - $capitalTotal;
        
        // ** 1. Interés Fijo por Cuota **
        // Se distribuye el interés total de forma equitativa en cada cuota.
        $interesFijo = round($interesTotal / $numeroCuotas, 2);
        
        // ** 2. Capital Fijo por Cuota **
        // Se distribuye el capital total de forma equitativa en cada cuota.
        $capitalFijo = round($capitalTotal / $numeroCuotas, 2);

        // ** 3. Otros Fijos por Cuota **
        // Se calcula como el residuo del valor_cuota después de sumar capital e interés.
        $otrosFijos = round($montoCuotaFija - $capitalFijo - $interesFijo, 2);


        // 2. Preparar fechas e iteración
        $frecuencia = $prestamo->frecuencia;
        $fechaVencimiento = Carbon::parse($prestamo->fecha_inicio);
        $periodicidadCarbon = $this->getPeriodicidad($frecuencia);
        $cuotas = [];

        // 3. Iteración para generar las cuotas
        for ($i = 1; $i <= $numeroCuotas; $i++) {
            
            // Establecer la fecha de vencimiento
            if ($i > 1) {
                if ($frecuencia === 'CATORCENAL') {
                     $fechaVencimiento->addWeeks(2);
                } else {
                     $fechaVencimiento->{$periodicidadCarbon}();
                }
            }

            // Los montos ya están calculados y son constantes.

            $cuotas[] = [
                'id_Prestamo' => $prestamo->id,
                'numero_cuota' => $i,
                'monto' => $montoCuotaFija,       // Fijo del campo valor_cuota del préstamo
                'capital' => $capitalFijo,       // Fijo: Capital Total / Cuotas
                'interes' => $interesFijo,       // Fijo: Interés Total / Cuotas
                'otros' => $otrosFijos,          // Fijo: Residuo para cuadrar la cuota
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'estado' => 1,                   // 1: pendiente
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 4. Insertar todas las cuotas
        if (!empty($cuotas)) {
            $prestamo->cuota()->createMany($cuotas); 
        }
    }
}