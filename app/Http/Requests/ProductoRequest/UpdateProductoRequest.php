<?php

namespace App\Http\Requests\ProductoRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{

    public function rules(): array
    {
        // Se obtiene el ID del producto de la URL para ignorarlo en la regla unique
        $productId = $this->route('producto'); 
        
        return [
            'nombre' => ['required', 'string', 'max:255', Rule::unique('productos', 'nombre')->ignore($productId)],
            'rango_tasa' => ['required', 'string', 'max:50'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe otro producto con este nombre.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'rango_tasa.required' => 'El rango de tasa es obligatorio.',
        ];
    }
}