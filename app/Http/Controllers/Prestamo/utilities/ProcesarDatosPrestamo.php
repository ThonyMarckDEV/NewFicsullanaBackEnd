<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcesarDatosPrestamo
{
    protected $creadorCuotas;
    protected $liquidadorPrestamo;
    protected $creadorCronograma; // 1. Añadir la nueva propiedad

    /**
     * Constructor para inyectar los servicios necesarios.
     */
    public function __construct(
        CrearCuotasPrestamo $creadorCuotas, 
        LiquidarPrestamoAnterior $liquidadorPrestamo,
        CrearCronograma $creadorCronograma // 2. Inyectar el nuevo servicio
    ) {
        $this->creadorCuotas = $creadorCuotas;
        $this->liquidadorPrestamo = $liquidadorPrestamo;
        $this->creadorCronograma = $creadorCronograma; // 3. Asignar el servicio
    }

    /**
     * Crea un nuevo registro de préstamo, aplicando lógicas y generando el cronograma.
     */
    public function crearNuevoPrestamo(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            // LÓGICA PARA MODALIDAD RCS
            if ($data['modalidad'] === 'RCS') {
                $this->liquidadorPrestamo->execute($data['id_Cliente']);
            }

            // CREACIÓN DEL NUEVO PRÉSTAMO
            $data['fecha_generacion'] = Carbon::now();
            $data['fecha_inicio'] = Carbon::now()->addDays(1);
            $data['estado'] = 1; // 1: vigente

            $prestamo = Prestamo::create($data);

            if ($prestamo) {
                // Generar las cuotas en la base de datos
                $this->creadorCuotas->generarCuotas($prestamo);
                
                // 4. Generar y guardar el PDF del cronograma
                // Esta llamada ocurre después de que el préstamo y las cuotas ya existen.
                $this->creadorCronograma->generar($prestamo);
            }

            return $prestamo->load('cuota');
        });
    }
}