<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Cliente\utilities\ProcesarDatos;
use App\Models\Cliente;
usE App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Event\Exception;

class ClienteController extends Controller
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
     * Store a newly created resource in storage.
     */
   public function store(StoreClienteRequest $request, ProcesarDatos $procesador): JsonResponse
    {
        try {
            // La validación ya se ejecutó gracias a StoreClienteRequest
            $validatedData = $request->validated();
            
            // Usamos la clase de utilidad para procesar y guardar los datos
            $cliente = $procesador->crearNuevoCliente($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Cliente registrado exitosamente.',
                'data' => $cliente
            ], 201);

        } catch (Exception $e) {
            // Si algo falla en la transacción, capturamos el error
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al registrar el cliente.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
