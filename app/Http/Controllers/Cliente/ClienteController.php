<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Cliente\utilities\ProcesarDatosCliente;
use App\Models\Cliente;
usE App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
   public function store(StoreClienteRequest $request, ProcesarDatosCliente $procesador): JsonResponse
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
     * Muestra toda la información de un cliente específico, sea buscado por ID o DNI.
     */
    public function show(User $cliente , ProcesarDatosCliente $procesador): JsonResponse
    {
        try {
            // A. VALIDACIÓN DE ROL (DEBE hacerse ANTES de cargar toda la data)
            // Solo cargamos la relación 'rol' para esta validación rápida.
            $cliente->load('rol');
            
            if (!$cliente->rol || $cliente->rol->id !== 2) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'El usuario no es un cliente.',
                ], 403);
            }

            // 1. Delegar la carga y formateo de datos a la clase de utilidad.
            $clienteProcesado = $procesador->obtenerInformacionCliente($cliente);

            // 2. Respuesta final exitosa.
            return response()->json([
                'type'    => 'success',
                'message' => 'Cliente encontrado.',
                'data'    => $clienteProcesado
            ]);

        } catch (Exception $e) {
            // Manejo de error si el modelo User no fue encontrado o hay un error interno.
            return response()->json([
                'type'    => 'error',
                'message' => 'Cliente no encontrado.'
            ], 404);
        }
    }

   
    
    /**
     * Actualiza un cliente existente en la base de datos.
     */
    public function update(UpdateClienteRequest $request, User $cliente, ProcesarDatosCliente $procesador): JsonResponse
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


    public function toggleEstado(Request $request, User $cliente): JsonResponse
    {
        // 1. Validar el estado que se recibe (0 o 1)
        $request->validate([
            'estado' => 'required|boolean',
        ]);

        try {
            // 2. Actualizar el estado del cliente (el modelo User)
            $cliente->estado = $request->estado;
            $cliente->save();
            
            // Determinar el mensaje de éxito
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
