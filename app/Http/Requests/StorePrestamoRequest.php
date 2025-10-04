<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Prestamo; // Asegúrate de importar el modelo Prestamo

class StorePrestamoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_Cliente' => ['required', 'integer', 'exists:usuarios,id'],
            'id_Asesor' => ['required', 'integer', 'exists:usuarios,id'],
            'id_Producto' => ['required', 'integer', 'exists:productos,id'],
            'monto' => ['required', 'numeric', 'min:100'],
            'interes' => ['required', 'numeric', 'min:0', 'max:1'], 
            'cuotas' => ['required', 'integer', 'min:1'],
            'total' => ['required', 'numeric', 'min:0'],
            'valor_cuota' => ['required', 'numeric', 'min:0'],
            
            'frecuencia' => ['required', 'string', Rule::in(['SEMANAL', 'CATORCENAL', 'MENSUAL'])],
            'modalidad' => ['required', 'string', Rule::in(['NUEVO', 'RCS', 'RSS'])],
            'abonado_por' => ['required', 'string', Rule::in(['CUENTA CORRIENTE', 'CAJA CHICA'])],
        ];
    }

    /**
     * Configura el validador con lógica de negocio personalizada.
     * Esta función verifica si el cliente ya tiene un préstamo vigente (estado 1).
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            
            // Solo procede si la validación de 'id_Cliente' ya pasó
            if ($this->has('id_Cliente') && !$validator->errors()->has('id_Cliente')) {
                
                $clienteId = $this->input('id_Cliente');

                // 1. Buscar préstamos del cliente que estén en estado VIGENTE (estado = 1)
                $prestamoVigente = Prestamo::where('id_Cliente', $clienteId)
                                            ->where('estado', 1) // Estado 1 = Vigente
                                            ->exists();

                // 2. Si se encuentra un préstamo vigente, añade el error
                if ($prestamoVigente) {
                    $validator->errors()->add(
                        'id_Cliente', 
                        'El cliente ya tiene un préstamo VIGENTE activo.'
                    );
                }
            }
        });
    }

    /**
     * Define mensajes de error personalizados (opcional).
     */
    public function messages(): array
    {
        return [
            'id_Cliente.exists' => 'El cliente seleccionado no existe.',
            'id_Cliente.required' => 'El ID del cliente es obligatorio.',
            // El mensaje de préstamo vigente ya se añade en withValidator.
        ];
    }
}