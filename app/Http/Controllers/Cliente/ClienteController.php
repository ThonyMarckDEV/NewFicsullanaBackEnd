<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Cliente\utilities\ProcesarDatosCliente;
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
     * Muestra toda la información de un cliente específico, buscando por ID o DNI.
     */
    public function show($identifier): JsonResponse
    {
        // ID del rol de Cliente
        $CLIENTE_ROL_ID = 2;
        
        try {
            // Limpiamos el identificador por si se pasan espacios.
            $identifier = trim($identifier);

            // 1. Determinar el tipo de búsqueda
            $isDniOrRuc = is_numeric($identifier) && strlen($identifier) >= 8;

            // 2. Buscar el cliente (aplicando filtro de rol)
            if ($isDniOrRuc) {
                // Buscamos en la tabla 'datos' por la columna 'dni' Y aseguramos que el usuario sea rol 2
                $cliente = User::where('id_Rol', $CLIENTE_ROL_ID) // <--- FILTRO AGREGADO
                    ->whereHas('datos', function ($query) use ($identifier) {
                        $query->where('dni', $identifier); 
                    })
                    ->firstOrFail();

            } else {
                // Si tiene menos de 8 dígitos o no es numérico, lo tratamos como el ID del usuario.
                // Buscamos por ID Y aseguramos que el usuario sea rol 2
                $cliente = User::where('id_Rol', $CLIENTE_ROL_ID) // <--- FILTRO AGREGADO
                                ->findOrFail($identifier);
            }

            // 3. Cargamos las relaciones
            $cliente->load([
                'datos.direcciones',
                'datos.contactos',
                'datos.empleos',
                'datos.cuentasBancarias',
                'avales'
            ]);

            // 4. Construimos la estructura plana para el frontend.
            $clienteProcesado = [
                'id' => $cliente->id,
                'username' => $cliente->username,
                'estado' => $cliente->estado,
                'datos' => $cliente->datos->getAttributes(),
                // Usamos ->first() para aplanar las colecciones de "one-to-one"
                'direcciones' => $cliente->datos->direcciones->first() ?? null,
                'contactos' => $cliente->datos->contactos->first() ?? null,
                'empleos' => $cliente->datos->empleos->first() ?? null,
                'cuentas_bancarias' => $cliente->datos->cuentasBancarias->first() ?? null,
                'avales' => $cliente->avales, 
            ];
            
            // 5. Devolvemos la respuesta
            return response()->json([
                'type'    => 'success',
                'message' => 'Cliente encontrado.',
                'data'    => $clienteProcesado 
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Devuelve 404 si el cliente no se encuentra O si se encuentra pero NO es rol 2
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        } catch (Exception $e) {
            // \Log::error("Error al buscar cliente: " . $e->getMessage()); 
            return response()->json(['message' => 'Ocurrió un error en el servidor.'], 500);
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
