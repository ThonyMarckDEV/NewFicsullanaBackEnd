<?php

namespace App\Http\Requests\PrestamoRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReprogramarPrestamoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'prestamo_id' => ['required', 'integer', 'exists:prestamos,id'],
            'nueva_tasa' => ['required', 'numeric', 'between:0.01,0.05'], // 1% a 5%
            'nueva_frecuencia' => ['required', 'string', Rule::in(['SEMANAL', 'CATORCENAL', 'MENSUAL'])],
        ];
    }
}