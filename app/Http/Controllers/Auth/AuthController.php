<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Datos incorrectos...'], 401);
        }

        /** @var User $user */
        $user = JWTAuth::user();

        if (!$user->activo) {
            JWTAuth::invalidate($token);

            return response()->json([
                'error' => 'Su usuario esta inactivo. Contacte al administrador.',
            ], 403);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'user' => $this->serializeUser(JWTAuth::user()),
        ]);
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['error' => 'Token no proporcionado'], 400);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Sesion cerrada correctamente desde el servidor.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'No se pudo cerrar sesion',
            ], 500);
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    protected function respondWithToken(string $token)
    {
        /** @var User $user */
        $user = JWTAuth::user();

        return response()->json([
            'access_token' => $token,
            'email' => $user->email,
            'user' => $this->serializeUser($user),
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol,
            'rol_label' => $user->rol_label,
            'activo' => (bool) $user->activo,
        ];
    }
}
