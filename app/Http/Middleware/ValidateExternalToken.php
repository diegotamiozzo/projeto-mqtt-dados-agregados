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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Captura os parâmetros da URL (token e dados)
        $tokenFromUrl = $request->query('token', '');
        $dataEncoded  = $request->query('data', '');

        // 2. Obtém a chave secreta do arquivo de configuração
        // (Certifique-se de ter configurado 'external_access' no config/services.php)
        $secret = config('services.external_access.token');

        // --- VALIDAÇÕES BÁSICAS ---
        
        if (empty($tokenFromUrl) || empty($dataEncoded)) {
            return response("Acesso negado: parâmetros ausentes (token ou data).", 401);
        }

        // Tenta decodificar o Base64
        $jsonData = base64_decode($dataEncoded, true);
        if ($jsonData === false) {
            return response("Acesso negado: dados Base64 inválidos.", 401);
        }

        // Converte JSON para Array associativo
        $data = json_decode($jsonData, true);
        if (!is_array($data)) {
            return response("Acesso negado: JSON inválido.", 401);
        }

        // --- SEGURANÇA (HASH HMAC) ---

        // 3. Valida a Assinatura
        // Recria o hash usando os dados recebidos + a senha local para ver se bate com o token da URL
        $expectedToken = hash_hmac('sha256', $dataEncoded, $secret);

        if (!hash_equals($expectedToken, $tokenFromUrl)) {
            return response("Acesso negado: token inválido ou assinatura incorreta.", 401);
        }

        // 4. Valida Expiração (Link válido por 5 minutos)
        if (!isset($data['timestamp']) || (time() - intval($data['timestamp'])) > 300) {
            return response("Acesso expirado (link antigo). Atualize a página anterior.", 401);
        }

        // --- LÓGICA PRINCIPAL: LOGIN OU CRIAÇÃO (AUTO-CADASTRO) ---

        if (isset($data['email'])) {
            // Tenta encontrar o usuário pelo e-mail
            $user = User::where('email', $data['email'])->first();

            // Recupera o ID do cliente que veio do App 1 (scope_id)
            $externalClientId = $data['scope_id'] ?? null;

            if ($user) {
                // CENÁRIO A: Usuário JÁ EXISTE
                // Atualizamos o nome e o vínculo do cliente para garantir que ele veja os dados certos
                $user->update([
                    'name' => $data['name'] ?? $user->name,
                    'external_client_id' => $externalClientId
                ]);
            } else {
                // CENÁRIO B: Usuário NÃO EXISTE (Aqui estava o seu erro)
                // Criamos o usuário na hora para não barrar o acesso!
                try {
                    $user = User::create([
                        'name'     => $data['name'] ?? 'Utilizador Externo',
                        'email'    => $data['email'],
                        'password' => bcrypt(Str::random(32)), // Gera senha aleatória forte
                        'external_client_id' => $externalClientId // Vincula ao cliente correto
                    ]);
                } catch (\Exception $e) {
                    return response("Erro ao criar utilizador automático: " . $e->getMessage(), 500);
                }
            }

            // Realiza o login forçado no Laravel
            Auth::login($user);
        }

        // Opcional: Passa os dados brutos para o Controller
        $request->attributes->set('userData', $data);

        // Deixa a requisição passar para o Controller
        return $next($request);
    }
}