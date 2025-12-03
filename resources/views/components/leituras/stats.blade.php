@props(['leituras', 'colunasVisiveis', 'disponibilidade'])

@php
    $stats = [
        'brunidores' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'blue'],
        'descascadores' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'green'],
        'polidores' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'amber'],
        'temperatura' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'red'],
        'umidade' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'cyan'],
        'tensao_r' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'red'],
        'tensao_s' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'amber'],
        'tensao_t' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'blue'],
        'corrente_r' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'red'],
        'corrente_s' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'amber'],
        'corrente_t' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'blue'],
        'potencia_ativa' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'green'],
        'potencia_reativa' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'purple'],
        'potencia_aparente' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'indigo'],
        'fator_potencia' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'primary'],
    ];

    $values = [
        'brunidores_min' => [], 'brunidores_max' => [], 'brunidores_avg' => [],
        'descascadores_min' => [], 'descascadores_max' => [], 'descascadores_avg' => [],
        'polidores_min' => [], 'polidores_max' => [], 'polidores_avg' => [],
        'temperatura_min' => [], 'temperatura_max' => [], 'temperatura_avg' => [],
        'umidade_min' => [], 'umidade_max' => [], 'umidade_avg' => [],
        'tensao_r_min' => [], 'tensao_r_max' => [], 'tensao_r_avg' => [],
        'tensao_s_min' => [], 'tensao_s_max' => [], 'tensao_s_avg' => [],
        'tensao_t_min' => [], 'tensao_t_max' => [], 'tensao_t_avg' => [],
        'corrente_r_min' => [], 'corrente_r_max' => [], 'corrente_r_avg' => [],
        'corrente_s_min' => [], 'corrente_s_max' => [], 'corrente_s_avg' => [],
        'corrente_t_min' => [], 'corrente_t_max' => [], 'corrente_t_avg' => [],
        'potencia_ativa_min' => [], 'potencia_ativa_max' => [], 'potencia_ativa_avg' => [],
        'potencia_reativa_min' => [], 'potencia_reativa_max' => [], 'potencia_reativa_avg' => [],
        'potencia_aparente_min' => [], 'potencia_aparente_max' => [], 'potencia_aparente_avg' => [],
        'fator_potencia_min' => [], 'fator_potencia_max' => [], 'fator_potencia_avg' => [],
    ];

    $ultimaLeitura = $leituras->first();

