<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpleadoRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            // Datos de la tabla 'datos'
            'nombre' => 'required|string|max:255',
            'apellidoPaterno' => 'required|string|max:255',
            'apellidoMaterno' => 'required|string|max:255',
            'apellidoConyuge' => 'nullable|string|max:255',
            'estadoCivil' => ['required', 'string', Rule::in(['SOLTERO/A', 'CASADO/A', 'VIUDO/A', 'DIVORCIADO/A', 'CONVIVIENTE'])],
            'sexo' => ['required', Rule::in(['Masculino', 'Femenino'])],
            'dni' => 'required|string|size:8|unique:datos,dni',
            'fechaNacimiento' => 'required|date|before:today',
            'fechaCaducidadDni' => 'required|date|after_or_equal:today',
            'nacionalidad' => 'required|string|max:255',
            'residePeru' => 'required|boolean',
            'nivelEducativo' => 'required|string|max:255',
            'profesion' => 'required|string|max:255',
            'enfermedadesPreexistentes' => 'required|boolean',
            'ruc' => 'nullable|string|size:11|unique:datos,ruc',
            'expuestaPoliticamente' => 'required|boolean',

            // Datos de la tabla 'usuarios' (Datos de acceso)
            'username' => 'required|string|max:255|unique:usuarios,username',
            'password' => 'required|string|min:8|confirmed',
            'id_Rol' => ['required', 'integer', Rule::in([3, 4])],
        ];
    }
}