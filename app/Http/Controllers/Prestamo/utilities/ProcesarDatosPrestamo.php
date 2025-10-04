<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ProcesarDatosPrestamo
{
    /**
     * Crea un nuevo registro de préstamo en la base de datos.
     * * @param array $data Los datos validados del préstamo.
     * @return Prestamo El modelo de Préstamo creado.
     */
    public function crearNuevoPrestamo(array $data)
    {
        // Utilizamos una transacción para la creación
        return DB::transaction(function () use ($data) {
            
            // Establecer valores por defecto/calculados si no vienen del frontend
            $data['fecha_generacion'] = Carbon::now();
            $data['fecha_inicio'] = Carbon::now()->addDays(1); // Ejemplo: Inicia el día siguiente
            $data['estado'] = 1; // 1: vigente (Estado inicial por defecto)

            // Crear el préstamo
            $prestamo = Prestamo::create($data);

            // TODO: Lógica adicional, como generar el cronograma de pagos.
            // if ($prestamo) {
            //     $this->generarCronograma($prestamo);
            // }

            return $prestamo;
        });
    }

    // TODO: (Opcional) Puedes añadir aquí la función generarCronograma() si es necesario.
    // ...
}