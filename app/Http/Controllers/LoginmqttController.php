<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthLinkService;

class LoginmqttController extends Controller
{
    public function index(Request $request)
    {
        $tokenFromUrl  = $request->input('token', '');
        $localToken    = env('ACCESS_TOKEN', '');
        $accessGranted = false;
        $errorMessage  = '';
        $userData      = null;

        // Validação do token
        if (empty($tokenFromUrl)) {
            $errorMessage = 'Token não fornecido.';
        } elseif ($tokenFromUrl !== $localToken) {
            $errorMessage = 'Token inválido.';
        } else {
            $userData = AuthLinkService::decode($request->input('data'));

            if ($userData) {
                session([
                    'user_email' => $userData['email'],
                    'user_name'  => $userData['name'] ?? 'Usuário',
                    'auth_token' => $tokenFromUrl
                ]);
                $accessGranted = true;
            } else {
                $errorMessage = 'Dados inválidos ou link expirado.';
            }
        }

        // Inicialização de variáveis para a view
        $leituras       = collect([]);
        $clientes       = [];
        $equipamentos   = collect([]);
        $colunasVisiveis = [
            'brunidores' => true,
            'descascadores' => true,
            'polidores' => true,
            'temperatura' => true,
            'umidade' => true,
            'grandezas_eletricas' => true
        ];
        $disponibilidade = [
            'brunidores' => 0,
            'descascadores' => 0,
            'polidores' => 0
        ];
        $nomeEquipamento = 'Equipamento Selecionado';

        // Retorna a view com todos os dados
        return view('dashboard.index', compact(
            'accessGranted', 'errorMessage', 'userData',
            'leituras', 'clientes', 'equipamentos',
            'colunasVisiveis', 'disponibilidade', 'nomeEquipamento'
        ));
    }
}