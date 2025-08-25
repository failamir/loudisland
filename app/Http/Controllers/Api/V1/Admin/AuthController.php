<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // 'password' => 'required|string|min:6',
            'uid' => 'required|string|max:255',
            // 'nik' => 'nullable|string|max:50',
            // 'no_hp' => 'nullable|string|max:50',
            // 'device_name' => 'sometimes|string|max:100',
        ])->validate();

        // Normalize email for consistency
        $email = strtolower($data['email']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['email']),
            'nik' => $data['nik'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'uid' => $data['uid'],
            'device_name' => $data['device_name'] ?? 'api-token',
        ]);

        $token = $user->createToken($data['device_name'])->plainTextToken;

        // Return only necessary fields
        $userPayload = $user->only(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Registered successfully',
            'token' => $token,
            'data' => $userPayload,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            // 'device_name' => 'sometimes|string|max:100',
            'revoke_others' => 'sometimes|boolean',
        ])->validate();

        $email = strtolower($data['email']);

        // Select only columns we need
        $user = User::query()
            ->select(['id', 'name', 'email', 'nik', 'no_hp'])
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'data' => null,
            ], 401);
        }

        // Optionally revoke existing tokens (default true for backward compatibility)
        $revoke = array_key_exists('revoke_others', $data) ? (bool)$data['revoke_others'] : true;
        if ($revoke) {
            $user->tokens()->delete();
        }

        $deviceName = $data['device_name'] ?? 'api-token';
        $token = $user->createToken($deviceName)->plainTextToken;

        $userPayload = collect($user)->except(['password'])->all();

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $userPayload,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
