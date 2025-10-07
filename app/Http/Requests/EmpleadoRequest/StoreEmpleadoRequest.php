<?php

namespace App\Http\Requests\EmpleadoRequest;

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

    /**
     * Mensajes de validación personalizados.
     */
    public function messages(): array
    {
        return [
            // Mensajes generales
            '*.required' => 'El campo :attribute es obligatorio.',
            '*.string' => 'El campo :attribute debe ser un texto.',
            '*.max' => [
                'string' => 'El campo :attribute no puede exceder :max caracteres.',
            ],
            '*.integer' => 'El campo :attribute debe ser un número entero.',
            '*.boolean' => 'El campo :attribute debe ser verdadero o falso.',
            '*.date' => 'El campo :attribute debe ser una fecha válida.',
            '*.before' => 'El campo :attribute debe ser una fecha anterior a hoy.',
            '*.after_or_equal' => 'El campo :attribute debe ser una fecha igual o posterior a hoy.',
            '*.size' => 'El campo :attribute debe tener exactamente :size caracteres.',
            '*.confirmed' => 'La confirmación de :attribute no coincide.',
            '*.min' => [
                'string' => 'El campo :attribute debe tener al menos :min caracteres.',
            ],
            '*.unique' => 'El valor de :attribute ya está en uso.',

            // Reglas específicas para roles
            'id_Rol.in' => 'El rol seleccionado no es válido. Debe ser Asesor o Cajero.',

            // Reglas específicas para username
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.max' => 'El nombre de usuario no puede exceder 255 caracteres.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',

            // Reglas específicas para password
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide con la contraseña.',

            // Reglas específicas para datos
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.max' => 'El apellido paterno no puede exceder 255 caracteres.',
            'apellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'apellidoMaterno.max' => 'El apellido materno no puede exceder 255 caracteres.',
            'apellidoConyuge.max' => 'El apellido de cónyuge no puede exceder 255 caracteres.',
            'estadoCivil.required' => 'El estado civil es obligatorio.',
            'estadoCivil.in' => 'El estado civil debe ser uno de: Soltero/a, Casado/a, Viudo/a, Divorciado/a, Conviviente.',
            'sexo.required' => 'El sexo es obligatorio.',
            'sexo.in' => 'El sexo debe ser Masculino o Femenino.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'fechaNacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fechaNacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'fechaCaducidadDni.required' => 'La fecha de caducidad del DNI es obligatoria.',
            'fechaCaducidadDni.after_or_equal' => 'La fecha de caducidad del DNI debe ser igual o posterior a hoy.',
            'nacionalidad.required' => 'La nacionalidad es obligatoria.',
            'nacionalidad.max' => 'La nacionalidad no puede exceder 255 caracteres.',
            'residePeru.required' => 'Debe indicar si reside en Perú.',
            'nivelEducativo.required' => 'El nivel educativo es obligatorio.',
            'nivelEducativo.max' => 'El nivel educativo no puede exceder 255 caracteres.',
            'profesion.required' => 'La profesión es obligatoria.',
            'profesion.max' => 'La profesión no puede exceder 255 caracteres.',
            'enfermedadesPreexistentes.required' => 'Debe indicar si tiene enfermedades preexistentes.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'Este RUC ya está registrado.',
            'expuestaPoliticamente.required' => 'Debe indicar si está expuesto políticamente.',
        ];
    }

    /**
     * Atributos personalizados para los mensajes.
     */
    public function attributes(): array
    {
        return [
            'id_Rol' => 'rol',
            'username' => 'nombre de usuario',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'nombre' => 'nombre',
            'apellidoPaterno' => 'apellido paterno',
            'apellidoMaterno' => 'apellido materno',
            'apellidoConyuge' => 'apellido de cónyuge',
            'estadoCivil' => 'estado civil',
            'sexo' => 'sexo',
            'dni' => 'DNI',
            'fechaNacimiento' => 'fecha de nacimiento',
            'fechaCaducidadDni' => 'fecha de caducidad del DNI',
            'nacionalidad' => 'nacionalidad',
            'residePeru' => 'residencia en Perú',
            'nivelEducativo' => 'nivel educativo',
            'profesion' => 'profesión',
            'enfermedadesPreexistentes' => 'enfermedades preexistentes',
            'ruc' => 'RUC',
            'expuestaPoliticamente' => 'exposición política',
        ];
    }
}