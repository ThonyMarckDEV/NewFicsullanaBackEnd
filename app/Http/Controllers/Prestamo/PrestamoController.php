<?php

namespace App\Http\Controllers\Prestamo;

use App\Http\Controllers\Prestamo\utilities\ProcesarDatosPrestamo;
use App\Http\Requests\StorePrestamoRequest;
use App\Models\Prestamo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
     * Display the specified resource.
     */
    public function show(Prestamo $prestamo)
    {
        //
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
     * Remove the specified resource from storage.
     */
    public function destroy(Prestamo $prestamo)
    {
        //
    }
}
