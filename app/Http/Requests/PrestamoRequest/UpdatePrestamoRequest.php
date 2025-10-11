<?php

namespace App\Http\Requests\PrestamoRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UpdatePrestamoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     *
     * La autorización se concede solo si el préstamo fue generado hoy
     * Y no tiene ninguna cuota pagada.
     */
    public function authorize(): bool
    {
        // Se obtiene la instancia del modelo Prestamo desde la ruta.
        $prestamo = $this->route('prestamo');

        // 1. Verifica si el préstamo tiene alguna cuota con estado 2 (pagada).
        //    El método `doesntExist()` es más eficiente que `!exists()`.
        $noTieneCuotasPagadas = $prestamo->cuota()->where('estado', 2)->doesntExist();

        // 2. Verifica si la fecha de generación del préstamo es el día de hoy.
        $esDeHoy = Carbon::parse($prestamo->fecha_generacion)->isToday();

        // El usuario está autorizado solo si ambas condiciones son verdaderas.
        return $esDeHoy && $noTieneCuotasPagadas;
    }

    /**
     * Obtiene las reglas de validación que aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // NOTA: No incluimos 'id_Cliente' ni 'modalidad'
        return [
            'id_Asesor' => ['required', 'integer', 'exists:usuarios,id'],
            
            'id_Producto' => ['required', 'integer', 'exists:productos,id'],
            'monto' => ['required', 'numeric', 'min:100'],
            'interes' => ['required', 'numeric', 'min:0', 'max:1'], 
            'cuotas' => ['required', 'integer', 'min:1'],
            'total' => ['required', 'numeric', 'min:0'],
            'valor_cuota' => ['required', 'numeric', 'min:0'],
            
            'frecuencia' => ['required', 'string', Rule::in(['SEMANAL', 'CATORCENAL', 'MENSUAL'])],
            'abonado_por' => ['required', 'string', Rule::in(['CUENTA CORRIENTE', 'CAJA CHICA'])],
        ];
    }

    /**
     * Define mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            // ===== MENSAJES AÑADIDOS =====
            'id_Asesor.required' => 'Debe seleccionar un asesor.',
            'id_Asesor.integer' => 'El asesor seleccionado no es válido.',
            'id_Asesor.exists' => 'El asesor seleccionado no existe en la base de datos.',
            // =============================

            'id_Producto.required' => 'Debe seleccionar un producto.',
            'id_Producto.exists' => 'El producto seleccionado no existe.',

            'monto.required' => 'El monto del préstamo es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto mínimo del préstamo es de S/ :min.',

            'interes.required' => 'La tasa de interés es obligatoria.',
            'interes.numeric' => 'La tasa de interés debe ser un valor numérico.',

            'cuotas.required' => 'El número de cuotas es obligatorio.',
            'cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'cuotas.min' => 'El préstamo debe tener al menos :min cuota.',

            'frecuencia.required' => 'Debe seleccionar una frecuencia de pago.',
            'frecuencia.in' => 'La frecuencia de pago seleccionada no es válida.',
            
            'abonado_por.required' => 'Debe especificar de dónde se abona el dinero.',
            'abonado_por.in' => 'El origen del abono seleccionado no es válido.',
        ];
    }
}