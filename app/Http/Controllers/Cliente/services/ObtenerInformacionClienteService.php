<?php

namespace App\Http\Controllers\Cliente\services;

use App\Models\User;
use Illuminate\Support\Facades\Auth; // Importa el Facade de Autenticación

/**
 * Servicio encargado de cargar y formatear toda la información de un cliente.
 */
class ObtenerInformacionClienteService
{
    protected $modalidadClienteService;

    /**
     * Inyecta las dependencias necesarias.
     * @param ObtenerModalidadCliente $modalidadClienteService Servicio para calcular la modalidad.
     */
    public function __construct(ObtenerModalidadCliente $modalidadClienteService)
    {
        $this->modalidadClienteService = $modalidadClienteService;
    }

    /**
     * Ejecuta la lógica principal del servicio.
     * @param User $cliente El modelo User del cliente.
     * @return array Un array con toda la información del cliente formateada.
     */
    public function execute(User $cliente): array
    {
        // 1. Obtener el usuario autenticado que está realizando la solicitud.
        $authenticatedUser = Auth::user();

        // 2. VERIFICAR ROL: Si el usuario es un Cajero (rol 4), devolver solo datos limitados.
        if ($authenticatedUser && $authenticatedUser->id_Rol === 4) {
            
            // Cargamos solo la relación 'datos' para obtener el nombre.
            $cliente->load('datos');

            // Devolvemos una estructura simple y segura.
            return [
                'id'=> $cliente->id,
                'datos' => [ 
                    'nombre'          => optional($cliente->datos)->nombre,
                    'apellidoPaterno' => optional($cliente->datos)->apellidoPaterno,
                    'apellidoMaterno' => optional($cliente->datos)->apellidoMaterno,
                ]
            ];
        }

        // 3. LÓGICA ORIGINAL PARA ADMINS Y OTROS ROLES: Si no es cajero, devuelve todo.
        
        // Cargar todas las relaciones necesarias para una consulta eficiente.
        $cliente->load([
            'rol',
            'prestamos',
            'datos.direcciones',
            'datos.contactos',
            'datos.empleos',
            'datos.cuentasBancarias',
            'avales'
        ]);

        // Usar el servicio inyectado para calcular la modalidad del cliente.
        $modalidadClienteCalculada = $this->modalidadClienteService->obtenerModalidad($cliente);

        // Construir la estructura de datos final y completa para la respuesta.
        return [
            'id'                => $cliente->id,
            'username'          => $cliente->username,
            'estado'            => $cliente->estado,
            'datos'             => optional($cliente->datos)->getAttributes(),
            'modalidad_cliente' => $modalidadClienteCalculada,
            'direcciones'       => optional($cliente->datos)->direcciones,
            'contactos'         => optional($cliente->datos)->contactos,
            'empleos'           => optional($cliente->datos)->empleos,
            'cuentas_bancarias' => optional($cliente->datos)->cuentasBancarias,
            'avales'            => $cliente->avales,
        ];
    }
}