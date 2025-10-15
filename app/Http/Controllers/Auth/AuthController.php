<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Crear una nueva instancia del controlador.
     *
     * @return void
     */
    public function __construct()
    {
        // Protege todas las rutas excepto login
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Iniciar sesión y generar un token JWT.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Datos incorrectos...'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Obtener el usuario autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    /**
     * Cerrar sesión (invalidar el token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
            
        try {
            // Obtener el token enviado en el header Authorization
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'Token no proporcionado'], 400);
            }

            // Invalida el token (logout)
            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Sesión cerrada correctamente desde el servidor.'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'No se pudo cerrar sesión'
            ], 500);
        }
    }

    /**
     * Refrescar un token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Estructura de respuesta con token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'email' => JWTAuth::user()->email,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60
        ]);
    }
}
