<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $query = User::with(['roles'])->select(['id', 'name', 'email', 'approved', 'created_at', 'nik', 'no_hp', 'uid', 'device_name', 'region', 'village']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(User $user)
    {
        $user->load('roles');
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'approved' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        if (array_key_exists('approved', $validated)) {
            $user->approved = (bool) $validated['approved'];
        }
        $user->save();

        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        $user->load('roles');
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'approved' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        if (array_key_exists('name', $validated)) $user->name = $validated['name'];
        if (array_key_exists('email', $validated)) $user->email = $validated['email'];
        if (array_key_exists('password', $validated) && $validated['password']) $user->password = $validated['password'];
        if (array_key_exists('approved', $validated)) $user->approved = (bool) $validated['approved'];
        $user->save();

        if (array_key_exists('roles', $validated)) {
            $user->roles()->sync($validated['roles'] ?? []);
        }

        $user->load('roles');
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function roles()
    {
        return response()->json(Role::select(['id', 'title'])->orderBy('title')->get());
    }
}
