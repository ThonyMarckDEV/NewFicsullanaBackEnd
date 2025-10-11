<?php

namespace App\Http\Controllers\Prestamo;

use App\Http\Controllers\Prestamo\services\AdjuntarCapturaPagoUrl;
use App\Http\Controllers\Prestamo\services\AdjuntarComprobanteUrl;
use App\Http\Controllers\Prestamo\services\AdjuntarCronogramaUrl;
use App\Http\Controllers\Prestamo\services\CalcularMora;
use App\Http\Controllers\Prestamo\services\CrearCronograma;
use App\Http\Controllers\Prestamo\services\CrearCuotasPrestamo;
use App\Http\Controllers\Prestamo\services\EliminarCronograma;
use App\Http\Controllers\Prestamo\services\ReducirMora;
use App\Http\Controllers\Prestamo\services\VerificarCuotasPagadas;

use App\Http\Controllers\Prestamo\utilities\ProcesarDatosPrestamo;
use App\Http\Controllers\Prestamo\utilities\ProcesarReprogramacion;

use App\Http\Requests\PrestamoRequest\ReprogramarPrestamoRequest;
use App\Http\Requests\PrestamoRequest\StorePrestamoRequest;
use App\Http\Requests\PrestamoRequest\UpdatePrestamoRequest;

use App\Models\Cuota;
use App\Models\Prestamo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrestamoController extends Controller
{

 /**
     * Aplica una reducción porcentual a la mora de una cuota.
     *
     * @param Request $request
     * @param Cuota $cuota
     * @param ReducirMora $reductorDeMora El servicio para aplicar la reducción.
     * @return \Illuminate\Http\JsonResponse
     */
    public function reducirMora(Request $request, Cuota $cuota, ReducirMora $reductorDeMora)
    {
        // 1. Validar la entrada HTTP
        $validated = $request->validate([
            'porcentaje_reduccion' => 'required|numeric|min:1|max:100',
        ]);

        try {
            // 2. Envolver la lógica en una transacción de base de datos
            $cuotaActualizada = DB::transaction(function () use ($reductorDeMora, $cuota, $validated) {
                // 3. Delegar toda la lógica de negocio al servicio
                return $reductorDeMora->execute($cuota, $validated['porcentaje_reduccion']);
            });

            // 4. Devolver una respuesta de éxito
            return response()->json([
                'type' => 'success',
                'message' => 'Reducción de mora aplicada con éxito.',
                'data' => $cuotaActualizada,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Datos de entrada inválidos.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Captura cualquier excepción lanzada por el servicio
            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
            ], 422); // 422 Unprocessable Entity es apropiado para errores de lógica de negocio
        }
    }


    /**
     * Muestra una lista paginada de préstamos, con búsqueda y ordenamiento.
     */
    public function index(
        Request $request,
        VerificarCuotasPagadas $verificadorCuotasPagadas,
        AdjuntarCronogramaUrl $adjuntadorUrl,
        CalcularMora $calculadorMora
    ) {
        // 1. Validar la entrada (sin cambios)
        $validated = $request->validate([
            'search' => 'nullable|string|max:50',
            'sort_by' => 'nullable|string|in:id,monto,frecuencia,fecha_generacion,estado',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $searchQuery = $validated['search'] ?? null;
        $user = $request->user();
        
        // Carga las relaciones completas para que los servicios funcionen
        $prestamosQuery = Prestamo::with(['cliente.datos', 'cuota']);

        if ($user->id_Rol === 2) {
            $prestamosQuery->where('id_Cliente', $user->id);
        }

        $prestamosQuery->when($searchQuery, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('cliente.datos', function ($subQ) use ($search) {
                        $subQ->where('dni', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%")
                            ->orWhere('apellidoPaterno', 'like', "%{$search}%");
                    });
            });
        });

        $prestamos = $prestamosQuery->orderBy('id', 'desc')->paginate(10);

        // Usa los servicios para transformar la colección
        $prestamos->getCollection()->transform(function ($prestamo) use ($verificadorCuotasPagadas, $adjuntadorUrl, $calculadorMora) {
            
            // a. Ejecuta los servicios que podrían actualizar la BD
            if ($prestamo->cuota) {
                foreach ($prestamo->cuota as $item_cuota) {
                    $calculadorMora->execute($item_cuota);
                }
            }
            
            // b. Recarga el modelo para tener la versión más actualizada desde la BD
            // Esto asegura que cualquier cambio hecho por 'calculadorMora' se refleje.
            $prestamo->refresh();

            // c. Ahora, con el modelo actualizado, ejecuta los servicios que añaden datos para la respuesta
            $verificadorCuotasPagadas->execute($prestamo); // Añade 'tiene_cuotas_pagadas'
            $adjuntadorUrl->execute($prestamo);             // Añade 'cronograma_url'

            // d. Construye y devuelve el array final usando el único objeto $prestamo, que ahora tiene todo
            return [
                'id' => $prestamo->id,
                'monto' => $prestamo->monto,
                'frecuencia' => $prestamo->frecuencia,
                'fecha_generacion' => $prestamo->fecha_generacion,
                'estado' => $prestamo->estado,
                'interes' => $prestamo->interes,
                'total' => $prestamo->total,
                'cronograma_url' => $prestamo->cronograma_url,
                'tiene_cuotas_pagadas' => $prestamo->tiene_cuotas_pagadas,
                'cliente' => [
                    'datos' => [
                        'nombre' => optional($prestamo->cliente->datos)->nombre,
                        'apellidoPaterno' => optional($prestamo->cliente->datos)->apellidoPaterno,
                        'apellidoMaterno' => optional($prestamo->cliente->datos)->apellidoMaterno,
                        'dni' => optional($prestamo->cliente->datos)->dni,
                    ]
                ]
            ];
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
    public function show(Prestamo $prestamo, AdjuntarComprobanteUrl $adjuntadorcomprobante, AdjuntarCapturaPagoUrl $adjuntadorcapturapago)
    {
        // 1. Carga las relaciones que necesitas (asesor y sus datos)
        $prestamo->load(['cliente.datos', 'producto', 'cuota', 'asesor.datos']);

        // 2. Ejecuta tus servicios para adjuntar las URLs
        $adjuntadorcomprobante->execute($prestamo->cuota, $prestamo);
        $adjuntadorcapturapago->execute($prestamo->cuota, $prestamo);

        // 3. Convierte el modelo y sus relaciones a un array para poder manipularlo
        $data = $prestamo->toArray();

        // 4. Reconstruye el array del asesor con la estructura que quieres
        if (isset($data['asesor']) && isset($data['asesor']['datos'])) {
            $asesorDatos = $data['asesor']['datos'];
            $data['asesor'] = [
                'id'     => $data['asesor']['id'],
                // Crea el array 'datos' anidado
                'datos'  => [
                    'dni'             => $asesorDatos['dni'],
                    'nombre'          => $asesorDatos['nombre'],
                    'apellidoPaterno' => $asesorDatos['apellidoPaterno'],
                    'apellidoMaterno' => $asesorDatos['apellidoMaterno'],
                ]
            ];
        }

        // 5. Devuelve el array modificado como JSON
        return response()->json($data);
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
