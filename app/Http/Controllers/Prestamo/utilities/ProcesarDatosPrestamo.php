<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;


class ProcesarDatosPrestamo
{
    /**
     * @var CrearCuotasPrestamo El servicio para generar cuotas.
     */
    protected $creadorCuotas; // 1. Declarar la propiedad

    /**
     * Constructor para inyectar el servicio de creación de cuotas.
     * @param CrearCuotasPrestamo $creadorCuotas
     */
    public function __construct(CrearCuotasPrestamo $creadorCuotas)
    {
        // 2. Asignar la instancia inyectada a la propiedad de la clase
        $this->creadorCuotas = $creadorCuotas;
    }

    //---------------------------------------------------------

    /**
     * Crea un nuevo registro de préstamo en la base de datos.
     * @param array $data Los datos validados del préstamo.
     * @return Prestamo El modelo de Préstamo creado.
     */
    public function crearNuevoPrestamo(array $data)
    {
        // Utilizamos una transacción para la creación
        return DB::transaction(function () use ($data) {
            
            // 1. Establecer valores por defecto/calculados
            $data['fecha_generacion'] = Carbon::now();
            $data['fecha_inicio'] = Carbon::now()->addDays(1); // Ejemplo: Inicia el día siguiente
            $data['estado'] = 1; // 1: vigente (Estado inicial por defecto)

            // Crear el préstamo
            $prestamo = Prestamo::create($data);

            // 2. Generar y guardar el cronograma de pagos usando el servicio
            if ($prestamo) {
                // Ahora $this->creadorCuotas está definido y el error P1014 desaparece.
                $this->creadorCuotas->generarCuotas($prestamo); 
            }

            // Devolver el préstamo con la relación de cuotas cargada para la respuesta
            return $prestamo->load('cuotas');
        });
    }
}