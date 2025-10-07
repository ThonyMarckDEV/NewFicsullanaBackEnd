<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Cliente\utilities\StoreCliente;
use App\Http\Controllers\Cliente\utilities\UpdateCliente;
use App\Http\Controllers\Cliente\services\ObtenerInformacionClienteService;
use App\Http\Requests\ClienteRequest\StoreClienteRequest;
use App\Http\Requests\ClienteRequest\UpdateClienteRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de clientes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $clientes = User::where('id_Rol', 2)
                ->with(['datos' => function ($query) {
                    $query->select('id', 'nombre', 'apellidoPaterno', 'apellidoMaterno', 'dni');
                }])
                ->select('id', 'id_Datos', 'estado', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json($clientes);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al obtener los clientes.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo cliente utilizando una clase de acción dedicada.
     */
    public function store(StoreClienteRequest $request, StoreCliente $action): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Usamos la clase de acción para procesar y guardar los datos
            $cliente = $action->execute($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Cliente registrado exitosamente.',
                'data' => $cliente
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al registrar el cliente.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra toda la información de un cliente específico.
     */
    public function show(User $cliente, ObtenerInformacionClienteService $service): JsonResponse
    {
        try {
            // A. VALIDACIÓN DE ROL
            $cliente->load('rol');
            
            if (!$cliente->rol || $cliente->rol->id !== 2) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'El usuario no es un cliente.',
                ], 403);
            }

            // B. DELEGAR LA LÓGICA AL SERVICIO
            $clienteProcesado = $service->execute($cliente);

            return response()->json([
                'type'    => 'success',
                'message' => 'Cliente encontrado.',
                'data'    => $clienteProcesado
            ]);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Cliente no encontrado o ha ocurrido un error.',
                'details' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Actualiza un cliente existente en la base de datos.
     */
    public function update(UpdateClienteRequest $request, User $cliente, UpdateCliente $action): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $clienteActualizado = $action->execute($cliente, $validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Cliente actualizado exitosamente.',
                'data' => $clienteActualizado
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar el cliente.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado de un cliente.
     */
    public function toggleEstado(Request $request, User $cliente): JsonResponse
    {
        $request->validate(['estado' => 'required|boolean']);

        try {
            $cliente->estado = $request->estado;
            $cliente->save();
            
            $mensaje = $request->estado == 1 ? 'Cliente activado exitosamente.' : 'Cliente inactivado exitosamente.';

            return response()->json([
                'type' => 'success',
                'message' => $mensaje,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al cambiar el estado del cliente.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