foreach($leituras as $leitura) {
        if ($colunasVisiveis['brunidores']) {
            if (!is_null($leitura->corrente_brunidores_min) && $leitura->corrente_brunidores_min > 0) $values['brunidores_min'][] = $leitura->corrente_brunidores_min;
            if (!is_null($leitura->corrente_brunidores_max) && $leitura->corrente_brunidores_max > 0) $values['brunidores_max'][] = $leitura->corrente_brunidores_max;
            if (!is_null($leitura->corrente_brunidores_media) && $leitura->corrente_brunidores_media > 0) $values['brunidores_avg'][] = $leitura->corrente_brunidores_media;
        }
        if ($colunasVisiveis['descascadores']) {
            if (!is_null($leitura->corrente_descascadores_min) && $leitura->corrente_descascadores_min > 0) $values['descascadores_min'][] = $leitura->corrente_descascadores_min;
            if (!is_null($leitura->corrente_descascadores_max) && $leitura->corrente_descascadores_max > 0) $values['descascadores_max'][] = $leitura->corrente_descascadores_max;
            if (!is_null($leitura->corrente_descascadores_media) && $leitura->corrente_descascadores_media > 0) $values['descascadores_avg'][] = $leitura->corrente_descascadores_media;
        }
        if ($colunasVisiveis['polidores']) {
            if (!is_null($leitura->corrente_polidores_min) && $leitura->corrente_polidores_min > 0) $values['polidores_min'][] = $leitura->corrente_polidores_min;
            if (!is_null($leitura->corrente_polidores_max) && $leitura->corrente_polidores_max > 0) $values['polidores_max'][] = $leitura->corrente_polidores_max;
            if (!is_null($leitura->corrente_polidores_media) && $leitura->corrente_polidores_media > 0) $values['polidores_avg'][] = $leitura->corrente_polidores_media;
        }
        if ($colunasVisiveis['temperatura']) {
            if (!is_null($leitura->temperatura_min) && $leitura->temperatura_min > 0) $values['temperatura_min'][] = $leitura->temperatura_min;
            if (!is_null($leitura->temperatura_max) && $leitura->temperatura_max > 0) $values['temperatura_max'][] = $leitura->temperatura_max;
            if (!is_null($leitura->temperatura_media) && $leitura->temperatura_media > 0) $values['temperatura_avg'][] = $leitura->temperatura_media;
        }
        if ($colunasVisiveis['umidade']) {
            if (!is_null($leitura->umidade_min) && $leitura->umidade_min > 0) $values['umidade_min'][] = $leitura->umidade_min;
            if (!is_null($leitura->umidade_max) && $leitura->umidade_max > 0) $values['umidade_max'][] = $leitura->umidade_max;
            if (!is_null($leitura->umidade_media) && $leitura->umidade_media > 0) $values['umidade_avg'][] = $leitura->umidade_media;
        }
        if ($colunasVisiveis['grandezas_eletricas']) {
            foreach(['tensao_r', 'tensao_s', 'tensao_t', 'corrente_r', 'corrente_s', 'corrente_t', 'potencia_ativa', 'potencia_reativa', 'potencia_aparente', 'fator_potencia'] as $field) {
                if (!is_null($leitura->{$field.'_min'}) && $leitura->{$field.'_min'} > 0) $values[$field.'_min'][] = $leitura->{$field.'_min'};
                if (!is_null($leitura->{$field.'_max'}) && $leitura->{$field.'_max'} > 0) $values[$field.'_max'][] = $leitura->{$field.'_max'};
                if (!is_null($leitura->{$field.'_media'}) && $leitura->{$field.'_media'} > 0) $values[$field.'_avg'][] = $leitura->{$field.'_media'};
            }
        }
    }

    foreach($stats as $key => $stat) {
        if (!empty($values[$key.'_min'])) {
            $stats[$key]['min'] = min($values[$key.'_min']);
        }
        if (!empty($values[$key.'_max'])) {
            $stats[$key]['max'] = max($values[$key.'_max']);
        }
        if (!empty($values[$key.'_avg'])) {
            $stats[$key]['avg'] = array_sum($values[$key.'_avg']) / count($values[$key.'_avg']);
        }
    }

    if ($ultimaLeitura) {
        $stats['brunidores']['last'] = $ultimaLeitura->corrente_brunidores_media;
        $stats['descascadores']['last'] = $ultimaLeitura->corrente_descascadores_media;
        $stats['polidores']['last'] = $ultimaLeitura->corrente_polidores_media;
        $stats['temperatura']['last'] = $ultimaLeitura->temperatura_media;
        $stats['umidade']['last'] = $ultimaLeitura->umidade_media;
        $stats['tensao_r']['last'] = $ultimaLeitura->tensao_r_media;
        $stats['tensao_s']['last'] = $ultimaLeitura->tensao_s_media;
        $stats['tensao_t']['last'] = $ultimaLeitura->tensao_t_media;
        $stats['corrente_r']['last'] = $ultimaLeitura->corrente_r_media;
        $stats['corrente_s']['last'] = $ultimaLeitura->corrente_s_media;
        $stats['corrente_t']['last'] = $ultimaLeitura->corrente_t_media;
        $stats['potencia_ativa']['last'] = $ultimaLeitura->potencia_ativa_media;
        $stats['potencia_reativa']['last'] = $ultimaLeitura->potencia_reativa_media;
        $stats['potencia_aparente']['last'] = $ultimaLeitura->potencia_aparente_media;
        $stats['fator_potencia']['last'] = $ultimaLeitura->fator_potencia_media;
    }

    $statusProducao = [];
    foreach(['brunidores', 'descascadores', 'polidores'] as $tipo) {
        if (!empty($values[$tipo.'_avg']) && !is_null($stats[$tipo]['last'])) {
            $campoMedia = 'corrente_' . $tipo . '_media';
            $campoUltima = 'corrente_' . $tipo . '_media';

            $ultimaMedida = $ultimaLeitura ? $ultimaLeitura->$campoUltima : null;
            $mediaUltimaHora = $stats[$tipo]['avg'];

            if (!is_null($ultimaMedida) && !is_null($mediaUltimaHora) && $mediaUltimaHora > 0) {
                $diferencaPercentual = (($ultimaMedida - $mediaUltimaHora) / $mediaUltimaHora) * 100;

                if ($diferencaPercentual < -20) {
                    $statusProducao[$tipo] = ['status' => 'leve', 'color' => 'blue', 'label' => 'Leve', 'ultima_medida' => $ultimaMedida, 'media_ultima_hora' => $mediaUltimaHora];
                } elseif ($diferencaPercentual > 20) {
                    $statusProducao[$tipo] = ['status' => 'pesada', 'color' => 'orange', 'label' => 'Pesada', 'ultima_medida' => $ultimaMedida, 'media_ultima_hora' => $mediaUltimaHora];
                } else {
                    $statusProducao[$tipo] = ['status' => 'normal', 'color' => 'green', 'label' => 'Normal', 'ultima_medida' => $ultimaMedida, 'media_ultima_hora' => $mediaUltimaHora];
                }
            }
        }
    }
