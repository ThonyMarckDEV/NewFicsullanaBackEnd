<?php

namespace App\Http\Requests\ProductoRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:productos,nombre'],
            // Se asume que rango_tasa es una cadena que describe el rango (ej: "10%-15%").
            'rango_tasa' => ['required', 'string', 'max:50'], 
        ];
    }
    
    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con este nombre.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'rango_tasa.required' => 'El rango de tasa es obligatorio.',
        ];
    }
}