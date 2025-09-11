<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');
        if ($search = $request->string('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        // Optional simple pagination
        $perPage = (int) $request->input('per_page', 0);
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }
        return $query->get();
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return $role;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:roles,title'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $role = Role::create([
            'title' => $validated['title'],
        ]);
        $role->permissions()->sync($request->input('permissions', []));
        $role->load('permissions');

        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'title')->ignore($role->id)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        if (array_key_exists('title', $validated)) {
            $role->update([
                'title' => $validated['title'],
            ]);
        }
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        $role->load('permissions');
        return $role;
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }
}