@endphp

<div class="overflow-x-auto pb-4">
    <div class="flex gap-4 min-w-max">
        @if($colunasVisiveis['brunidores'] && !empty($values['brunidores_avg']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Brunidor - Corrente</p>
                <p class="text-3xl font-bold text-primary-600 mb-2">{{ number_format($stats['brunidores']['avg'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                <p class="text-xs text-neutral-500 mb-3">Média do período</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span>Min: {{ number_format($stats['brunidores']['min'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Max: {{ number_format($stats['brunidores']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($statusProducao['brunidores']))
        @php $status = $statusProducao['brunidores']; @endphp
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $status['color'] }}-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $status['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Brunidor - Produção</p>
                <p class="text-3xl font-bold text-{{ $status['color'] }}-600 mb-3">{{ $status['label'] }}</p>
                <div class="text-xs text-neutral-600">
                    <p>Última medida: {{ number_format($status['ultima_medida'], 2, ',', '.') }} A</p>
                    <p>Média da última hora: {{ number_format($status['media_ultima_hora'], 2, ',', '.') }} A</p>
                </div>
            </div>
        </div>
        @endif

        @if(!is_null($disponibilidade['brunidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg {{ $disponibilidade['brunidores'] >= 80 ? 'bg-green-100' : ($disponibilidade['brunidores'] >= 50 ? 'bg-amber-100' : 'bg-red-100') }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $disponibilidade['brunidores'] >= 80 ? 'text-green-600' : ($disponibilidade['brunidores'] >= 50 ? 'text-amber-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Brunidor - Disponibilidade</p>
                <p class="text-3xl font-bold {{ $disponibilidade['brunidores'] >= 80 ? 'text-green-600' : ($disponibilidade['brunidores'] >= 50 ? 'text-amber-600' : 'text-red-600') }} mb-3">{{ number_format($disponibilidade['brunidores'], 1, ',', '.') }}%</p>
                <div class="text-xs text-neutral-600">
                    <p class="flex items-center">
                        <span class="inline-block w-2 h-2 rounded-full {{ $disponibilidade['brunidores'] >= 80 ? 'bg-green-500' : ($disponibilidade['brunidores'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }} mr-2"></span>
                        {{ $disponibilidade['brunidores'] >= 80 ? 'Alta' : ($disponibilidade['brunidores'] >= 50 ? 'Média' : 'Baixa') }} disponibilidade
                    </p>
                </div>
            </div>
        </div>
        @endif
        @endif

        @if($colunasVisiveis['descascadores'] && !empty($values['descascadores_avg']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Descascador - Corrente</p>
                <p class="text-3xl font-bold text-green-600 mb-2">{{ number_format($stats['descascadores']['avg'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                <p class="text-xs text-neutral-500 mb-3">Média do período</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span>Min: {{ number_format($stats['descascadores']['min'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Max: {{ number_format($stats['descascadores']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($statusProducao['descascadores']))
        @php $status = $statusProducao['descascadores']; @endphp
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $status['color'] }}-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $status['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Descascador - Produção</p>
                <p class="text-3xl font-bold text-{{ $status['color'] }}-600 mb-3">{{ $status['label'] }}</p>
                <div class="text-xs text-neutral-600">
                    <p>Última medida: {{ number_format($status['ultima_medida'], 2, ',', '.') }} A</p>
                    <p>Média da última hora: {{ number_format($status['media_ultima_hora'], 2, ',', '.') }} A</p>
                </div>
            </div>
        </div>
        @endif

        @if(!is_null($disponibilidade['descascadores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg {{ $disponibilidade['descascadores'] >= 80 ? 'bg-green-100' : ($disponibilidade['descascadores'] >= 50 ? 'bg-amber-100' : 'bg-red-100') }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $disponibilidade['descascadores'] >= 80 ? 'text-green-600' : ($disponibilidade['descascadores'] >= 50 ? 'text-amber-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Descascador - Disponibilidade</p>
                <p class="text-3xl font-bold {{ $disponibilidade['descascadores'] >= 80 ? 'text-green-600' : ($disponibilidade['descascadores'] >= 50 ? 'text-amber-600' : 'text-red-600') }} mb-3">{{ number_format($disponibilidade['descascadores'], 1, ',', '.') }}%</p>
                <div class="text-xs text-neutral-600">
                    <p class="flex items-center">
                        <span class="inline-block w-2 h-2 rounded-full {{ $disponibilidade['descascadores'] >= 80 ? 'bg-green-500' : ($disponibilidade['descascadores'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }} mr-2"></span>
                        {{ $disponibilidade['descascadores'] >= 80 ? 'Alta' : ($disponibilidade['descascadores'] >= 50 ? 'Média' : 'Baixa') }} disponibilidade
                    </p>
                </div>
            </div>
        </div>
        @endif
        @endif

        @if($colunasVisiveis['polidores'] && !empty($values['polidores_avg']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Polidor - Corrente</p>
                <p class="text-3xl font-bold text-amber-600 mb-2">{{ number_format($stats['polidores']['avg'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                <p class="text-xs text-neutral-500 mb-3">Média do período</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span>Min: {{ number_format($stats['polidores']['min'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Max: {{ number_format($stats['polidores']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($statusProducao['polidores']))
        @php $status = $statusProducao['polidores']; @endphp
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $status['color'] }}-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $status['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Polidor - Produção</p>
                <p class="text-3xl font-bold text-{{ $status['color'] }}-600 mb-3">{{ $status['label'] }}</p>
                <div class="text-xs text-neutral-600">
                    <p>Última medida: {{ number_format($status['ultima_medida'], 2, ',', '.') }} A</p>
                    <p>Média da última hora: {{ number_format($status['media_ultima_hora'], 2, ',', '.') }} A</p>
                </div>
            </div>
        </div>
        @endif

        @if(!is_null($disponibilidade['polidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg {{ $disponibilidade['polidores'] >= 80 ? 'bg-green-100' : ($disponibilidade['polidores'] >= 50 ? 'bg-amber-100' : 'bg-red-100') }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $disponibilidade['polidores'] >= 80 ? 'text-green-600' : ($disponibilidade['polidores'] >= 50 ? 'text-amber-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Polidor - Disponibilidade</p>
                <p class="text-3xl font-bold {{ $disponibilidade['polidores'] >= 80 ? 'text-green-600' : ($disponibilidade['polidores'] >= 50 ? 'text-amber-600' : 'text-red-600') }} mb-3">{{ number_format($disponibilidade['polidores'], 1, ',', '.') }}%</p>
                <div class="text-xs text-neutral-600">
                    <p class="flex items-center">
                        <span class="inline-block w-2 h-2 rounded-full {{ $disponibilidade['polidores'] >= 80 ? 'bg-green-500' : ($disponibilidade['polidores'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }} mr-2"></span>
                        {{ $disponibilidade['polidores'] >= 80 ? 'Alta' : ($disponibilidade['polidores'] >= 50 ? 'Média' : 'Baixa') }} disponibilidade
                    </p>
                </div>
            </div>
        </div>
        @endif
        @endif

        @if($colunasVisiveis['temperatura'] && !empty($values['temperatura_avg']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Temperatura</p>
                <p class="text-3xl font-bold text-red-600 mb-3">{{ number_format($stats['temperatura']['last'], 1, ',', '.') }} <span class="text-sm text-neutral-500">°C</span></p>
                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span>Min: {{ number_format($stats['temperatura']['min'], 1, ',', '.') }}°C</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Max: {{ number_format($stats['temperatura']['max'], 1, ',', '.') }}°C</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['umidade'] && !empty($values['umidade_avg']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Umidade</p>
                <p class="text-3xl font-bold text-cyan-600 mb-3">{{ number_format($stats['umidade']['last'], 1, ',', '.') }} <span class="text-sm text-neutral-500">%</span></p>
                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <span>Min: {{ number_format($stats['umidade']['min'], 1, ',', '.') }}%</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Max: {{ number_format($stats['umidade']['max'], 1, ',', '.') }}%</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['grandezas_eletricas'])
            <div id="grandezas-cards" class="flex gap-4">
                <div id="card-tensao" class="hidden">
                    <div class="flex gap-4 min-w-max">
                        @if(!empty($values['tensao_r_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Tensão Fase R</p>
                                <p class="text-3xl font-bold text-red-600 mb-3">{{ number_format($stats['tensao_r']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">V</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['tensao_r']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['tensao_r']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['tensao_s_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Tensão Fase S</p>
                                <p class="text-3xl font-bold text-amber-600 mb-3">{{ number_format($stats['tensao_s']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">V</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['tensao_s']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['tensao_s']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['tensao_t_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Tensão Fase T</p>
                                <p class="text-3xl font-bold text-primary-600 mb-3">{{ number_format($stats['tensao_t']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">V</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['tensao_t']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['tensao_t']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="card-corrente" class="hidden">
                    <div class="flex gap-4 min-w-max">
                        @if(!empty($values['corrente_r_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Corrente Fase R</p>
                                <p class="text-3xl font-bold text-red-600 mb-3">{{ number_format($stats['corrente_r']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['corrente_r']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['corrente_r']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['corrente_s_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Corrente Fase S</p>
                                <p class="text-3xl font-bold text-amber-600 mb-3">{{ number_format($stats['corrente_s']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['corrente_s']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['corrente_s']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['corrente_t_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Corrente Fase T</p>
                                <p class="text-3xl font-bold text-primary-600 mb-3">{{ number_format($stats['corrente_t']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['corrente_t']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['corrente_t']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="card-potencia" class="hidden">
                    <div class="flex gap-4 min-w-max">
                        @if(!empty($values['potencia_ativa_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Potência Ativa</p>
                                <p class="text-3xl font-bold text-green-600 mb-3">{{ number_format($stats['potencia_ativa']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">kW</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['potencia_ativa']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['potencia_ativa']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['potencia_reativa_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Potência Reativa</p>
                                <p class="text-3xl font-bold text-purple-600 mb-3">{{ number_format($stats['potencia_reativa']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">kVAr</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['potencia_reativa']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['potencia_reativa']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($values['potencia_aparente_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Potência Aparente</p>
                                <p class="text-3xl font-bold text-indigo-600 mb-3">{{ number_format($stats['potencia_aparente']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">kVA</span></p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['potencia_aparente']['min'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['potencia_aparente']['max'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="card-fator" class="hidden">
                    <div class="flex gap-4 min-w-max">
                        @if(!empty($values['fator_potencia_avg']))
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Fator de Potência</p>
                                <p class="text-3xl font-bold text-primary-600 mb-3">{{ number_format($stats['fator_potencia']['last'], 3, ',', '.') }}</p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-neutral-600">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Min: {{ number_format($stats['fator_potencia']['min'], 3, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        <span>Max: {{ number_format($stats['fator_potencia']['max'], 3, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    window.switchGrandezasCards = function(tipo) {
        ['tensao', 'corrente', 'potencia', 'fator'].forEach(t => {
            const card = document.getElementById(`card-${t}`);
            const cardContent = card.querySelector('.flex');
            if (card) {
                if (t === tipo) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.switchGrandezasCards('tensao');
    });
</script>