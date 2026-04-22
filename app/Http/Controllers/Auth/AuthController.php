<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const LOCK_MINUTES = 30;
    private const TWO_FACTOR_TTL_MINUTES = 10;
    private const MAX_TWO_FACTOR_ATTEMPTS = 5;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'verify2fa']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos incompletos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var User|null $user */
        $user = User::query()->where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'Datos incorrectos...'], 401);
        }

        if ($this->isUserLocked($user)) {
            return $this->lockedResponse($user);
        }

        if (!Hash::check((string) $request->input('password'), (string) $user->password)) {
            $this->registerFailedPasswordAttempt($user);

            if ($this->isUserLocked($user->fresh())) {
                return $this->lockedResponse($user->fresh());
            }

            return response()->json(['error' => 'Datos incorrectos...'], 401);
        }

        if (!$user->activo) {
            return response()->json([
                'error' => 'Su usuario esta inactivo. Contacte al administrador.',
            ], 403);
        }

        $this->resetFailedPasswordAttempts($user);

        if ((bool) $user->two_factor_verified) {
            $token = JWTAuth::fromUser($user);
            return $this->respondWithToken($token, $user->fresh());
        }

        $twoFactorCode = $this->generateTwoFactorCode();
        $pendingToken = Str::uuid()->toString();
        $pendingKey = $this->pendingTokenCacheKey($pendingToken);

        $user->forceFill([
            'two_factor_code' => Hash::make($twoFactorCode),
            'two_factor_expires_at' => now()->addMinutes(self::TWO_FACTOR_TTL_MINUTES),
            'two_factor_attempts' => 0,
            'two_factor_verified' => false,
        ])->save();

        Cache::put($pendingKey, [
            'user_id' => $user->id,
            'email' => $user->email,
        ], now()->addMinutes(self::TWO_FACTOR_TTL_MINUTES));

        try {
            $this->sendTwoFactorCode($user, $twoFactorCode);
        } catch (\Throwable $e) {
            report($e);
            Cache::forget($pendingKey);
            $user->forceFill([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_attempts' => 0,
            ])->save();

            return response()->json([
                'error' => 'No se pudo enviar el codigo de verificacion. Intente nuevamente.',
            ], 500);
        }

        return response()->json([
            'requires_2fa' => true,
            'message' => 'Te enviamos un codigo de verificacion a tu correo.',
            'pending_token' => $pendingToken,
            'expires_in' => self::TWO_FACTOR_TTL_MINUTES * 60,
            'email' => $user->email,
        ]);
    }

    public function verify2fa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'pending_token' => 'required|string',
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ], [
            'code.regex' => 'El codigo debe tener 6 digitos numericos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos incompletos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pendingToken = (string) $request->input('pending_token');
        $pendingData = Cache::get($this->pendingTokenCacheKey($pendingToken));

        if (!$pendingData) {
            return response()->json([
                'error' => 'La verificacion expiro. Inicia sesion nuevamente.',
            ], 422);
        }

        /** @var User|null $user */
        $user = User::query()->find($pendingData['user_id'] ?? null);

        if (!$user || strcasecmp((string) $user->email, (string) $request->input('email')) !== 0) {
            Cache::forget($this->pendingTokenCacheKey($pendingToken));

            return response()->json([
                'error' => 'No se pudo validar la sesion de verificacion.',
            ], 422);
        }

        if ($this->isUserLocked($user)) {
            return $this->lockedResponse($user);
        }

        if (!$user->two_factor_code || !$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            Cache::forget($this->pendingTokenCacheKey($pendingToken));
            $user->forceFill([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_attempts' => 0,
                'two_factor_verified' => false,
            ])->save();

            return response()->json([
                'error' => 'El codigo ya expiro. Inicia sesion nuevamente.',
            ], 422);
        }

        $code = (string) $request->input('code');
        if (!Hash::check($code, (string) $user->two_factor_code)) {
            $attempts = (int) $user->two_factor_attempts + 1;
            $user->forceFill(['two_factor_attempts' => $attempts])->save();

            if ($attempts >= self::MAX_TWO_FACTOR_ATTEMPTS) {
                Cache::forget($this->pendingTokenCacheKey($pendingToken));
                $user->forceFill([
                    'two_factor_code' => null,
                    'two_factor_expires_at' => null,
                    'two_factor_attempts' => 0,
                    'two_factor_verified' => false,
                ])->save();

                return response()->json([
                    'error' => 'Excediste los intentos del codigo. Inicia sesion nuevamente.',
                ], 422);
            }

            return response()->json([
                'error' => 'Codigo incorrecto.',
                'attempts_left' => self::MAX_TWO_FACTOR_ATTEMPTS - $attempts,
            ], 422);
        }

        Cache::forget($this->pendingTokenCacheKey($pendingToken));

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
            'two_factor_verified' => true,
            'last_login_at' => now(),
        ])->save();

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user);
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
        $token = JWTAuth::refresh();

        /** @var User $user */
        $user = auth('api')->user();

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken(string $token, User $user)
    {
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
            'two_factor_verified' => (bool) $user->two_factor_verified,
            'locked_until' => optional($user->locked_until)->toDateTimeString(),
        ];
    }

    private function sendTwoFactorCode(User $user, string $code): void
    {
        Mail::raw(
            "Tu codigo de verificacion es: {$code}\n\nEste codigo vence en " . self::TWO_FACTOR_TTL_MINUTES . " minutos.",
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Codigo de verificacion de acceso - HECAB');
            }
        );
    }

    private function generateTwoFactorCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function pendingTokenCacheKey(string $pendingToken): string
    {
        return 'auth:2fa:pending:' . $pendingToken;
    }

    private function isUserLocked(User $user): bool
    {
        return $user->locked_until !== null && now()->lt($user->locked_until);
    }

    private function lockedResponse(User $user)
    {
        $minutes = max(1, now()->diffInMinutes($user->locked_until));

        return response()->json([
            'error' => "Cuenta bloqueada temporalmente por intentos fallidos. Intenta nuevamente en {$minutes} minuto(s).",
            'locked_until' => optional($user->locked_until)->toDateTimeString(),
        ], 423);
    }

    private function registerFailedPasswordAttempt(User $user): void
    {
        $attempts = (int) $user->failed_login_attempts + 1;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => now()->addMinutes(self::LOCK_MINUTES),
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_attempts' => 0,
                // Tras bloqueo por intentos fallidos, exige 2FA de nuevo.
                'two_factor_verified' => false,
            ])->save();

            return;
        }

        $user->forceFill([
            'failed_login_attempts' => $attempts,
        ])->save();
    }

    private function resetFailedPasswordAttempts(User $user): void
    {
        if ((int) $user->failed_login_attempts === 0 && $user->locked_until === null) {
            return;
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }
}
