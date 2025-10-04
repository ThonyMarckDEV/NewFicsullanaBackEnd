<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrestamoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Asumiendo que un usuario autenticado (e.g., el Asesor) puede crear un préstamo.
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id_Cliente' => ['required', 'integer', 'exists:usuarios,id'],
            'id_Asesor' => ['required', 'integer', 'exists:usuarios,id'],
            'id_Producto' => ['required', 'integer', 'exists:productos,id'],
            'monto' => ['required', 'numeric', 'min:100'],
            'interes' => ['required', 'numeric', 'min:0', 'max:1'], // Interés como decimal (e.g., 0.18)
            'cuotas' => ['required', 'integer', 'min:1'],
            'total' => ['required', 'numeric', 'min:0'],
            'valor_cuota' => ['required', 'numeric', 'min:0'],
            
            'frecuencia' => ['required', 'string', Rule::in(['SEMANAL', 'CATORCENAL', 'MENSUAL'])],
            'modalidad' => ['required', 'string', Rule::in(['NUEVO', 'RCS', 'RSS'])],
            'abonado_por' => ['required', 'string', Rule::in(['CUENTA CORRIENTE', 'CAJA CHICA'])],
        ];
    }
}