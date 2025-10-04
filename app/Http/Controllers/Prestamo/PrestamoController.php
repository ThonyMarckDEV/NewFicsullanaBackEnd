<?php

namespace App\Http\Controllers\Prestamo;

use App\Http\Controllers\Prestamo\utilities\AdjuntarComprobanteUrl;
use App\Http\Controllers\Prestamo\utilities\AdjuntarCronogramaUrl;
use App\Http\Controllers\Prestamo\utilities\CrearCronograma;
use App\Http\Controllers\Prestamo\utilities\CrearCuotasPrestamo;
use App\Http\Controllers\Prestamo\utilities\EliminarCronograma;
use App\Http\Controllers\Prestamo\utilities\ProcesarDatosPrestamo;
use App\Http\Controllers\Prestamo\utilities\ProcesarReprogramacion;
use App\Http\Controllers\Prestamo\utilities\VerificarCuotasPagadas;
use App\Http\Requests\ReprogramarPrestamoRequest;
use App\Http\Requests\StorePrestamoRequest;
use App\Http\Requests\UpdatePrestamoRequest;
use App\Models\Prestamo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PrestamoController extends Controller
{
   public function index(Request $request, VerificarCuotasPagadas $verificador, AdjuntarCronogramaUrl $adjuntadorUrl)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:50',
            'sort_by' => 'nullable|string|in:id,monto,frecuencia,fecha_generacion,estado',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $searchQuery = $validated['search'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'id';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        $prestamos = Prestamo::with(['cliente.datos', 'asesor.datos'])
            ->when($searchQuery, function ($query, $search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhereHas('cliente.datos', function ($q) use ($search) {
                        $q->where('dni', 'like', "%{$search}%")
                          ->orWhere('nombre', 'like', "%{$search}%")
                          ->orWhere('apellidoPaterno', 'like', "%{$search}%");
                    });
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10);
        
        // 2. Usar los servicios para transformar la colección
        $prestamos->getCollection()->transform(function ($prestamo) use ($verificador, $adjuntadorUrl) {
            $verificador->execute($prestamo);
            $adjuntadorUrl->execute($prestamo);
            return $prestamo;
        });

        return response()->json($prestamos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un nuevo préstamo.
     *
     * @param  \App\Http\Requests\StorePrestamoRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePrestamoRequest $request , ProcesarDatosPrestamo $procesador)
    {
        try {
            $validatedData = $request->validated();

            $prestamo = $procesador->crearNuevoPrestamo($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Préstamo creado con éxito.',
                'data' => $prestamo,
            ], 201); // 201 Created
        } catch (\Exception $e) {
            // Log::error('Error al crear préstamo: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Error al guardar el préstamo.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra los detalles de un préstamo, incluyendo la URL del comprobante de cada cuota pagada.
     *
     * @param Prestamo $prestamo
     * @param AdjuntarComprobanteUrl $adjuntador El servicio para adjuntar las URLs.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Prestamo $prestamo, AdjuntarComprobanteUrl $adjuntador)
    {
        // Carga las relaciones principales
        $prestamo->load(['cliente.datos', 'asesor.datos', 'producto', 'cuota']);

        // 2. Delegar la lógica de adjuntar URLs al servicio
        $adjuntador->execute($prestamo->cuota, $prestamo);

        return response()->json($prestamo);
    }


      /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdatePrestamoRequest $request, 
        Prestamo $prestamo, 
        CrearCuotasPrestamo $creadorCuotas, 
        CrearCronograma $creadorCronograma,
        EliminarCronograma $eliminadorCronograma
    ) {
        try {
            $validatedData = $request->validated();
            
            DB::transaction(function () use ($prestamo, $validatedData, $creadorCuotas, $creadorCronograma, $eliminadorCronograma) {
                // 1. Actualizar el préstamo con los nuevos datos
                $prestamo->update($validatedData);

                // 2. Eliminar las cuotas anteriores
                $prestamo->cuota()->delete();

                // 3. Generar las nuevas cuotas con los datos actualizados
                $creadorCuotas->generarCuotas($prestamo);
                
                // 4. Eliminar el directorio de cronogramas antiguos
                $eliminadorCronograma->execute($prestamo->id_Cliente, $prestamo->id);

                // 5. Generar un nuevo cronograma en PDF (que creará el directorio de nuevo)
                $creadorCronograma->generar($prestamo);
            });
            
            return response()->json([
                'type' => 'success',
                'message' => 'Préstamo actualizado con éxito.',
                'data' => $prestamo->fresh()->load('cuota'),
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Acción no permitida.',
                'details' => 'Este préstamo no puede ser editado porque no fue creado hoy.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error al actualizar el préstamo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

       /**
     * Elimina un préstamo y sus archivos asociados si fue creado el mismo día.
     *
     * @param \App\Models\Prestamo $prestamo
     * @param \App\Http\Controllers\Prestamo\utilities\EliminarCronograma $eliminador
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Prestamo $prestamo, EliminarCronograma $eliminador)
    {
        try {
            // 1. Verificar la condición de la fecha.
            if (!Carbon::parse($prestamo->fecha_generacion)->isToday()) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Acción no permitida.',
                    'details' => 'Este préstamo no puede ser eliminado porque no fue creado hoy.'
                ], 403);
            }

            // 2. Llamar al servicio para eliminar la carpeta de cronogramas.
            $eliminador->execute($prestamo->id_Cliente, $prestamo->id);

            // 3. Eliminar el préstamo de la base de datos.
            $prestamo->delete();

            // 4. Devolver una respuesta de éxito.
            return response()->json([
                'type' => 'success',
                'message' => 'El préstamo y sus archivos asociados han sido eliminados con éxito.',
            ]);

        } catch (\Exception $e) {
            // Log::error('Error al eliminar el préstamo: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error inesperado al intentar eliminar el préstamo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reprograma un préstamo existente, liquidando el anterior y creando uno nuevo.
     */
    public function reprogramar(ReprogramarPrestamoRequest $request, ProcesarReprogramacion $procesador)
    {
        try {
            $validatedData = $request->validated();
            $prestamoOriginal = Prestamo::findOrFail($validatedData['prestamo_id']);

            // Delegar toda la lógica al servicio
            $nuevoPrestamo = $procesador->execute($prestamoOriginal, $validatedData);

            return response()->json([
                'message' => 'Préstamo reprogramado con éxito. Se ha generado un nuevo préstamo con el ID: ' . $nuevoPrestamo->id,
                'data' => $nuevoPrestamo,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al reprogramar el préstamo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
