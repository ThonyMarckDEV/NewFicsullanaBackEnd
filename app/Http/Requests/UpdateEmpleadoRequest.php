<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpleadoRequest extends FormRequest
{
    public function rules(): array
    {
        $empleadoId = $this->route('empleado'); // Asume que la ruta usa {empleado}

        return [
            // Datos de la tabla 'datos'
            'nombre' => 'required|string|max:255',
            'apellidoPaterno' => 'required|string|max:255',
            'apellidoMaterno' => 'required|string|max:255',
            'apellidoConyuge' => 'nullable|string|max:255',
            'estadoCivil' => ['required', 'string', Rule::in(['SOLTERO/A', 'CASADO/A', 'VIUDO/A', 'DIVORCIADO/A', 'CONVIVIENTE'])],
            'sexo' => ['required', Rule::in(['Masculino', 'Femenino'])],
            'dni' => [
                'required', 
                'string', 
                'size:8', 
                // Ignorar el DNI actual del empleado en la tabla 'datos'
                Rule::unique('datos', 'dni')->ignore($this->empleado->id_Datos, 'id'), 
            ],
            'fechaNacimiento' => 'required|date|before:today',
            'fechaCaducidadDni' => 'required|date|after_or_equal:today',
            'nacionalidad' => 'required|string|max:255',
            'residePeru' => 'required|boolean',
            'nivelEducativo' => 'required|string|max:255',
            'profesion' => 'required|string|max:255',
            'enfermedadesPreexistentes' => 'required|boolean',
            'ruc' => [
                'nullable', 
                'string', 
                'size:11', 
                // Ignorar el RUC actual del empleado
                Rule::unique('datos', 'ruc')->ignore($this->empleado->id_Datos, 'id'),
            ],
            'expuestaPoliticamente' => 'required|boolean',

            // Datos de la tabla 'usuarios'
            'id_Rol' => ['required', 'integer', Rule::in([4, 5])], // Ajustado a valores del frontend: 4=Asesor, 5=Cajero
            'username' => [
                'required',
                'string',
                'max:255',
                // Ignorar el username actual del empleado en la tabla 'users'
                Rule::unique('usuarios', 'username')->ignore($this->empleado->id),
            ],
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable|min:8',
        ];
    }
}