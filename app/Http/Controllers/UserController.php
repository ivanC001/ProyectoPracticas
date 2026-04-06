<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('rol', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->activo);
        }

        $usuarios = $query->orderByDesc('id')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de usuarios',
            'roles' => config('roles.definitions', []),
            'data' => $usuarios->items(),
            'pagination' => [
                'total' => $usuarios->total(),
                'per_page' => $usuarios->perPage(),
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
            ],
        ]);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = $request->boolean('activo', true);

        $usuario = User::create($data);

        return response()->json([
            'success' => true,
            'message' => "Usuario creado: {$usuario->name}",
            'data' => $usuario,
        ], 201);
    }

    public function show(string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            throw ValidationException::withMessages([
                'usuario' => ['Usuario no encontrado.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario encontrado',
            'roles' => config('roles.definitions', []),
            'data' => $usuario,
        ]);
    }

    public function update(UserRequest $request, string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            throw ValidationException::withMessages([
                'usuario' => ['Usuario no encontrado.'],
            ]);
        }

        if ((int) auth('api')->id() === (int) $usuario->id && !$request->boolean('activo', true)) {
            throw ValidationException::withMessages([
                'activo' => ['No puede desactivar su propio usuario.'],
            ]);
        }

        $data = $request->validated();
        $newRole = $data['rol'] ?? $usuario->rol;
        $newActive = $request->boolean('activo', $usuario->activo);

        $this->ensureAtLeastOneAdmin($usuario, $newRole, $newActive);

        if ((int) auth('api')->id() === (int) $usuario->id && $newRole !== 'admin') {
            throw ValidationException::withMessages([
                'rol' => ['No puede cambiar su propio usuario administrador a otro rol.'],
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['activo'] = $newActive;

        $usuario->update($data);

        return response()->json([
            'success' => true,
            'message' => "Usuario actualizado: {$usuario->name}",
            'data' => $usuario->fresh(),
        ]);
    }

    public function destroy(string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            throw ValidationException::withMessages([
                'usuario' => ['Usuario no encontrado.'],
            ]);
        }

        if ((int) auth('api')->id() === (int) $usuario->id) {
            throw ValidationException::withMessages([
                'usuario' => ['No puede desactivar su propio usuario.'],
            ]);
        }

        $this->ensureAtLeastOneAdmin($usuario, $usuario->rol, false);

        $usuario->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => "Usuario desactivado: {$usuario->name}",
        ]);
    }

    protected function ensureAtLeastOneAdmin(User $usuario, string $newRole, bool $newActive): void
    {
        $isCurrentlyActiveAdmin = $usuario->rol === 'admin' && $usuario->activo;
        $willRemainActiveAdmin = $newRole === 'admin' && $newActive;

        if (!$isCurrentlyActiveAdmin || $willRemainActiveAdmin) {
            return;
        }

        $activeAdmins = User::query()
            ->where('rol', 'admin')
            ->where('activo', true)
            ->count();

        if ($activeAdmins <= 1) {
            throw ValidationException::withMessages([
                'rol' => ['Debe existir al menos un administrador activo en el sistema.'],
            ]);
        }
    }
}
