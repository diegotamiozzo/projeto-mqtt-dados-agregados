<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class ValidateExternalToken
{
    public function handle(Request $request, Closure $next)
    {
        $tokenFromUrl = $request->query('token', '');
        $dataEncoded  = $request->query('data', '');

        // --- Token obrigatório ---
        if (empty($tokenFromUrl) || empty($dataEncoded)) {
            // Se usuário já logado, deixa passar
            if (Auth::check() && Auth::user()->external_client_id) {
                return $next($request);
            }
            return response("Acesso negado: parâmetros ausentes (token ou data).", 401);
        }

        // --- Decodifica dados ---
        $jsonData = base64_decode($dataEncoded, true);
        $data = json_decode($jsonData, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return response("Acesso negado: JSON inválido.", 401);
        }

        // --- Valida HMAC ---
        $secret = config('services.external_access.token');
        $expectedToken = hash_hmac('sha256', $dataEncoded, $secret);
        if (!hash_equals($expectedToken, $tokenFromUrl)) {
            return response("Acesso negado: token inválido ou assinatura incorreta.", 401);
        }

        // --- Valida expiração ---
        if (!isset($data['timestamp']) || (time() - intval($data['timestamp'])) > 300) {
            return response("Acesso expirado (link antigo). Atualize a página anterior.", 401);
        }

        // --- Procura ou cria usuário ---
        if (isset($data['email'])) {
            $user = User::where('email', $data['email'])->first();
            $externalClientId = $data['scope_id'] ?? null;

            if ($user) {
                $user->name = $data['name'] ?? $user->name;
                if ($externalClientId) {
                    $user->external_client_id = $externalClientId;
                }
                $user->save();
            } else {
                $user = User::create([
                    'name' => $data['name'] ?? 'Usuário Externo',
                    'email' => $data['email'],
                    'password' => bcrypt(Str::random(32)),
                    'external_client_id' => $externalClientId
                ]);
            }

            // --- Troca de usuário logado se necessário ---
            if (!Auth::check() || Auth::id() !== $user->id) {
                Auth::logout(); // garante logout completo do antigo
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Auth::login($user, false); // login temporário
                $request->session()->regenerate(); // nova sessão limpa
            }
        }

        // --- Passa dados para controller ---
        $request->attributes->set('userData', $data);

        return $next($request);
    }
}
