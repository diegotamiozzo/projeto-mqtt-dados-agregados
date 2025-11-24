<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class ValidateExternalToken
{
    /**
     * Manipula a requisição de entrada.
     */
    public function handle(Request $request, Closure $next)
    {
        // Captura parâmetros da URL
        $tokenFromUrl = $request->query('token');
        $dataEncoded  = $request->query('data');

        // ---------------------------------------------------------------------
        // CENÁRIO 1: O usuário enviou um TOKEN (Link novo ou troca de cliente)
        // ---------------------------------------------------------------------
        if ($tokenFromUrl && $dataEncoded) {
            
            $secret = config('services.external_access.token');

            // 1. Decodifica e Valida JSON
            $jsonData = base64_decode($dataEncoded, true);
            if ($jsonData === false) return response("Dados inválidos.", 401);

            $data = json_decode($jsonData, true);
            if (!is_array($data)) return response("JSON inválido.", 401);

            // 2. Valida Assinatura (Segurança)
            $expectedToken = hash_hmac('sha256', $dataEncoded, $secret);
            if (!hash_equals($expectedToken, $tokenFromUrl)) {
                return response("Token inválido.", 401);
            }

            // 3. Valida Expiração do Link (5 min)
            if (!isset($data['timestamp']) || (time() - intval($data['timestamp'])) > 300) {
                return response("Link expirado. Atualize a página de origem.", 401);
            }

            // 4. Lógica de Login / Criação
            if (isset($data['email'])) {
                $user = User::where('email', $data['email'])->first();
                $externalClientId = $data['scope_id'] ?? null;

                if ($user) {
                    // Atualiza vínculo do cliente
                    $user->update([
                        'name' => $data['name'] ?? $user->name,
                        'external_client_id' => $externalClientId
                    ]);
                } else {
                    // Cria usuário novo
                    try {
                        $user = User::create([
                            'name'     => $data['name'] ?? 'Usuário Externo',
                            'email'    => $data['email'],
                            'password' => bcrypt(Str::random(32)),
                            'external_client_id' => $externalClientId
                        ]);
                    } catch (\Exception $e) {
                        return response("Erro ao criar usuário: " . $e->getMessage(), 500);
                    }
                }

                // Faz login e inicia a SESSÃO
                Auth::login($user);
            }
            
            // Passa dados e continua
            $request->attributes->set('userData', $data);
            return $next($request);
        }

        // ---------------------------------------------------------------------
        // CENÁRIO 2: Sem token, mas já está LOGADO (Refresh da página)
        // ---------------------------------------------------------------------
        if (Auth::check()) {
            // Se o usuário já tem uma sessão válida no Laravel, deixamos passar.
            // Isso resolve o problema da atualização automática.
            return $next($request);
        }

        // ---------------------------------------------------------------------
        // CENÁRIO 3: Sem token e sem sessão (Acesso Negado)
        // ---------------------------------------------------------------------
        return response("Acesso negado: parâmetros ausentes (token ou data) e sessão expirada.", 401);
    }
}