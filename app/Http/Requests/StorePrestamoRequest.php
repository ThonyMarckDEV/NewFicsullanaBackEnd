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
     * Define mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            // === Mensajes para IDs ===
            'id_Cliente.required' => 'Debe seleccionar un cliente.',
            'id_Cliente.integer' => 'El cliente seleccionado no es válido.',
            'id_Cliente.exists' => 'El cliente seleccionado no existe en la base de datos.',

            'id_Asesor.required' => 'Debe seleccionar un asesor.',
            'id_Asesor.integer' => 'El asesor seleccionado no es válido.',
            'id_Asesor.exists' => 'El asesor seleccionado no existe en la base de datos.',

            'id_Producto.required' => 'Debe seleccionar un producto.',
            'id_Producto.integer' => 'El producto seleccionado no es válido.',
            'id_Producto.exists' => 'El producto seleccionado no existe.',

            // === Mensajes para Datos del Préstamo ===
            'monto.required' => 'El monto del préstamo es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto mínimo del préstamo es de S/ :min.',

            'interes.required' => 'La tasa de interés es obligatoria.',
            'interes.numeric' => 'La tasa de interés debe ser un valor numérico.',
            'interes.min' => 'La tasa de interés no puede ser negativa.',
            'interes.max' => 'La tasa de interés debe ser un valor decimal entre 0 y 1 (ej: 0.2 para 20%).',

            'cuotas.required' => 'El número de cuotas es obligatorio.',
            'cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'cuotas.min' => 'El préstamo debe tener al menos :min cuota.',

            'total.required' => 'El campo total es requerido para el registro.',
            'valor_cuota.required' => 'El valor de la cuota es requerido para el registro.',

            // === Mensajes para Selects de Texto ===
            'frecuencia.required' => 'Debe seleccionar una frecuencia de pago.',
            'frecuencia.in' => 'La frecuencia de pago seleccionada no es válida.',

            'modalidad.required' => 'Debe seleccionar una modalidad para el préstamo.',
            'modalidad.in' => 'La modalidad seleccionada no es válida.',
            
            'abonado_por.required' => 'Debe especificar de dónde se abona el dinero.',
            'abonado_por.in' => 'El origen del abono seleccionado no es válido.',
        ];
    }
}