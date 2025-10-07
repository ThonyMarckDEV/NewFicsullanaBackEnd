<?php

namespace App\Http\Requests\EmpleadoRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpleadoRequest extends FormRequest
{
    /**
     * Prepara los datos para la validación (agrupa los campos de 'datos').
     * @return void
     */
    protected function prepareForValidation()
    {
        // Campos que pertenecen a la tabla 'datos' (la relación)
        $datosKeys = [
            'nombre', 'apellidoPaterno', 'apellidoMaterno', 'apellidoConyuge', 
            'estadoCivil', 'sexo', 'dni', 'fechaNacimiento', 'fechaCaducidadDni', 
            'nacionalidad', 'residePeru', 'nivelEducativo', 'profesion', 
            'enfermedadesPreexistentes', 'ruc', 'expuestaPoliticamente'
        ];

        $datos = [];
        $input = $this->all();

        // Mover los campos de 'datos' a la clave 'datos'
        foreach ($datosKeys as $key) {
            if (isset($input[$key])) {
                $datos[$key] = $input[$key];
            }
        }

        // FUSIONAR: Asegurarse de que el array 'datos' exista en la petición
        $this->merge([
            'datos' => $datos,
        ]);
    }

    public function rules(): array
    {
        // Se asume que $this->empleado es el modelo User inyectado por Route Model Binding
        
        return [
            // DATOS DE LA TABLA 'usuarios'
            'id_Rol' => ['required', 'integer', Rule::in([3, 4])], 
            'username' => [
                'required',
                'string',
                'max:255',
                // ✅ CORRECCIÓN CONFIRMADA: Usamos 'usuarios' para la tabla de users
                Rule::unique('usuarios', 'username')->ignore($this->empleado->id),
            ],
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable|min:8',
            
            // DATOS DE LA TABLA 'datos' (anidados)
            'datos.nombre' => 'required|string|max:255',
            'datos.apellidoPaterno' => 'required|string|max:255',
            'datos.apellidoMaterno' => 'required|string|max:255',
            'datos.apellidoConyuge' => 'nullable|string|max:255',
            'datos.estadoCivil' => ['required', 'string', Rule::in(['SOLTERO/A', 'CASADO/A', 'VIUDO/A', 'DIVORCIADO/A', 'CONVIVIENTE'])],
            'datos.sexo' => ['required', Rule::in(['Masculino', 'Femenino'])],
            'datos.dni' => [
                'required', 
                'string', 
                'size:8', 
                // 🛑 CORRECCIÓN: Usamos $this->empleado->id_Datos para ignorar el registro correcto en la tabla 'datos'
                Rule::unique('datos', 'dni')->ignore($this->empleado->id_Datos, 'id'), 
            ],
            'datos.fechaNacimiento' => 'required|date|before:today',
            'datos.fechaCaducidadDni' => 'required|date|after_or_equal:today',
            'datos.nacionalidad' => 'required|string|max:255',
            'datos.residePeru' => 'required|boolean',
            'datos.nivelEducativo' => 'required|string|max:255',
            'datos.profesion' => 'required|string|max:255',
            'datos.enfermedadesPreexistentes' => 'required|boolean',
            'datos.ruc' => [
                'nullable', 
                'string', 
                'size:11', 
                // 🛑 CORRECCIÓN: Usamos $this->empleado->id_Datos para ignorar el registro correcto en la tabla 'datos'
                Rule::unique('datos', 'ruc')->ignore($this->empleado->id_Datos, 'id'),
            ],
            'datos.expuestaPoliticamente' => 'required|boolean',
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
            'username.unique' => 'Este nombre de usuario ya está en uso por otro empleado.',

            // Reglas específicas para password
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide con la contraseña.',

            // Reglas específicas para datos (anidados)
            'datos.nombre.required' => 'El nombre es obligatorio.',
            'datos.nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'datos.apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'datos.apellidoPaterno.max' => 'El apellido paterno no puede exceder 255 caracteres.',
            'datos.apellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'datos.apellidoMaterno.max' => 'El apellido materno no puede exceder 255 caracteres.',
            'datos.apellidoConyuge.max' => 'El apellido de cónyuge no puede exceder 255 caracteres.',
            'datos.estadoCivil.required' => 'El estado civil es obligatorio.',
            'datos.estadoCivil.in' => 'El estado civil debe ser uno de: Soltero/a, Casado/a, Viudo/a, Divorciado/a, Conviviente.',
            'datos.sexo.required' => 'El sexo es obligatorio.',
            'datos.sexo.in' => 'El sexo debe ser Masculino o Femenino.',
            'datos.dni.required' => 'El DNI es obligatorio.',
            'datos.dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'datos.dni.unique' => 'Este DNI ya está registrado por otro empleado.',
            'datos.fechaNacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'datos.fechaNacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'datos.fechaCaducidadDni.required' => 'La fecha de caducidad del DNI es obligatoria.',
            'datos.fechaCaducidadDni.after_or_equal' => 'La fecha de caducidad del DNI debe ser igual o posterior a hoy.',
            'datos.nacionalidad.required' => 'La nacionalidad es obligatoria.',
            'datos.nacionalidad.max' => 'La nacionalidad no puede exceder 255 caracteres.',
            'datos.residePeru.required' => 'Debe indicar si reside en Perú.',
            'datos.nivelEducativo.required' => 'El nivel educativo es obligatorio.',
            'datos.nivelEducativo.max' => 'El nivel educativo no puede exceder 255 caracteres.',
            'datos.profesion.required' => 'La profesión es obligatoria.',
            'datos.profesion.max' => 'La profesión no puede exceder 255 caracteres.',
            'datos.enfermedadesPreexistentes.required' => 'Debe indicar si tiene enfermedades preexistentes.',
            'datos.ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'datos.ruc.unique' => 'Este RUC ya está registrado por otro empleado.',
            'datos.expuestaPoliticamente.required' => 'Debe indicar si está expuesto políticamente.',
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
            'datos.nombre' => 'nombre',
            'datos.apellidoPaterno' => 'apellido paterno',
            'datos.apellidoMaterno' => 'apellido materno',
            'datos.apellidoConyuge' => 'apellido de cónyuge',
            'datos.estadoCivil' => 'estado civil',
            'datos.sexo' => 'sexo',
            'datos.dni' => 'DNI',
            'datos.fechaNacimiento' => 'fecha de nacimiento',
            'datos.fechaCaducidadDni' => 'fecha de caducidad del DNI',
            'datos.nacionalidad' => 'nacionalidad',
            'datos.residePeru' => 'residencia en Perú',
            'datos.nivelEducativo' => 'nivel educativo',
            'datos.profesion' => 'profesión',
            'datos.enfermedadesPreexistentes' => 'enfermedades preexistentes',
            'datos.ruc' => 'RUC',
            'datos.expuestaPoliticamente' => 'exposición política',
        ];
    }
}