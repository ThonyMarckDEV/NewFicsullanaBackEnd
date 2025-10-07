<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Empleado\utilities\StoreEmpleado;
use App\Http\Controllers\Empleado\utilities\UpdateEmpleado;
use App\Http\Requests\EmpleadoRequest\StoreEmpleadoRequest;
use App\Http\Requests\EmpleadoRequest\UpdateEmpleadoRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    /**
     * Obtiene la lista de empleados con paginación y filtros.
     */
    public function index(Request $request)
    {
        $query = User::with('datos')
            ->whereIn('id_Rol', [3, 4]) // Solo Asesor (3) y Cajero (4)
            ->orderBy('id', 'desc');

        // Filtro por Rol
        if ($request->filled('rol') && in_array($request->rol, [3, 4])) {
            $query->where('id_Rol', $request->rol);
        }

        // Filtro de Búsqueda (DNI, Nombre, Apellidos)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('datos', function ($q) use ($searchTerm) {
                $q->where('dni', 'like', $searchTerm)
                    ->orWhere('nombre', 'like', $searchTerm)
                    ->orWhere('apellidoPaterno', 'like', $searchTerm)
                    ->orWhere('apellidoMaterno', 'like', $searchTerm)
                    ->orWhere(DB::raw("CONCAT(nombre, ' ', apellidoPaterno, ' ', apellidoMaterno)"), 'like', $searchTerm);
            });
        }

        // Paginación por 10 ítems
        $empleados = $query->paginate(10);

        return response()->json($empleados);
    }

    /**
     * Almacena un nuevo empleado utilizando una clase de acción dedicada.
     */
    public function store(StoreEmpleadoRequest $request, StoreEmpleado $action)
    {
        try {
            $validatedData = $request->validated();

            // Delegamos la creación y la transacción a la clase de acción
            $nuevoEmpleado = $action->execute($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Empleado registrado exitosamente.',
                'data' => $nuevoEmpleado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al registrar el empleado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los datos de un empleado para la edición.
     */
    public function show(User $empleado)
    {
        // Verificar que el usuario sea realmente un empleado
        if (!in_array($empleado->id_Rol, [3, 4])) { // rol 3 asesor, rol 4 cajero
            return response()->json(['message' => 'Usuario no es un empleado válido.'], 404);
        }

        // Cargar los datos relacionados
        $empleado->load('datos');

        return response()->json([
            'type'    => 'success',
            'message' => 'Empleado encontrado.',
            'data'    => $empleado
        ]);
    }

    /**
     * Actualiza los datos de un empleado utilizando una clase de acción dedicada.
     */
    public function update(UpdateEmpleadoRequest $request, User $empleado, UpdateEmpleado $action)
    {
        try {
            $validatedData = $request->validated();

            // Delegamos la actualización y la transacción a la clase de acción
            $empleadoActualizado = $action->execute($empleado, $validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Empleado actualizado exitosamente.',
                'data' => $empleadoActualizado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar el empleado.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado (Activo/Inactivo) de un empleado.
     */
    public function toggleEstado(User $empleado)
    {
        // Asegurarse de no desactivar a un administrador (ID 1)
        if ($empleado->id_Rol === 1) {
            return response()->json([
                'type' => 'error',
                'message' => 'No se puede cambiar el estado de un administrador.',
            ], 403);
        }

        $nuevoEstado = $empleado->estado === 1 ? 0 : 1;
        $empleado->estado = $nuevoEstado;
        $empleado->save();

        $estadoTexto = $nuevoEstado === 1 ? 'activado' : 'desactivado';

        return response()->json([
            'type' => 'success',
            'message' => "Empleado ha sido {$estadoTexto} exitosamente.",
        ]);
    }
}
