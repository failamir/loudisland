<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
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
            'device_name' => $data['device_name'] ?? 'api-token',
        ]);

        $token = $user->createToken($data['device_name'] ?? 'api-token')->plainTextToken;

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
            ->select(['id', 'name', 'email', 'nik', 'password', 'no_hp'])
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'data' => null,
            ], 401);
        }

        // Optionally revoke existing tokens (default true for backward compatibility)
        // Fix: Added missing comment slashes
        // $revoke = array_key_exists('revoke_others', $data) ? (bool)$data['revoke_others'] : true;
        // if ($revoke) {
        //     $user->tokens()->delete();
        // }
        $revoke = array_key_exists('revoke_others', $data) ? (bool)$data['revoke_others'] : true;
        if ($revoke) {
            $user->tokens()->delete();
        }

        $deviceName = $data['device_name'] ?? 'api-token';
        $accessToken = $user->createToken($deviceName)->plainTextToken;

        // Issue refresh token (opaque), store only its hash
        $refreshPlain = Str::random(64);
        $refreshHash = hash('sha256', $refreshPlain);
        $refreshTtlDays = 30; // adjust as needed
        $expiresAt = Carbon::now()->addDays($refreshTtlDays);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshHash,
            'device_name' => $deviceName,
            'revoked' => false,
            'expires_at' => $expiresAt,
        ]);

        $userPayload = collect($user)->except(['password'])->all();

        $accessTtlMinutes = config('sanctum.expiration'); // minutes or null
        return response()->json([
            'message' => 'Login successful',
            // keep backward compatibility
            'token' => $accessToken,
            'access_token' => $accessToken,
            'access_token_expires_in' => $accessTtlMinutes ? $accessTtlMinutes * 60 : null,
            'refresh_token' => $refreshPlain,
            'refresh_token_expires_at' => $expiresAt->toIso8601String(),
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

    public function refresh(Request $request)
    {
        $data = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'device_name' => 'sometimes|string|max:100',
        ])->validate();

        $hash = hash('sha256', $data['refresh_token']);

        $record = RefreshToken::query()
            ->where('token', $hash)
            ->where('revoked', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            })
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        $user = User::find($record->user_id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 401);
        }

        // Rotate refresh token: revoke old, issue new
        $record->revoked = true;
        $record->save();

        $deviceName = $data['device_name'] ?? ($record->device_name ?: 'api-token');

        // Optionally revoke other access tokens for the user on this device
        // $user->tokens()->where('name', $deviceName)->delete();

        $accessToken = $user->createToken($deviceName)->plainTextToken;

        $newRefreshPlain = Str::random(64);
        $newRefreshHash = hash('sha256', $newRefreshPlain);
        $refreshTtlDays = 30;
        $expiresAt = Carbon::now()->addDays($refreshTtlDays);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $newRefreshHash,
            'device_name' => $deviceName,
            'revoked' => false,
            'expires_at' => $expiresAt,
        ]);

        $accessTtlMinutes = config('sanctum.expiration');
        return response()->json([
            'access_token' => $accessToken,
            'access_token_expires_in' => $accessTtlMinutes ? $accessTtlMinutes * 60 : null,
            'refresh_token' => $newRefreshPlain,
            'refresh_token_expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
