<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
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
            // 'uid' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:50',
            'device_name' => 'sometimes|string|max:100',
            // client does not need to send id_token; server will create Firebase user
            'id_token' => 'sometimes|string',
        ])->validate();

        // Normalize email for consistency
        $email = strtolower($data['email']);

        // Create Firebase user (email/password) via Identity Toolkit signUp to obtain UID
        $firebaseUid = null;
        $firebaseIdToken = null; // can be used for DB sync
        try {
            $apiKey = env('FIREBASE_WEB_API_KEY');
            if (!$apiKey) {
                return response()->json([
                    'message' => 'Server misconfiguration: FIREBASE_WEB_API_KEY is not set',
                    'data' => null,
                ], 500);
            }

            $client = new Client(['timeout' => 8]);
            $signUpUrl = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . urlencode($apiKey);
            $resp = $client->request('POST', $signUpUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'email' => $email,
                    'password' => $data['password'],
                    'returnSecureToken' => true,
                ]),
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            if (!isset($body['localId'])) {
                return response()->json([
                    'message' => 'Failed to create Firebase user',
                    'data' => null,
                ], 502);
            }
            $firebaseUid = $body['localId'];
            $firebaseIdToken = $body['idToken'] ?? null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Parse Firebase error
            $responseBody = (string) $e->getResponse()->getBody();
            $err = json_decode($responseBody, true);
            $code = $err['error']['message'] ?? '';
            if ($code === 'EMAIL_EXISTS') {
                return response()->json([
                    'message' => 'Email already registered on Firebase',
                    'data' => null,
                ], 400);
            }
            Log::warning('Firebase signUp failed: ' . $responseBody);
            return response()->json([
                'message' => 'Firebase sign up failed',
                'data' => null,
            ], 502);
        } catch (\Throwable $e) {
            Log::warning('Firebase signUp exception: ' . $e->getMessage());
            return response()->json([
                'message' => 'Unable to create Firebase user',
                'data' => null,
            ], 502);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password']),
            'nik' => $data['nik'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'uid' => $firebaseUid,
        ]);

        // Try to sync to Firebase Realtime Database via REST API
        try {
            $dbUrl = rtrim(env('FIREBASE_DATABASE_URL', ''), '/');
            if ($dbUrl) {
                // Use freshly issued Firebase ID token if present, fallback to database secret if configured
                $authToken = $firebaseIdToken ?? env('FIREBASE_DATABASE_SECRET');
                $endpoint = $dbUrl . '/users/' . rawurlencode($firebaseUid) . '.json';
                if ($authToken) {
                    $endpoint .= '?auth=' . urlencode($authToken);
                }

                $payload = [
                    'uid' => $firebaseUid,
                    'name' => $data['name'],
                    'displayName' => $data['name'],
                    'email' => $email,
                    'nik' => $data['nik'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'createdAt' => now()->toISOString(),
                ];

                $client = new Client(['timeout' => 5]);
                $client->request('PUT', $endpoint, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ]);
            } else {
                Log::warning('FIREBASE_DATABASE_URL not set. Skipping Firebase sync for user UID ' . $firebaseUid);
            }
        } catch (\Throwable $e) {
            Log::warning('Firebase REST sync failed for user UID ' . $firebaseUid . ': ' . $e->getMessage());
        }

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
            'data' => $userPayload,
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
