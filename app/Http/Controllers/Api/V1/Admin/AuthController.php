<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct()
    {
        // Routes protection is handled in routes using auth:api (jwt guard)
    }

    public function register(Request $request)
    {
        //if email already reistered
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'Email already registered',
                'data' => null,
            ], 400);
        }

        $data = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'uid' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:50',
            'device_name' => 'sometimes|string|max:100',
        ])->validate();

        // Normalize email for consistency
        $email = strtolower($data['email']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password']),
            'nik' => $data['nik'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'uid' => $data['uid'],
        ]);

        // Issue JWT token
        $token = JWTAuth::fromUser($user);

        $userPayload = $user->only(['id', 'name', 'email', 'uid']);

        return response()->json([
            'message' => 'Registered successfully',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'data' => $userPayload,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'revoke_others' => 'sometimes|boolean',
        ])->validate();

        $credentials = ['email' => strtolower($data['email']), 'password' => $data['password']];

        try {
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'data' => null,
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        $user = auth('api')->user();
        $userPayload = collect($user)->except(['password'])->all();

        return response()->json([
            'message' => 'Login successful',
            'uid' => $user->uid,
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'bearer',
            'access_token_expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $userPayload,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(auth('api')->user());
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // token might already be invalid/expired
        }
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(Request $request)
    {
        try {
            $newToken = auth('api')->refresh();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalid or expired'], 401);
        }

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'access_token_expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
