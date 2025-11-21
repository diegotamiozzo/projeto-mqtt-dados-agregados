@php
// --- LÓGICA DE AUTENTICAÇÃO E INICIALIZAÇÃO ---
$tokenFromUrl = request('token', '');
$dataFromUrl  = request('data', '');
$localToken   = env('ACCESS_TOKEN', '');

$accessGranted = false;
$errorMessage  = '';
$userData      = null;
$maxAge        = 300; // 5 minutos

// 1. Validação de Acesso
if (empty($tokenFromUrl)) {
    $errorMessage = 'Token não fornecido. Acesso não autorizado.';
} elseif ($tokenFromUrl !== $localToken) {
    $errorMessage = 'Token inválido. Acesso não autorizado.';
} else {
    try {
        if (!empty($dataFromUrl)) {
            $decodedData = base64_decode($dataFromUrl, true);
            $userData = json_decode($decodedData, true);
            
            // Persistência na sessão para navegação
            if ($userData && isset($userData['email'])) {
                session(['user_email' => $userData['email']]);
                session(['user_name' => $userData['name'] ?? 'Usuário']);
                session(['auth_token' => $tokenFromUrl]);
            }
        } else {
            // Recupera da sessão se não vier na URL
            $userData = [
                'email' => session('user_email'),
                'name' => session('user_name'),
                'timestamp' => time()
            ];
        }

        if (!$userData || !isset($userData['email'])) {
            $errorMessage = 'Sessão expirada ou dados inválidos.';
        } else {
            $accessGranted = true;
        }
    } catch (Exception $e) {
        $errorMessage = 'Erro ao processar dados.';
    }
}

// 2. Inicialização de Variáveis (Evita erro "Undefined Variable")
$leituras       = $leituras ?? collect([]);
$clientes       = $clientes ?? [];
$equipamentos   = $equipamentos ?? collect([]);

$colunasVisiveis = $colunasVisiveis ?? [
    'brunidores' => true,
    'descascadores' => true,
    'polidores' => true,
    'temperatura' => true,
    'umidade' => true,
    'grandezas_eletricas' => true
];

$disponibilidade = $disponibilidade ?? [
    'brunidores' => 0,
    'descascadores' => 0,
    'polidores' => 0
];

$nomeEquipamento = $nomeEquipamento ?? 'Equipamento Selecionado';
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel de Leituras MQTT</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' }
            }
        }
    }
}
</script>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }

/* Ajustes para coexistência Bootstrap + Tailwind */
.table-responsive { overflow-x: auto; }
a { text-decoration: none; }
.btn { display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>

<body class="antialiased min-h-screen flex flex-col">

@if(!$accessGranted)
<!-- TELA DE ACESSO NEGADO -->
<div class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border-top border-4 border-danger text-center">
        <div class="bg-red-100 rounded-full p-4 d-inline-flex mb-4">
            <i class="fas fa-lock text-danger fs-1"></i>
        </div>
        <h1 class="h3 fw-bold text-dark mb-2">Acesso Negado</h1>
        <p class="text-danger fw-medium mb-4">{{ $errorMessage }}</p>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
    </div>
</div>
@else
<!-- DASHBOARD COMPLETO -->
<div class="flex-grow py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- HEADER DO USUÁRIO -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Painel de Monitoramento</h2>
                    <p class="text-sm text-gray-600"><i class="fas fa-envelope mr-1"></i> {{ $userData['email'] }}</p>
                </div>
            </div>
            <div class="text-center sm:text-right">
                <span class="badge bg-primary fs-6">Usuário: {{ $userData['name'] }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            
            <!-- COLUNA 1: FILTROS -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-200 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter text-primary-600 mr-2"></i> Filtros
                    </h3>

                    <form method="GET" action="" class="space-y-4">
                        <input type="hidden" name="token" value="{{ request('token') ?? session('auth_token') }}">
                        @if(request('email') || session('user_email'))
                            <input type="hidden" name="email" value="{{ request('email') ?? session('user_email') }}">
                        @endif

                        <div>
                            <label for="id_cliente" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                            <select name="id_cliente" id="id_cliente" onchange="this.form.submit()" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Selecione o Cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente }}" {{ request('id_cliente') == $cliente ? 'selected' : '' }}>{{ $cliente }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="id_equipamento" class="block text-sm font-medium text-gray-700 mb-1">Equipamento</label>
                            <select name="id_equipamento" id="id_equipamento" onchange="this.form.submit()" class="form-select w-full rounded-md border-gray-300 shadow-sm" {{ $equipamentos->isEmpty() ? 'disabled' : '' }}>
                                <option value="">Selecione o Equipamento</option>
                                @foreach($equipamentos as $equipamento)
                                    <option value="{{ $equipamento }}" {{ request('id_equipamento') == $equipamento ? 'selected' : '' }}>{{ $equipamento }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Início</label>
                                <input type="datetime-local" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="form-control text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fim</label>
                                <input type="datetime-local" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="form-control text-sm">
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 pt-3">
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-search me-2"></i> Buscar Dados
                            </button>
                            <a href="?token={{ request('token') ?? session('auth_token') }}" class="btn btn-outline-secondary w-full">
                                <i class="fas fa-eraser me-2"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- COLUNA 2: CONTEÚDO -->
            <div class="xl:col-span-3 space-y-6">
                
                @if($leituras->isNotEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 flex justify-between items-center">
                        <h4 class="text-base font-semibold text-gray-700">Ações Rápidas</h4>
                        <div>
                            <x-leituras.actions />
                        </div>
                    </div>

                    <div class="w-full">
                        <x-leituras.stats :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" :disponibilidade="$disponibilidade" />
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-1 border border-gray-200">
                        <x-leituras.charts :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" :nomeEquipamento="$nomeEquipamento" />
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Detalhamento dos Registros</h3>
                        <x-leituras.table :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm p-12 border border-gray-200 text-center">
                        <div class="bg-gray-50 rounded-full p-6 inline-flex mb-4">
                            <i class="fas fa-chart-bar text-gray-400 text-5xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Nenhum dado para exibir</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Utilize os filtros ao lado para selecionar um cliente, equipamento e período para visualizar as leituras.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Scripts Globais -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dataInicioInput = document.getElementById('data_inicio');
const dataFimInput = document.getElementById('data_fim');

if (dataInicioInput && dataFimInput) {
    dataInicioInput.addEventListener('change', function() {
        if (dataFimInput.value && this.value > dataFimInput.value) {
            alert('A data de início não pode ser posterior à data fim.');
            this.value = '';
        }
    });
}
</script>
@endif
</body>
</html>
