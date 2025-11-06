@props(['leituras', 'colunasVisiveis'])

@php
    $stats = [
        'brunidores' => ['min' => null, 'max' => null, 'avg' => null],
        'descascadores' => ['min' => null, 'max' => null, 'avg' => null],
        'polidores' => ['min' => null, 'max' => null, 'avg' => null],
        'temperatura' => ['min' => null, 'max' => null, 'avg' => null],
        'umidade' => ['min' => null, 'max' => null, 'avg' => null],
    ];

    $values = [
        'brunidores' => [],
        'descascadores' => [],
        'polidores' => [],
        'temperatura' => [],
        'umidade' => [],
    ];

    foreach($leituras as $leitura) {
        if (!is_null($leitura->corrente_brunidores_media)) {
            $values['brunidores'][] = $leitura->corrente_brunidores_media;
        }
        if (!is_null($leitura->corrente_descascadores_media)) {
            $values['descascadores'][] = $leitura->corrente_descascadores_media;
        }
        if (!is_null($leitura->corrente_polidores_media)) {
            $values['polidores'][] = $leitura->corrente_polidores_media;
        }
        if (!is_null($leitura->temperatura_media)) {
            $values['temperatura'][] = $leitura->temperatura_media;
        }
        if (!is_null($leitura->umidade_media)) {
            $values['umidade'][] = $leitura->umidade_media;
        }
    }

    foreach($values as $key => $arr) {
        if (!empty($arr)) {
            $stats[$key]['min'] = min($arr);
            $stats[$key]['max'] = max($arr);
            $stats[$key]['avg'] = array_sum($arr) / count($arr);
        }
    }
@endphp

<div class="row mb-4">
    @if($colunasVisiveis['brunidores'])
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Brunidores (A)</div>
                    <div class="stat-value text-primary">{{ number_format($stats['brunidores']['avg'], 2, ',', '.') }}</div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Min: {{ number_format($stats['brunidores']['min'], 2, ',', '.') }}</small>
                    <small class="text-muted d-block">Max: {{ number_format($stats['brunidores']['max'], 2, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['descascadores'])
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Descascadores (A)</div>
                    <div class="stat-value text-success">{{ number_format($stats['descascadores']['avg'], 2, ',', '.') }}</div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Min: {{ number_format($stats['descascadores']['min'], 2, ',', '.') }}</small>
                    <small class="text-muted d-block">Max: {{ number_format($stats['descascadores']['max'], 2, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['polidores'])
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Polidores (A)</div>
                    <div class="stat-value text-warning">{{ number_format($stats['polidores']['avg'], 2, ',', '.') }}</div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Min: {{ number_format($stats['polidores']['min'], 2, ',', '.') }}</small>
                    <small class="text-muted d-block">Max: {{ number_format($stats['polidores']['max'], 2, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['temperatura'])
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Temperatura (°C)</div>
                    <div class="stat-value text-danger">{{ number_format($stats['temperatura']['avg'], 1, ',', '.') }}</div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Min: {{ number_format($stats['temperatura']['min'], 1, ',', '.') }}</small>
                    <small class="text-muted d-block">Max: {{ number_format($stats['temperatura']['max'], 1, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['umidade'])
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Umidade (%)</div>
                    <div class="stat-value text-info">{{ number_format($stats['umidade']['avg'], 1, ',', '.') }}</div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Min: {{ number_format($stats['umidade']['min'], 1, ',', '.') }}</small>
                    <small class="text-muted d-block">Max: {{ number_format($stats['umidade']['max'], 1, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
