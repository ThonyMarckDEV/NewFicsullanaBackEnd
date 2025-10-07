<?php

namespace App\Http\Controllers\Prestamo\utilities;

use App\Http\Controllers\Prestamo\services\CrearCronograma;
use App\Http\Controllers\Prestamo\services\CrearCuotasPrestamo;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcesarReprogramacion
{
    protected $creadorCuotas;
    protected $creadorCronograma;

    public function __construct(CrearCuotasPrestamo $creadorCuotas, CrearCronograma $creadorCronograma)
    {
        $this->creadorCuotas = $creadorCuotas;
        $this->creadorCronograma = $creadorCronograma;
    }

    public function execute(Prestamo $prestamo, array $data): Prestamo
    {
        return DB::transaction(function () use ($prestamo, $data) {
            
            $cuotasPendientes = $prestamo->cuota()->where('estado', '!=', 2)->orderBy('numero_cuota')->get();
            $cuotasPagadasCount = $prestamo->cuota()->where('estado', 2)->count();

            if ($cuotasPendientes->isEmpty()) {
                throw new \Exception("Este préstamo no tiene cuotas pendientes para reprogramar.");
            }

            $deudaReprogramar = 0;
            foreach ($cuotasPendientes as $cuota) {
                $deudaReprogramar += ($cuota->capital + $cuota->cargo_mora) - $cuota->excedente_anterior;
            }
            $deudaReprogramar = max(0, $deudaReprogramar);
            
            // Eliminar todas las cuotas pendientes antiguas.
            $prestamo->cuota()->where('estado', '!=', 2)->delete();

            // Actualizar los datos del préstamo principal.
            $prestamo->monto = $deudaReprogramar;
            $prestamo->interes = $data['nueva_tasa'];
            $prestamo->frecuencia = $data['nueva_frecuencia'];
            
            $numeroCuotasRestantes = $cuotasPendientes->count();
            $nuevoTotal = $deudaReprogramar * (1 + $data['nueva_tasa']);
            $nuevoValorCuota = $numeroCuotasRestantes > 0 ? $nuevoTotal / $numeroCuotasRestantes : 0;

            $prestamo->total = $nuevoTotal;
            $prestamo->valor_cuota = $nuevoValorCuota;
            
            $ultimaCuotaPagada = $prestamo->cuota()->where('estado', 2)->orderBy('numero_cuota', 'desc')->first();
            $prestamo->fecha_inicio = $ultimaCuotaPagada 
                ? Carbon::parse($ultimaCuotaPagada->fecha_vencimiento)->addDay()
                : Carbon::now()->addDay();
                
            $prestamo->save();

            // Generar las nuevas cuotas y el nuevo cronograma.
            $this->creadorCuotas->generarCuotas($prestamo, $cuotasPagadasCount, $numeroCuotasRestantes);
            $this->creadorCronograma->generar($prestamo);

            return $prestamo->fresh(['cliente.datos', 'asesor.datos', 'producto', 'cuota']);
        });
    }
}