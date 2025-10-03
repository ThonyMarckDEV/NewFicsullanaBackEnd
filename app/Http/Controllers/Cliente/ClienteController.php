<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Cliente\utilities\ProcesarDatos;
use App\Models\Cliente;
usE App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Event\Exception;

class ClienteController extends Controller
{
     /**
     * Muestra una lista paginada de clientes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Buscamos usuarios que tengan el rol de cliente (asumiendo id_Rol = 2)
            // y cargamos sus datos personales para evitar consultas N+1.
            $clientes = User::where('id_Rol', 2)
                ->with(['datos' => function ($query) {
                    // Seleccionamos solo los campos necesarios de la tabla 'datos'
                    $query->select('id', 'nombre', 'apellidoPaterno', 'apellidoMaterno', 'dni');
                }])
                ->select('id', 'id_Datos', 'estado', 'created_at') // Seleccionamos campos de 'usuarios'
                ->orderBy('created_at', 'desc') // Ordenamos por los más recientes
                ->paginate(10); // Laravel se encarga de la paginación automáticamente

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
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        //
    }

    /**
     * Muestra toda la información de un cliente específico.
     */
    public function show(User $cliente): JsonResponse
    {
        try {
            // 1. Cargamos las relaciones como antes.
            $cliente->load([
                'datos.direcciones',
                'datos.contactos',
                'datos.empleos',
                'datos.cuentasBancarias',
                'avales'
            ]);

            // 2. Construimos la estructura plana que necesita el frontend.
            $clienteProcesado = [
                'id' => $cliente->id,
                'username' => $cliente->username,
                'estado' => $cliente->estado,
                'datos' => $cliente->datos->getAttributes(),
                'direcciones' => $cliente->datos->direcciones,
                'contactos' => $cliente->datos->contactos,
                'empleos' => $cliente->datos->empleos,
                'cuentas_bancarias' => $cliente->datos->cuentasBancarias,
                'avales' => $cliente->avales,
            ];

            // ===============================================================
            // === CAMBIO CLAVE AQUÍ ===
            // Envolvemos la respuesta en un objeto con la propiedad 'data'.
            // ===============================================================
            return response()->json([
                'type'    => 'success',
                'message' => 'Cliente encontrado.',
                'data'    => $clienteProcesado // <-- AQUÍ ESTÁ LA MAGIA
            ]);

        } catch (Exception $e) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }
    }

    /**
     * Actualiza un cliente existente en la base de datos.
     */
    public function update(UpdateClienteRequest $request, User $cliente, ProcesarDatos $procesador): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Llama a un nuevo método en tu clase de utilidad para actualizar
            // (lo crearemos en el siguiente paso)
            $clienteActualizado = $procesador->actualizarCliente($cliente, $validatedData);

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
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
