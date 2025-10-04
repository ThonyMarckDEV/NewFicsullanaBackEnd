<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcesarDatosPrestamo
{
    protected $creadorCuotas;
    protected $liquidadorPrestamo;

    /**
     * Constructor para inyectar los servicios necesarios.
     */
    // MODIFICADO: Inyectamos la nueva clase 'LiquidarPrestamoAnterior'
    public function __construct(
        CrearCuotasPrestamo $creadorCuotas, 
        LiquidarPrestamoAnterior $liquidadorPrestamo
    ) {
        $this->creadorCuotas = $creadorCuotas;
        $this->liquidadorPrestamo = $liquidadorPrestamo; // La asignamos
    }

    /**
     * Crea un nuevo registro de préstamo, aplicando lógicas según la modalidad.
     */
    public function crearNuevoPrestamo(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            // LÓGICA PARA MODALIDAD RCS
            if ($data['modalidad'] === 'RCS') {
                // MODIFICADO: Llamamos al método 'execute' de la nueva clase
                $this->liquidadorPrestamo->execute($data['id_Cliente']);
            }

            // CREACIÓN DEL NUEVO PRÉSTAMO (común para todas las modalidades)
            $data['fecha_generacion'] = Carbon::now();
            $data['fecha_inicio'] = Carbon::now()->addDays(1);
            $data['estado'] = 1; // 1: vigente

            $prestamo = Prestamo::create($data);

            if ($prestamo) {
                $this->creadorCuotas->generarCuotas($prestamo); 
            }

            return $prestamo->load('cuota');
        });
    }

    
}