<?php

namespace App;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'id_Cuota' => ['required', 'integer', 'exists:cuotas,id'],
            'monto_pagado' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'modalidad' => ['required', 'string', 'in:PRESENCIAL,VIRTUAL'],
            'numero_operacion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            // 'id_Usuario' se tomará del usuario autenticado, no del request
        ];
    }
}