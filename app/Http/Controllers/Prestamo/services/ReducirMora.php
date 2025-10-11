<?php

namespace App\Http\Controllers\Prestamo\services;

use App\Models\Cuota;
use Exception;

class ReducirMora
{
    /**
     * Aplica una reducción porcentual a la mora de una cuota.
     *
     * @param Cuota $cuota La cuota a modificar.
     * @param float $porcentaje El porcentaje de reducción (1-100).
     * @return Cuota La cuota actualizada.
     * @throws Exception Si la cuota no tiene mora o si ya se aplicó una reducción.
     */
    public function execute(Cuota $cuota, float $porcentaje): Cuota
    {
        // 1. Verificar condiciones de negocio
        if ($cuota->cargo_mora <= 0) {
            throw new Exception('Esta cuota no tiene mora para reducir.');
        }
        if ($cuota->reduccion_mora_aplicada) {
            throw new Exception('Ya se ha aplicado una reducción de mora a esta cuota.');
        }

        // 2. Calcular el nuevo valor de la mora
        $moraOriginal = $cuota->cargo_mora;
        $montoAReducir = $moraOriginal * ($porcentaje / 100);
        $nuevaMora = $moraOriginal - $montoAReducir;

        // 3. Actualizar la cuota en la base de datos
        $cuota->update([
            'cargo_mora' => $nuevaMora,
            'mora_reducida' => $porcentaje,
            'reduccion_mora_aplicada' => true,
        ]);
        
        // 4. Devolver la cuota actualizada
        return $cuota->fresh();
    }
}