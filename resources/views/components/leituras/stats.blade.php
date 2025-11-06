@props(['leituras', 'colunasVisiveis'])

@php
    $stats = [
        'brunidores' => ['min' => null, 'max' => null, 'avg' => null],
        'descascadores' => ['min' => null, 'max' => null, 'avg' => null],
        'polidores' => ['min' => null, 'max' => null, 'avg' => null],
        'temperatura' => ['min' => null, 'max' => null, 'avg' => null],
        'umidade' => ['min' => null, 'max' => null, 'avg' => null],
        'tensao_r' => ['min' => null, 'max' => null, 'avg' => null],
        'tensao_s' => ['min' => null, 'max' => null, 'avg' => null],
        'tensao_t' => ['min' => null, 'max' => null, 'avg' => null],
        'corrente_r' => ['min' => null, 'max' => null, 'avg' => null],
        'corrente_s' => ['min' => null, 'max' => null, 'avg' => null],
        'corrente_t' => ['min' => null, 'max' => null, 'avg' => null],
        'potencia_ativa' => ['min' => null, 'max' => null, 'avg' => null],
        'potencia_reativa' => ['min' => null, 'max' => null, 'avg' => null],
        'fator_potencia' => ['min' => null, 'max' => null, 'avg' => null],
    ];

    $values = [
        'brunidores' => [],
        'descascadores' => [],
        'polidores' => [],
        'temperatura' => [],
        'umidade' => [],
        'tensao_r' => [],
        'tensao_s' => [],
        'tensao_t' => [],
        'corrente_r' => [],
        'corrente_s' => [],
        'corrente_t' => [],
        'potencia_ativa' => [],
        'potencia_reativa' => [],
        'fator_potencia' => [],
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
        if (!is_null($leitura->tensao_r_media)) {
            $values['tensao_r'][] = $leitura->tensao_r_media;
        }
        if (!is_null($leitura->tensao_s_media)) {
            $values['tensao_s'][] = $leitura->tensao_s_media;
        }
        if (!is_null($leitura->tensao_t_media)) {
            $values['tensao_t'][] = $leitura->tensao_t_media;
        }
        if (!is_null($leitura->corrente_r_media)) {
            $values['corrente_r'][] = $leitura->corrente_r_media;
        }
        if (!is_null($leitura->corrente_s_media)) {
            $values['corrente_s'][] = $leitura->corrente_s_media;
        }
        if (!is_null($leitura->corrente_t_media)) {
            $values['corrente_t'][] = $leitura->corrente_t_media;
        }
        if (!is_null($leitura->potencia_ativa_media)) {
            $values['potencia_ativa'][] = $leitura->potencia_ativa_media;
        }
        if (!is_null($leitura->potencia_reativa_media)) {
            $values['potencia_reativa'][] = $leitura->potencia_reativa_media;
        }
        if (!is_null($leitura->fator_potencia_media)) {
            $values['fator_potencia'][] = $leitura->fator_potencia_media;
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

    @if($colunasVisiveis['grandezas_eletricas'])
        @if(!empty($values['tensao_r']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Tensão R (V)</div>
                        <div class="stat-value" style="color: rgb(239, 68, 68);">{{ number_format($stats['tensao_r']['avg'], 1, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['tensao_r']['min'], 1, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['tensao_r']['max'], 1, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['tensao_s']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Tensão S (V)</div>
                        <div class="stat-value" style="color: rgb(245, 158, 11);">{{ number_format($stats['tensao_s']['avg'], 1, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['tensao_s']['min'], 1, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['tensao_s']['max'], 1, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['tensao_t']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Tensão T (V)</div>
                        <div class="stat-value" style="color: rgb(59, 130, 246);">{{ number_format($stats['tensao_t']['avg'], 1, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['tensao_t']['min'], 1, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['tensao_t']['max'], 1, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['corrente_r']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Corrente R (A)</div>
                        <div class="stat-value" style="color: rgb(239, 68, 68);">{{ number_format($stats['corrente_r']['avg'], 2, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['corrente_r']['min'], 2, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['corrente_r']['max'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['corrente_s']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Corrente S (A)</div>
                        <div class="stat-value" style="color: rgb(245, 158, 11);">{{ number_format($stats['corrente_s']['avg'], 2, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['corrente_s']['min'], 2, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['corrente_s']['max'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['corrente_t']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Corrente T (A)</div>
                        <div class="stat-value" style="color: rgb(59, 130, 246);">{{ number_format($stats['corrente_t']['avg'], 2, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['corrente_t']['min'], 2, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['corrente_t']['max'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['potencia_ativa']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Potência Ativa (kW)</div>
                        <div class="stat-value" style="color: rgb(16, 185, 129);">{{ number_format($stats['potencia_ativa']['avg'], 2, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['potencia_ativa']['min'], 2, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['potencia_ativa']['max'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['potencia_reativa']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Potência Reativa (kVAr)</div>
                        <div class="stat-value" style="color: rgb(168, 85, 247);">{{ number_format($stats['potencia_reativa']['avg'], 2, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['potencia_reativa']['min'], 2, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['potencia_reativa']['max'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($values['fator_potencia']))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Fator de Potência</div>
                        <div class="stat-value" style="color: rgb(99, 102, 241);">{{ number_format($stats['fator_potencia']['avg'], 3, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Min: {{ number_format($stats['fator_potencia']['min'], 3, ',', '.') }}</small>
                        <small class="text-muted d-block">Max: {{ number_format($stats['fator_potencia']['max'], 3, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
