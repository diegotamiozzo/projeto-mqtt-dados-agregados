@props(['leituras', 'colunasVisiveis'])

@php
    $stats = [
        'brunidores' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'blue'],
        'descascadores' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'green'],
        'polidores' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'amber'],
        'temperatura' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'red'],
        'umidade' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'cyan'],
        'tensao_r' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'red'],
        'tensao_s' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'amber'],
        'tensao_t' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'blue'],
        'corrente_r' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'red'],
        'corrente_s' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'amber'],
        'corrente_t' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'blue'],
        'potencia_ativa' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'green'],
        'potencia_reativa' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'purple'],
        'fator_potencia' => ['min' => null, 'max' => null, 'avg' => null, 'color' => 'indigo'],
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
        if (!is_null($leitura->corrente_brunidores_media)) $values['brunidores'][] = $leitura->corrente_brunidores_media;
        if (!is_null($leitura->corrente_descascadores_media)) $values['descascadores'][] = $leitura->corrente_descascadores_media;
        if (!is_null($leitura->corrente_polidores_media)) $values['polidores'][] = $leitura->corrente_polidores_media;
        if (!is_null($leitura->temperatura_media)) $values['temperatura'][] = $leitura->temperatura_media;
        if (!is_null($leitura->umidade_media)) $values['umidade'][] = $leitura->umidade_media;
        if (!is_null($leitura->tensao_r_media)) $values['tensao_r'][] = $leitura->tensao_r_media;
        if (!is_null($leitura->tensao_s_media)) $values['tensao_s'][] = $leitura->tensao_s_media;
        if (!is_null($leitura->tensao_t_media)) $values['tensao_t'][] = $leitura->tensao_t_media;
        if (!is_null($leitura->corrente_r_media)) $values['corrente_r'][] = $leitura->corrente_r_media;
        if (!is_null($leitura->corrente_s_media)) $values['corrente_s'][] = $leitura->corrente_s_media;
        if (!is_null($leitura->corrente_t_media)) $values['corrente_t'][] = $leitura->corrente_t_media;
        if (!is_null($leitura->potencia_ativa_media)) $values['potencia_ativa'][] = $leitura->potencia_ativa_media;
        if (!is_null($leitura->potencia_reativa_media)) $values['potencia_reativa'][] = $leitura->potencia_reativa_media;
        if (!is_null($leitura->fator_potencia_media)) $values['fator_potencia'][] = $leitura->fator_potencia_media;
    }

    foreach($values as $key => $arr) {
        if (!empty($arr)) {
            $stats[$key]['min'] = min($arr);
            $stats[$key]['max'] = max($arr);
            $stats[$key]['avg'] = array_sum($arr) / count($arr);
        }
    }
@endphp

<div class="overflow-x-auto pb-4">
    <div class="flex gap-4 min-w-max">
        @if($colunasVisiveis['brunidores'] && !empty($values['brunidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Brunidor</p>
                            <p class="text-2xl font-bold text-primary-600">{{ number_format($stats['brunidores']['avg'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ number_format($stats['brunidores']['min'], 2, ',', '.') }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            {{ number_format($stats['brunidores']['max'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['descascadores'] && !empty($values['descascadores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Descascador</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['descascadores']['avg'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ number_format($stats['descascadores']['min'], 2, ',', '.') }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            {{ number_format($stats['descascadores']['max'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['polidores'] && !empty($values['polidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Polidor</p>
                            <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['polidores']['avg'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ number_format($stats['polidores']['min'], 2, ',', '.') }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            {{ number_format($stats['polidores']['max'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['temperatura'] && !empty($values['temperatura']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Temperatura</p>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['temperatura']['avg'], 1, ',', '.') }}°C</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ number_format($stats['temperatura']['min'], 1, ',', '.') }}°C
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            {{ number_format($stats['temperatura']['max'], 1, ',', '.') }}°C
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['umidade'] && !empty($values['umidade']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Umidade</p>
                            <p class="text-2xl font-bold text-cyan-600">{{ number_format($stats['umidade']['avg'], 1, ',', '.') }}%</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ number_format($stats['umidade']['min'], 1, ',', '.') }}%
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            {{ number_format($stats['umidade']['max'], 1, ',', '.') }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['grandezas_eletricas'])
            @foreach(['potencia_ativa' => 'Potência Ativa (kW)', 'potencia_reativa' => 'Potência Reativa (kVAr)', 'fator_potencia' => 'Fator de Potência'] as $key => $label)
                @if(!empty($values[$key]))
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-60 border border-neutral-200 card-hover">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ $label }}</p>
                                    <p class="text-2xl font-bold text-primary-600">{{ number_format($stats[$key]['avg'], $key === 'fator_potencia' ? 3 : 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-neutral-500 space-x-2">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    {{ number_format($stats[$key]['min'], $key === 'fator_potencia' ? 3 : 2, ',', '.') }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    {{ number_format($stats[$key]['max'], $key === 'fator_potencia' ? 3 : 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
