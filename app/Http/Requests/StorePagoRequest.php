<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        // 1. Reglas base para todos los pagos
        $rules = [
            'id_Cuota' => ['required', 'integer', 'exists:cuotas,id'],
            'monto_pagado' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date_format:Y-m-d'],
            'modalidad' => ['required', 'string', Rule::in(['PRESENCIAL', 'VIRTUAL'])],
            'numero_operacion' => ['nullable', 'string', 'max:255'],
            // 'observaciones' ya es nullable, no requiere cambio aquí.
        ];

        // 2. Reglas condicionales: Si la modalidad es VIRTUAL, el comprobante es obligatorio.
        if ($this->get('modalidad') === 'VIRTUAL') {
            $rules['comprobante'] = [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf', // Tipos de archivo permitidos
                'max:5120', // Tamaño máximo: 5MB (ajusta según tus necesidades)
            ];
            // Asegúrate de que 'metodo_pago' (usado en ProcesarDatosPago) también esté validado si es necesario
            $rules['metodo_pago'] = ['required', 'string', 'max:50'];
        }
        
        // 3. Reglas condicionales: Si la modalidad es PRESENCIAL, 'observaciones' puede ser enviada.
        // Si no se envía ninguna regla condicional para 'observaciones', se mantiene 'nullable' por defecto.
        
        return $rules;
    }

    /**
     * Define los mensajes de error personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            'id_Cuota.required' => 'La cuota es obligatoria.',
            'id_Cuota.exists' => 'La cuota seleccionada no es válida.',
            'monto_pagado.required' => 'El monto pagado es obligatorio.',
            'monto_pagado.numeric' => 'El monto debe ser un número.',
            'monto_pagado.min' => 'El monto a pagar debe ser mayor que cero.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'fecha_pago.date_format' => 'El formato de la fecha no es válido.',
            'modalidad.required' => 'La modalidad de pago es obligatoria.',
            'modalidad.in' => 'La modalidad seleccionada no es válida.',
        ];
    }
}