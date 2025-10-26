<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class PasswordResetApiController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Find user by email
        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return response()->json([
                'message' => __('passwords.user'),
                'status' => 'error',
            ], 422);
        }

        // Create a single token, send default email notification, and WA (best-effort)
        $token = Password::createToken($user);
        // Send default Laravel reset email
        try {
            $user->sendPasswordResetNotification($token);
        } catch (\Throwable $e) {
            // ignore email failure; caller still gets status ok below to avoid enumeration timing
        }

        // Best-effort WhatsApp notification if user has phone
        try {
            if (!empty($user->no_hp)) {
                $appUrl = rtrim(config('app.url'), '/');
                $resetUrl = $appUrl . '/reset-password/' . $token . '?email=' . urlencode($user->email);
                $appName = config('app.name', 'Application');

                // Normalize phone to 62 format
                $raw = preg_replace('/[^0-9+]/', '', (string) $user->no_hp);
                if (strpos($raw, '+') === 0) {
                    $raw = substr($raw, 1);
                }
                if (strpos($raw, '0') === 0) {
                    $chatId = '62' . substr($raw, 1);
                } elseif (strpos($raw, '62') === 0) {
                    $chatId = $raw;
                } else {
                    // Fallback assume Indonesia if missing
                    $chatId = '62' . $raw;
                }

                $baseUrl = rtrim(config('services.waha.base_url'), '/');
                $apiKey = config('services.waha.api_key');
                $session = config('services.waha.session');
                if ($baseUrl && $apiKey && $session) {
                    $text = "{$appName}: Link reset password Anda: {$resetUrl}";
                    Http::withHeaders([
                        'x-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(15)->connectTimeout(10)
                      ->post($baseUrl . '/api/sendText', [
                          'session' => $session,
                          'chatId' => $chatId,
                          'text' => $text,
                      ]);
                }
            }
        } catch (\Throwable $e) {
            // Silent fail to not affect API success
        }

        return response()->json([
            'message' => __('passwords.sent'),
            'status' => 'ok',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
                'status' => 'ok',
            ]);
        }

        return response()->json([
            'message' => __($status),
            'status' => 'error',
        ], 422);
    }
}
