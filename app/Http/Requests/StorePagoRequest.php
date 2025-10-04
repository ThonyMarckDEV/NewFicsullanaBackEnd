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
        return [
            'id_Cuota' => ['required', 'integer', 'exists:cuotas,id'],
            'monto_pagado' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date_format:Y-m-d'],
            'modalidad' => ['required', 'string', Rule::in(['PRESENCIAL', 'VIRTUAL'])],
            'numero_operacion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
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