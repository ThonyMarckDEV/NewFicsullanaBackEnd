<?php

namespace App\Http\Controllers\Pago;

use App\Http\Controllers\Pago\utilities\ProcesarCancelacionTotal;
use App\Http\Controllers\Pago\utilities\ProcesarDatosPago;
use App\Http\Requests\StorePagoRequest;
use App\Models\Cuota;
use App\Models\Pago;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
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
     * Almacena un nuevo pago utilizando el servicio ProcesarDatosPago.
     *
     * @param StorePagoRequest $request
     * @param ProcesarDatosPago $procesador
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePagoRequest $request, ProcesarDatosPago $procesador)
    {
        try {
            $validatedData = $request->validated();
            $cuota = Cuota::findOrFail($validatedData['id_Cuota']);

            if ($cuota->estado == 2) {
                return response()->json(['message' => 'Esta cuota ya ha sido pagada.'], 409); // 409 Conflict
            }

            // 2. Delegar toda la lógica de negocio al servicio
            $procesador->execute($validatedData, $cuota);

            return response()->json(['message' => 'Pago registrado con éxito.'], 201);

        } catch (\Exception $e) {
            // Log::error('Error al registrar pago: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar el pago.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

     /**
     * Procesa la cancelación total de un préstamo.
     */
    public function cancelarTotal(StorePagoRequest $request, ProcesarCancelacionTotal $procesador)
    {
        try {
            $validatedData = $request->validated();
            // Para la cancelación, el 'id_Cuota' representa la ÚLTIMA cuota pendiente
            $ultimaCuota = Cuota::findOrFail($validatedData['id_Cuota']);
            $prestamo = $ultimaCuota->prestamo;

            // Delegar la lógica de cancelación al servicio
            $procesador->execute($validatedData, $prestamo);

            return response()->json(['message' => 'Préstamo cancelado con éxito.'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar el préstamo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        //
    }
}
