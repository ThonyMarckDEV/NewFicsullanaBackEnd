<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            // Las reglas son casi las mismas, pero ajustamos las de 'unique'
            'datos.dni' => ['required', 'string', 'digits_between:8,9', Rule::unique('datos', 'dni')->ignore($clienteId)],
            'datos.ruc' => ['nullable', 'string', 'digits:11', Rule::unique('datos', 'ruc')->ignore($clienteId)],
            // ... copia aquí el resto de las reglas de tu StoreClienteRequest
            // ya que son las mismas (nombre, apellidos, direcciones, etc.)
            'datos.nombre' => 'required|string|max:100',
            'datos.apellidoPaterno' => 'required|string|max:100',
            // ...etc.
        ];
    }
}