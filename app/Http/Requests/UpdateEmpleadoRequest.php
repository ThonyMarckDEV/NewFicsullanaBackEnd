<?php

namespace App\Http\Requests;

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
}