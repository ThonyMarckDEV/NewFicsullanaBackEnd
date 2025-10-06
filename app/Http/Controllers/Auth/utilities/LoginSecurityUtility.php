<?php

namespace App\Http\Controllers\Auth\utilities;

use App\Http\Controllers\Auth\services\PasswordResetService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginSecurityUtility
{
    /**
     * Verifica si un cliente está iniciando sesión con una contraseña predeterminada (usuario === contraseña).
     * Si es así, activa un restablecimiento de contraseña y devuelve una respuesta JSON específica.
     *
     * @param User $user El usuario autenticado.
     * @param Request $request La solicitud de login.
     * @return JsonResponse|null Una respuesta JSON si se requiere un reseteo, de lo contrario null.
     */
    public static function checkInitialPassword(User $user, Request $request): ?JsonResponse
    {
        // La condición solo se aplica a clientes (id_Rol = 2)
        // y cuando la contraseña es igual al nombre de usuario.
        if ($user->id_Rol === 2 && $request->password === $user->username) {
            
            // 1. Verificar si ya existe un token de reseteo que NO haya expirado.
            $existingToken = DB::table('password_reset_tokens')
                ->where('id_Usuario', $user->id)
                ->where('expires_at', '>', now())
                ->first();

            // 2. Si se encuentra un token válido, se informa al usuario y no se envía otro correo.
            if ($existingToken) {
                return response()->json([
                    'message' => 'Ya se ha enviado un enlace para restablecer su contraseña. Por favor, revise su correo o bandeja de spam.',
                    'reset_required' => true,
                ], 403);
            }

            // 3. Si no hay un token válido (porque no existe o ya expiró), se procede a crear y enviar uno nuevo.
            try {
                // El servicio handlePasswordReset ya borra los tokens antiguos antes de crear el nuevo.
                PasswordResetService::handlePasswordReset(
                    $user,
                    $request->ip(),
                    $request->userAgent()
                );

                // Devolvemos una respuesta para forzar el cambio de contraseña.
                return response()->json([
                    'message' => 'Por su seguridad, tendrá que restablecer su contraseña. Se ha enviado un nuevo enlace a su correo.',
                    'reset_required' => true, // Una bandera útil para el frontend
                ], 403);

            } catch (\Exception $e) {
                Log::error('Error al activar el reseteo de contraseña inicial para el usuario ID ' . $user->id . ': ' . $e->getMessage());
                
                // Si falla el envío del correo, devuelve un error.
                return response()->json([
                    'message' => 'Se requiere un cambio de contraseña, pero hubo un error al enviar el enlace. Por favor, contacte a soporte.',
                ], 500);
            }
        }

        // Si las condiciones no se cumplen, devuelve null para que el login continúe normalmente.
        return null;
    }
}