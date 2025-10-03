<?php

namespace App\Http\Controllers\Empleado;

use App\Models\Empleado;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoRequest;
use App\Models\Datos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
   // Obtiene la lista de empleados con paginación y filtros
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
        
        // Retornar solo los datos del empleado que React necesita
        return response()->json($empleados);
    }
    
    // Almacena un nuevo empleado
    public function store(StoreEmpleadoRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. Crear el registro de Datos
            $datos = Datos::create($request->validated());

            // 2. Crear el registro de Usuario (Empleado)
            User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'id_Datos' => $datos->id,
                'id_Rol' => $request->id_Rol,
                'estado' => 1, // Por defecto activo
            ]);

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Empleado registrado exitosamente.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al registrar el empleado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    
    // Obtiene los datos de un empleado para la edición
    public function show(User $empleado)
    {
        // Verificar que el usuario sea realmente un empleado
        if (!in_array($empleado->id_Rol, [3, 4])) { //rol 3 asesor rol 4 cajero
             return response()->json(['message' => 'Usuario no es un empleado válido.'], 404);
        }
        
        // Usamos el 'with' para cargar los datos relacionados
        $empleado->load('datos');

        return response()->json([
            'type'    => 'success',
            'message' => 'Empleado encontrado.',
            'data'    => $empleado 
        ]);
    }

    // Actualiza los datos de un empleado
    public function update(UpdateEmpleadoRequest $request, User $empleado)
    {
        DB::beginTransaction();
        try {
            // 1. Actualizar Datos Personales
            $empleado->datos->update($request->validated());

            // 2. Actualizar Rol
            $empleado->update([
                'id_Rol' => $request->id_Rol,
            ]);

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Datos del empleado actualizados correctamente.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar los datos.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Cambia el estado (Activo/Inactivo) de un empleado
    public function toggleEstado(User $empleado)
    {
        // Asegúrate de que no estás intentando desactivar a un administrador (ID 1)
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
