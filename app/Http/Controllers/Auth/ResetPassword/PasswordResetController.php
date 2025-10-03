<?php

namespace App\Http\Controllers\Auth\ResetPassword;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Auth\services\PasswordResetService;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    /**
     * Handle the forgot password request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'DNI inválido',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Find user by DNI through datos
        $user = User::with(['datos', 'datos.contactos'])
            ->whereHas('datos', function ($query) use ($request) {
                $query->where('dni', $request->dni);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'DNI no existe',
            ], 404);
        }

        // Check if user role is not client (idRol != 3)
        if ($user->idRol !== 3) {
            return response()->json([
                'message' => 'Si no eres cliente y olvidaste tu contraseña, pídele al administrador que la cambie.',
            ], 403);
        }

        // Check for existing valid token and resend if found
        $existingTokenResult = PasswordResetService::checkExistingResetToken($user);
        if ($existingTokenResult) {
            return response()->json($existingTokenResult, $existingTokenResult['success'] ? 200 : 400);
        }

        // Handle new password reset request
        $result = PasswordResetService::handlePasswordReset($user, $request->ip(), $request->userAgent());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Display the password reset form.
     *
     * @param string $idUsuario
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function showResetForm($idUsuario, $token)
    {
        $resetToken = DB::table('password_reset_tokens')
            ->where('idUsuario', $idUsuario)
            ->where('token', $token)
            ->first();

        if (!$resetToken) {
            return view('reset-password', [
                'error' => 'Enlace de restablecimiento inválido.',
                'idUsuario' => $idUsuario,
                'token' => $token,
            ]);
        }

        if (Carbon::parse($resetToken->expires_at)->isPast()) {
            return view('reset-password', [
                'error' => 'Enlace de restablecimiento expirado. Solicita un nuevo enlace.',
                'idUsuario' => $idUsuario,
                'token' => $token,
            ]);
        }

        return view('reset-password', [
            'idUsuario' => $idUsuario,
            'token' => $token,
        ]);
    }

    /**
     * Handle the password reset submission.
     *
     * @param Request $request
     * @param string $idUsuario
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request, $idUsuario, $token)
    {
        $resetToken = DB::table('password_reset_tokens')
            ->where('idUsuario', $idUsuario)
            ->where('token', $token)
            ->first();

        if (!$resetToken || Carbon::parse($resetToken->expires_at)->isPast()) {
            return redirect()->route('password.reset.form', ['idUsuario' => $idUsuario, 'token' => $token])
                ->with('error', 'Enlace de restablecimiento inválido o expirado.');
        }

        $user = User::with('datos')->find($idUsuario);
        if (!$user) {
            return redirect()->route('password.reset.form', ['idUsuario' => $idUsuario, 'token' => $token])
                ->with('error', 'Usuario no encontrado.');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if the new password matches the user's DNI
        $dni = $user->datos->dni ?? '';
        if ($request->password === $dni) {
            return redirect()->back()
                ->withErrors(['password' => 'La contraseña no puede ser tu DNI.'])
                ->withInput();
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')
            ->where('idUsuario', $user->idUsuario)
            ->delete();

        return redirect()->route('password.reset.form', ['idUsuario' => $idUsuario, 'token' => $token])
            ->with('success', 'Contraseña cambiada exitosamente. Por favor, inicia sesión.');
    }
}