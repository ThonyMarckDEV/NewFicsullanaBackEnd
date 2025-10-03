<?php

namespace App\Http\Controllers\Producto;

use App\Models\Producto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
  /**
     * Muestra una lista paginada de productos.
     */
    public function index(): JsonResponse
    {
        try {
            // Paginamos 10 productos por página
            $productos = Producto::orderBy('created_at', 'desc')->paginate(10); 
            
            // Laravel automáticamente incluye metadatos de paginación
            return response()->json($productos); 

        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error al obtener los productos.',
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
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            Producto::create($request->validated());
            
            return response()->json([
                'type' => 'success',
                'message' => 'Producto creado con éxito.'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error al crear el producto.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): JsonResponse
    {
        try {
            $producto->update($request->validated());
            
            return response()->json([
                'type' => 'success',
                'message' => 'Producto actualizado con éxito.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error al actualizar el producto.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        //
    }
}
