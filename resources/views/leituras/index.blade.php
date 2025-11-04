<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Monitoramento de Equipamentos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="{{ asset('images/icone.png') }}" type="image/png">
    <style>
        .table-responsive {
            max-height: 80vh;
        }
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: white;
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="d-flex align-items-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="Logo da Empresa" style="height: 60px; margin-right: 15px;">
        <h1 class="mb-0">Monitoramento de Equipamentos</h1>
    </div>
    @if($totalLeituras > 0)
        <p class="text-muted">Exibindo as últimas {{ $totalLeituras }} horas de dados.</p>
    @else
        <p class="text-muted">Nenhum dado para exibir. Clique em "Atualizar" para processar.</p>
    @endif

    @if(session('success'))
        <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('leituras.index') }}" class="mb-4 p-3 border rounded bg-light">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="id_cliente" class="form-label">Cliente</label>
                <select name="id_cliente" id="id_cliente" class="form-select">
                    <option value="">Todos</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente }}" {{ ($filters['id_cliente'] ?? '') == $cliente ? 'selected' : '' }}>{{ $cliente }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="id_equipamento" class="form-label">Equipamento</label>
                <select name="id_equipamento" id="id_equipamento" class="form-select">
                    <option value="">Todos</option>
                    @foreach($equipamentos as $equipamento)
                        <option value="{{ $equipamento }}" {{ ($filters['id_equipamento'] ?? '') == $equipamento ? 'selected' : '' }}>{{ $equipamento }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $filters['data_inicio'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $filters['data_fim'] ?? '' }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info">Filtrar</button>
                <a href="{{ route('leituras.index') }}" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </div>
    </form>

        <div class="mb-3">
            <form action="{{ route('leituras.agregar') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </form>
            <a href="{{ route('leituras.exportar', request()->query()) }}" class="btn btn-success">Exportar Dados Filtrados</a>
        </div>


    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle table-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th rowspan="3">Cliente ID</th>
                    <th rowspan="3">Equipamento ID</th>
                    <th rowspan="3">Período Início</th>
                    <th rowspan="3">Período Fim</th>
                    <th rowspan="3">Registros</th>
                    <th colspan="4" class="table-primary">Corrente Brunidores</th>
                    <th colspan="4" class="table-info">Corrente Descascadores</th>
                    <th colspan="4" class="table-warning">Corrente Polidores</th>
                    <th colspan="4" class="table-success">Temperatura</th>
                    <th colspan="4" class="table-danger">Umidade</th>
                    <th colspan="36" class="table-secondary">Grandezas Elétricas</th>
                    <th rowspan="3">Última Atualização</th>
                </tr>
                <tr>
                    <th class="table-primary" rowspan="2">Média</th>
                    <th class="table-primary" rowspan="2">Máx</th>
                    <th class="table-primary" rowspan="2">Mín</th>
                    <th class="table-primary" rowspan="2">Última</th>
                    
                    <th class="table-info" rowspan="2">Média</th>
                    <th class="table-info" rowspan="2">Máx</th>
                    <th class="table-info" rowspan="2">Mín</th>
                    <th class="table-info" rowspan="2">Última</th>
                    
                    <th class="table-warning" rowspan="2">Média</th>
                    <th class="table-warning" rowspan="2">Máx</th>
                    <th class="table-warning" rowspan="2">Mín</th>
                    <th class="table-warning" rowspan="2">Última</th>

                    <th class="table-success" rowspan="2">Média</th>
                    <th class="table-success" rowspan="2">Máx</th>
                    <th class="table-success" rowspan="2">Mín</th>
                    <th class="table-success" rowspan="2">Última</th>

                    <th class="table-danger" rowspan="2">Média</th>
                    <th class="table-danger" rowspan="2">Máx</th>
                    <th class="table-danger" rowspan="2">Mín</th>
                    <th class="table-danger" rowspan="2">Última</th>

                    <th colspan="4" class="table-secondary">Tensão R</th>
                    <th colspan="4" class="table-secondary">Corrente R</th>

                    <th colspan="4" class="table-light">Tensão S</th>
                    <th colspan="4" class="table-light">Corrente S</th>

                    <th colspan="4" class="table-secondary">Tensão T</th>
                    <th colspan="4" class="table-secondary">Corrente T</th>

                    <th colspan="4" class="table-warning">Potência Ativa (kW)</th>
                    <th colspan="4" class="table-info">Potência Reativa (kVAr)</th>
                    <th colspan="4" class="table-success">Fator Potência (FP)</th>
                </tr>
                <tr>
                    <th class="table-secondary">Média</th>
                    <th class="table-secondary">Máx</th>
                    <th class="table-secondary">Mín</th>
                    <th class="table-secondary">Última</th>

                    <th class="table-secondary">Média</th>
                    <th class="table-secondary">Máx</th>
                    <th class="table-secondary">Mín</th>
                    <th class="table-secondary">Última</th>

                    <th class="table-light">Média</th>
                    <th class="table-light">Máx</th>
                    <th class="table-light">Mín</th>
                    <th class="table-light">Última</th>

                    <th class="table-light">Média</th>
                    <th class="table-light">Máx</th>
                    <th class="table-light">Mín</th>
                    <th class="table-light">Última</th>

                    <th class="table-secondary">Média</th>
                    <th class="table-secondary">Máx</th>
                    <th class="table-secondary">Mín</th>
                    <th class="table-secondary">Última</th>

                    <th class="table-secondary">Média</th>
                    <th class="table-secondary">Máx</th>
                    <th class="table-secondary">Mín</th>
                    <th class="table-secondary">Última</th>

                    <th class="table-warning">Média</th>
                    <th class="table-warning">Máx</th>
                    <th class="table-warning">Mín</th>
                    <th class="table-warning">Última</th>

                    <th class="table-info">Média</th>
                    <th class="table-info">Máx</th>
                    <th class="table-info">Mín</th>
                    <th class="table-info">Última</th>

                    <th class="table-success">Média</th>
                    <th class="table-success">Máx</th>
                    <th class="table-success">Mín</th>
                    <th class="table-success">Última</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @forelse($leituras as $l)
                    <tr>
                        <td>{{ $l->id_cliente }}</td>
                        <td>{{ $l->id_equipamento }}</td>
                        <td>{{ \Carbon\Carbon::parse($l->periodo_inicio)->format('d/m H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($l->periodo_fim)->format('d/m H:i') }}</td>
                        <td>{{ $l->registros_contagem }}</td>
                        
                        <td>{{ number_format($l->corrente_brunidores_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_brunidores_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_brunidores_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_brunidores_ultima, 2) }}A</td>
                        
                        <td>{{ number_format($l->corrente_descascadores_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_descascadores_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_descascadores_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_descascadores_ultima, 2) }}A</td>
                        
                        <td>{{ number_format($l->corrente_polidores_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_polidores_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_polidores_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_polidores_ultima, 2) }}A</td>

                        <td>{{ number_format($l->temperatura_media, 2) }}°C</td>
                        <td>{{ number_format($l->temperatura_max, 2) }}°C</td>
                        <td>{{ number_format($l->temperatura_min, 2) }}°C</td>
                        <td>{{ number_format($l->temperatura_ultima, 2) }}°C</td>

                        <td>{{ number_format($l->umidade_media, 2) }}%</td>
                        <td>{{ number_format($l->umidade_max, 2) }}%</td>
                        <td>{{ number_format($l->umidade_min, 2) }}%</td>
                        <td>{{ number_format($l->umidade_ultima, 2) }}%</td>

                        <td>{{ number_format($l->tensao_r_media, 2) }}V</td>
                        <td>{{ number_format($l->tensao_r_max, 2) }}V</td>
                        <td>{{ number_format($l->tensao_r_min, 2) }}V</td>
                        <td>{{ number_format($l->tensao_r_ultima, 2) }}V</td>

                        <td>{{ number_format($l->corrente_r_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_r_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_r_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_r_ultima, 2) }}A</td>
                        
                        <td>{{ number_format($l->tensao_s_media, 2) }}V</td>
                        <td>{{ number_format($l->tensao_s_max, 2) }}V</td>
                        <td>{{ number_format($l->tensao_s_min, 2) }}V</td>
                        <td>{{ number_format($l->tensao_s_ultima, 2) }}V</td>

                        <td>{{ number_format($l->corrente_s_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_s_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_s_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_s_ultima, 2) }}A</td>

                        <td>{{ number_format($l->tensao_t_media, 2) }}V</td>
                        <td>{{ number_format($l->tensao_t_max, 2) }}V</td>
                        <td>{{ number_format($l->tensao_t_min, 2) }}V</td>
                        <td>{{ number_format($l->tensao_t_ultima, 2) }}V</td>

                        <td>{{ number_format($l->corrente_t_media, 2) }}A</td>
                        <td>{{ number_format($l->corrente_t_max, 2) }}A</td>
                        <td>{{ number_format($l->corrente_t_min, 2) }}A</td>
                        <td>{{ number_format($l->corrente_t_ultima, 2) }}A</td>

                        <td>{{ number_format($l->potencia_ativa_media, 2) }}kW</td>
                        <td>{{ number_format($l->potencia_ativa_max, 2) }}kW</td>
                        <td>{{ number_format($l->potencia_ativa_min, 2) }}kW</td>
                        <td>{{ number_format($l->potencia_ativa_ultima, 2) }}kW</td>

                        <td>{{ number_format($l->potencia_reativa_media, 2) }}kVAr</td>
                        <td>{{ number_format($l->potencia_reativa_max, 2) }}kVAr</td>
                        <td>{{ number_format($l->potencia_reativa_min, 2) }}kVAr</td>
                        <td>{{ number_format($l->potencia_reativa_ultima, 2) }}kVAr</td>

                        <td>{{ number_format($l->fator_potencia_media, 4) }}</td>
                        <td>{{ number_format($l->fator_potencia_max, 4) }}</td>
                        <td>{{ number_format($l->fator_potencia_min, 4) }}</td>
                        <td>{{ number_format($l->fator_potencia_ultima, 4) }}</td>

                        <td>{{ \Carbon\Carbon::parse($l->updated_at)->format('d/m H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="60" class="text-center">Nenhum dado agregado encontrado para os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Aguarda 3 segundos e depois remove a mensagem de sucesso
    setTimeout(function() {
        let alert = document.getElementById('success-alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 3000); // 3000 milissegundos = 3 segundos
</script>
</body>
</html>