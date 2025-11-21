<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->query('token') ?? $request->header('Authorization') ?? session('auth_token');
        $email = $request->query('email') ?? session('user_email');

        if (!$token) {
            return response()->json(['error' => 'Token não fornecido'], 401);
        }

        if ($request->query('token')) {
            session(['auth_token' => $token]);
        }

        if ($email) {
            session(['user_email' => $email]);
        } else {
            $userEmail = $this->extractEmailFromToken($token);
            if ($userEmail) {
                session(['user_email' => $userEmail]);
            }
        }

        $request->merge(['auth_token' => $token]);

        return $next($request);
    }

    private function extractEmailFromToken(string $token): ?string
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            $data = json_decode($payload, true);

            return $data['email'] ?? $data['sub'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
