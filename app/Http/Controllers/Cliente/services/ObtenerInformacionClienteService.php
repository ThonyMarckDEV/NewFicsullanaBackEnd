<?php

namespace App\Http\Controllers\Cliente\services;

use App\Models\User;

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
        // 1. Cargar todas las relaciones necesarias para una consulta eficiente.
        $cliente->load([
            'rol',
            'prestamos',
            'datos.direcciones',
            'datos.contactos',
            'datos.empleos',
            'datos.cuentasBancarias',
            'avales'
        ]);

        // 2. Usar el servicio inyectado para calcular la modalidad del cliente.
        $modalidadClienteCalculada = $this->modalidadClienteService->obtenerModalidad($cliente);

        // 3. Construir la estructura de datos final para la respuesta.
        return [
            'id' => $cliente->id,
            'username' => $cliente->username,
            'estado' => $cliente->estado,
            'datos' => optional($cliente->datos)->getAttributes(),
            'modalidad_cliente' => $modalidadClienteCalculada,
            'direcciones' => optional($cliente->datos)->direcciones,
            'contactos' => optional($cliente->datos)->contactos,
            'empleos' => optional($cliente->datos)->empleos,
            'cuentas_bancarias' => optional($cliente->datos)->cuentasBancarias,
            'avales' => $cliente->avales,
        ];
    }
}
