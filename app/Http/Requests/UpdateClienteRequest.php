<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // 🚀 OBTENER EL ID DEL CLIENTE A IGNORAR 🚀
        // El parámetro de ruta 'cliente' ya es la instancia del modelo gracias a Route Model Binding.
        // Asumo que tu modelo usa la columna 'id_Datos' para el ID que necesitamos.
        $clienteId = $this->route('cliente')->id_Datos; 

        return [
            // --- Sección de Datos Personales ---
            'datos' => 'required|array',
            'datos.nombre' => 'required|string|max:100',
            'datos.apellidoPaterno' => 'required|string|max:100',
            'datos.apellidoMaterno' => 'required|string|max:100',
            'datos.apellidoConyuge' => 'nullable|string|max:100',
            'datos.estadoCivil' => ['required', 'string', Rule::in(['SOLTERO/A', 'CASADO/A', 'VIUDO/A', 'DIVORCIADO/A', 'CONVIVIENTE'])],
            'datos.sexo' => 'required|string|in:Masculino,Femenino',
            
            // 💡 REGLA UNIQUE: IGNORAR DNI DEL CLIENTE ACTUAL
            'datos.dni' => ['required', 'string', 'digits_between:8,9', Rule::unique('datos', 'dni')->ignore($clienteId)],
            
            'datos.fechaNacimiento' => 'required|date|before_or_equal:today',
            'datos.fechaCaducidadDni' => 'required|date|after:today',
            'datos.nacionalidad' => 'required|string|max:100',
            'datos.residePeru' => 'required|boolean',
            'datos.nivelEducativo' => 'required|string',
            'datos.profesion' => 'required|string|max:150',
            'datos.enfermedadesPreexistentes' => 'required|boolean',
            
            // 💡 REGLA UNIQUE: IGNORAR RUC DEL CLIENTE ACTUAL
            'datos.ruc' => ['nullable', 'string', 'digits:11', Rule::unique('datos', 'ruc')->ignore($clienteId)],
            
            'datos.expuestaPoliticamente' => 'required|boolean',

            // --- Sección de Direcciones ---
            'direcciones' => 'required|array',
            'direcciones.direccionFiscal' => 'required|string',
            'direcciones.direccionCorrespondencia' => 'nullable|string',
            'direcciones.departamento' => 'required|string',
            'direcciones.provincia' => 'required|string',
            'direcciones.distrito' => 'required|string',
            'direcciones.tipoVivienda' => 'required|string',
            'direcciones.tiempoResidencia' => 'required|string',
            'direcciones.referenciaDomicilio' => 'required|string',
            
            // --- Sección de Contactos ---
            'contactos' => 'required|array',
            'contactos.telefonoMovil' => 'required|string|digits:9',
            'contactos.telefonoFijo' => 'nullable|string|max:20',
            'contactos.correo' => 'required|email|max:150',

            // --- Sección de Empleo (Opcional) ---
            'empleo' => 'nullable|array',
            'empleo.centroLaboral' => 'required_with:empleo|string',
            'empleo.ingresoMensual' => 'required_with:empleo|numeric|min:0',
            'empleo.inicioLaboral' => 'required_with:empleo|date',
            'empleo.situacionLaboral' => 'required_with:empleo|string',
            
            // --- Sección de Cuentas Bancarias (Opcional) ---
            // Asumo que la tabla 'cuentas_bancarias' tiene una columna 'id_Datos' o 'cliente_id' 
            // que relaciona al cliente (en este ejemplo uso 'id_Datos' para ser consistente con la lógica de DNI/RUC)
            'cuentasBancarias' => 'nullable|array',
            'cuentasBancarias.entidadFinanciera' => 'required_with:cuentasBancarias|string',
            
            // 💡 REGLA UNIQUE: IGNORAR CTA AHORROS DEL CLIENTE ACTUAL
            // Aquí puedes necesitar ajustar el segundo parámetro si el campo que usas para relacionar al cliente
            // en la tabla 'cuentas_bancarias' es diferente a 'id_Datos'.
            'cuentasBancarias.ctaAhorros' => ['required_with:cuentasBancarias', 'string', Rule::unique('cuentas_bancarias', 'ctaAhorros')->ignore($clienteId, 'id_Datos')],
            
            // 💡 REGLA UNIQUE: IGNORAR CCI DEL CLIENTE ACTUAL
            'cuentasBancarias.cci' => ['nullable', 'string', 'digits:20', Rule::unique('cuentas_bancarias', 'cci')->ignore($clienteId, 'id_Datos')],

            // --- Sección de Avales (Lista opcional) ---
            'avales' => 'nullable|array',
            'avales.*.dniAval' => 'required|string|digits:8',
            'avales.*.nombresAval' => 'required|string',
            'avales.*.apellidoPaternoAval' => 'required|string',
            'avales.*.apellidoMaternoAval' => 'required|string',
            'avales.*.telefonoMovilAval' => 'required|string|digits:9',
            'avales.*.telefonoFijoAval' => 'nullable|string|max:20',
            'avales.*.direccionAval' => 'required|string',
            'avales.*.referenciaDomicilioAval' => 'required|string',
            'avales.*.departamentoAval' => 'required|string',
            'avales.*.provinciaAval' => 'required|string',
            'avales.*.distritoAval' => 'required|string',
            'avales.*.relacionClienteAval' => 'required|string',
        ];
    }
    
    /**
     * Obtiene los mensajes de error personalizados.
     * (Copia los mensajes de StoreClienteRequest sin cambios)
     */
    public function messages(): array
    {
        return [
            // --- Mensajes para Datos Personales ---
            'datos.required' => 'La sección de datos personales es obligatoria.',
            'datos.nombre.required' => 'El nombre del cliente es obligatorio.',
            'datos.apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'datos.apellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'datos.estadoCivil.required' => 'El estado civil es obligatorio.',
            'datos.sexo.required' => 'El sexo es obligatorio.',
            'datos.dni.required' => 'El DNI del cliente es obligatorio.',
            'datos.dni.digits_between' => 'El DNI debe tener entre 8 y 9 dígitos.',
            'datos.dni.unique' => 'Este DNI ya se encuentra registrado.',
            'datos.fechaNacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'datos.fechaNacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'datos.fechaCaducidadDni.required' => 'La fecha de caducidad del DNI es obligatoria.',
            'datos.fechaCaducidadDni.after' => 'El DNI parece haber expirado.',
            'datos.nacionalidad.required' => 'La nacionalidad es obligatoria.',
            'datos.residePeru.required' => 'Debe indicar si reside en Perú.',
            'datos.nivelEducativo.required' => 'El nivel educativo es obligatorio.',
            'datos.profesion.required' => 'La profesión es obligatoria.',
            'datos.ruc.digits' => 'El RUC debe tener 11 dígitos.',
            'datos.ruc.unique' => 'Este RUC ya se encuentra registrado.',

            // --- Mensajes para Direcciones ---
            'direcciones.required' => 'La sección de direcciones es obligatoria.',
            'direcciones.direccionFiscal.required' => 'La dirección fiscal es obligatoria.',
            'direcciones.departamento.required' => 'El departamento es obligatorio.',
            'direcciones.provincia.required' => 'La provincia es obligatoria.',
            'direcciones.distrito.required' => 'El distrito es obligatorio.',
            'direcciones.tipoVivienda.required' => 'El tipo de vivienda es obligatorio.',
            'direcciones.tiempoResidencia.required' => 'El tiempo de residencia es obligatorio.',
            'direcciones.referenciaDomicilio.required' => 'La referencia del domicilio es obligatoria.',
            
            // --- Mensajes para Contactos ---
            'contactos.required' => 'La sección de contacto es obligatoria.',
            'contactos.telefonoMovil.required' => 'El teléfono móvil es obligatorio.',
            'contactos.telefonoMovil.digits' => 'El teléfono móvil debe tener 9 dígitos.',
            'contactos.correo.required' => 'El correo electrónico es obligatorio.',
            'contactos.correo.email' => 'El formato del correo no es válido.',
            
            // --- Mensajes para Empleo ---
            'empleo.centroLaboral.required_with' => 'El centro laboral es obligatorio si se ingresan datos de empleo.',
            'empleo.ingresoMensual.required_with' => 'El ingreso mensual es obligatorio si se ingresan datos de empleo.',
            'empleo.ingresoMensual.numeric' => 'El ingreso mensual debe ser un número.',
            
            // --- Mensajes para Cuentas Bancarias ---
            'cuentasBancarias.entidadFinanciera.required_with' => 'La entidad financiera es obligatoria.',
            'cuentasBancarias.ctaAhorros.required_with' => 'La cuenta de ahorros es obligatoria.',
            'cuentasBancarias.ctaAhorros.unique' => 'Esta cuenta de ahorros ya está registrada.',
            'cuentasBancarias.cci.digits' => 'El CCI debe tener 20 dígitos.',
            'cuentasBancarias.cci.unique' => 'Este CCI ya está registrado.',
            
            // --- Mensajes para Avales (usando el * para aplicar a todos) ---
            'avales.*.dniAval.required' => 'El DNI del aval es obligatorio.',
            'avales.*.dniAval.digits' => 'El DNI del aval debe tener 8 dígitos.',
            'avales.*.nombresAval.required' => 'El nombre del aval es obligatorio.',
            'avales.*.apellidoPaternoAval.required' => 'El apellido paterno del aval es obligatorio.',
            'avales.*.apellidoMaternoAval.required' => 'El apellido materno del aval es obligatorio.',
            'avales.*.telefonoMovilAval.required' => 'El teléfono móvil del aval es obligatorio.',
            'avales.*.telefonoMovilAval.digits' => 'El teléfono móvil del aval debe tener 9 dígitos.',
            'avales.*.direccionAval.required' => 'La dirección del aval es obligatoria.',
            'avales.*.referenciaDomicilioAval.required' => 'La referencia del domicilio del aval es obligatoria.',
            'avales.*.departamentoAval.required' => 'El departamento del aval es obligatorio.',
            'avales.*.provinciaAval.required' => 'La provincia del aval es obligatorio.',
            'avales.*.distritoAval.required' => 'El distrito del aval es obligatorio.',
            'avales.*.relacionClienteAval.required' => 'La relación del aval con el cliente es obligatoria.',
        ];
    }
}