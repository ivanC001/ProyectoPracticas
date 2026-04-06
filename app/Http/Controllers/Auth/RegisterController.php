<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $firstUser = User::count() === 0;
        $authUser = auth("api")->user();

        $rules = [
            "name" => "required|string|max:255|min:3",
            "email" => "required|string|email|max:255|unique:users,email",
            "password" => "required|string|min:6|confirmed",
            "rol" => [
                Rule::in(array_keys(config("roles.definitions", []))),
            ],
            "activo" => "nullable|boolean",
        ];

        $messages = [
            "name.required" => "El nombre es obligatorio",
            "name.string" => "El nombre debe ser texto",
            "name.max" => "El nombre no puede tener mas de 255 caracteres",
            "name.min" => "El nombre debe tener al menos 3 caracteres",
            "email.required" => "El correo es obligatorio",
            "email.email" => "El correo no tiene un formato valido",
            "email.unique" => "Este correo ya esta registrado",
            "password.required" => "La contrasena es obligatoria",
            "password.min" => "La contrasena debe tener al menos 6 caracteres",
            "password.confirmed" => "La confirmacion de contrasena no coincide",
            "rol.required" => "El rol es obligatorio",
            "rol.in" => "El rol seleccionado no es valido",
        ];

        if ($firstUser) {
            $rules["rol"][] = "nullable";
        } else {
            $rules["rol"][] = "required";
            if (!$authUser || $authUser->rol !== "admin") {
                return response()->json([
                    "message" => "Solo un administrador puede registrar nuevos usuarios.",
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Error de validacion",
                "errors" => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "rol" => $firstUser ? "admin" : $request->rol,
            "activo" => $request->boolean("activo", true),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            "message" => "Usuario registrado correctamente",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "rol" => $user->rol,
                "rol_label" => $user->rol_label,
                "activo" => (bool) $user->activo,
            ],
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => JWTAuth::factory()->getTTL() * 60,
        ], 201);
    }
}
