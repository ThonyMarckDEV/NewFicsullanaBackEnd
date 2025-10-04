<?php

namespace App\Http\Controllers\Prestamo;

use App\Http\Controllers\Prestamo\utilities\EliminarCronograma;
use App\Http\Controllers\Prestamo\utilities\ProcesarDatosPrestamo;
use App\Http\Requests\StorePrestamoRequest;
use App\Models\Prestamo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrestamoController extends Controller
{
    /**
     * Muestra una lista paginada de préstamos, con opción de búsqueda.
     */
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:20']);
        $searchQuery = $request->input('search');

        // ===== INICIO DE LA CORRECCIÓN =====
        // La forma correcta de cargar relaciones anidadas es usando la notación de punto.
        $prestamos = Prestamo::with(['cliente.datos', 'asesor.datos'])
        // ===== FIN DE LA CORRECCIÓN =====
            ->when($searchQuery, function ($query, $search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhereHas('cliente.datos', function ($q) use ($search) {
                          $q->where('dni', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%")
                            ->orWhere('apellidoPaterno', 'like', "%{$search}%");
                      });
            })
            ->latest('id')
            ->paginate(10);

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
     * Muestra los detalles completos de un préstamo específico.
     */
    public function show(Prestamo $prestamo)
    {
        // Carga todas las relaciones anidadas necesarias para el modal de detalles
        // La notación de punto es clave: 'cliente.datos', 'asesor.datos'
        $prestamo->load(['cliente.datos', 'asesor.datos', 'producto', 'cuota']);

        return response()->json($prestamo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prestamo $prestamo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prestamo $prestamo)
    {
        //
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
}
