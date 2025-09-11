<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();
        if ($search = $request->string('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        $perPage = (int) $request->input('per_page', 0);
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }
        return $query->get();
    }

    public function show(Permission $permission)
    {
        return $permission;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:permissions,title'],
        ]);
        $permission = Permission::create([
            'title' => $validated['title'],
        ]);
        return response()->json($permission, 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('permissions', 'title')->ignore($permission->id)],
        ]);
        if (array_key_exists('title', $validated)) {
            $permission->update([
                'title' => $validated['title'],
            ]);
        }
        return $permission;
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(null, 204);
    }
}
