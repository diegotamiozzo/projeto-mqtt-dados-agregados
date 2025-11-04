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
    <h1 class="mb-4">Monitoramento de Equipamentos</h1>
    @if($totalLeituras > 0)
        <p class="text-muted">Exibindo as últimas {{ $totalLeituras }} agregações de dados.</p>
    @else
        <p class="text-muted">Nenhum dado para exibir. Clique em "Atualizar" para processar.</p>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

        <form action="{{ route('leituras.agregar') }}" method="POST" class="mb-4 d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                Atualizar 
            </button>
        </form>


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
                        <td colspan="44" class="text-center">Nenhum dado agregado encontrado. Clique em "Atualizar" para processar novos dados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>