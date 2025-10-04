<?php

namespace App\Http\Controllers\Pago;

use App\Models\Pago;
use App\Http\Controllers\Controller
use Illuminate\Http\Request;

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

    public function store(StorePagoRequest $request)
    {
        $validatedData = $request->validated();
        $cuota = Cuota::findOrFail($validatedData['id_Cuota']);

        if ($cuota->estado == 2) {
            return response()->json(['message' => 'Esta cuota ya ha sido pagada.'], 409);
        }

        try {
            DB::transaction(function () use ($validatedData, $cuota) {
                // 1. Crear el registro del pago
                Pago::create([
                    'id_Cuota' => $cuota->id,
                    'monto_pagado' => $validatedData['monto_pagado'],
                    'fecha_pago' => $validatedData['fecha_pago'],
                    'modalidad' => $validatedData['modalidad'],
                    'numero_operacion' => $validatedData['numero_operacion'] ?? null,
                    'observaciones' => $validatedData['observaciones'] ?? null,
                    'id_Usuario' => Auth::id(), // ID del cajero autenticado
                ]);

                // 2. Actualizar el estado de la cuota
                $cuota->estado = 2; // 2 = Pagado
                $cuota->save();

                // 3. Verificar si el préstamo está completo
                $prestamo = $cuota->prestamo;
                if ($prestamo->cuota()->where('estado', '!=', 2)->count() === 0) {
                    $prestamo->estado = 2; // 2 = Pagado
                    $prestamo->save();
                }
            });

            return response()->json(['message' => 'Pago registrado con éxito.'], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar el pago.',
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
