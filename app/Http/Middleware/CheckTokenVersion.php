<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckTokenVersion
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['message' => 'Token tidak ditemukan'], 401);
            }
            $payload = JWTAuth::getPayload($token);
            $tokenVersion = (int) ($payload->get('tv') ?? 0);

            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'User tidak terautentikasi'], 401);
            }
            // Treat missing column as 0
            $currentVersion = (int) ($user->token_version ?? 0);

            if ($tokenVersion !== $currentVersion) {
                return response()->json(['message' => 'Token telah dicabut'], 401);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        }

        return $next($request);
    }
}
