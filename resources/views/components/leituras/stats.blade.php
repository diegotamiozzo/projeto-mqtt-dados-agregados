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
        'fator_potencia' => ['min' => null, 'max' => null, 'avg' => null, 'last' => null, 'color' => 'primary'],
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

    $ultimaLeitura = $leituras->first();

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
        $stats['fator_potencia']['last'] = $ultimaLeitura->fator_potencia_media;
    }
@endphp

<div class="overflow-x-auto pb-4">
    <div class="flex gap-4 min-w-max">
        @if($colunasVisiveis['brunidores'] && !empty($values['brunidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                @if(!is_null($disponibilidade['brunidores']))
                <div class="flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $disponibilidade['brunidores'] >= 80 ? 'bg-green-100 text-green-700' : ($disponibilidade['brunidores'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ number_format($disponibilidade['brunidores'], 1, ',', '.') }}%
                </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Brunidor</p>
                <p class="text-3xl font-bold text-primary-600 mb-3">{{ number_format($stats['brunidores']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
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
        @endif

        @if($colunasVisiveis['descascadores'] && !empty($values['descascadores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                @if(!is_null($disponibilidade['descascadores']))
                <div class="flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $disponibilidade['descascadores'] >= 80 ? 'bg-green-100 text-green-700' : ($disponibilidade['descascadores'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ number_format($disponibilidade['descascadores'], 1, ',', '.') }}%
                </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Descascador</p>
                <p class="text-3xl font-bold text-green-600 mb-3">{{ number_format($stats['descascadores']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
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
        @endif

        @if($colunasVisiveis['polidores'] && !empty($values['polidores']))
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-smooth p-5 min-w-64 border border-neutral-200 card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                @if(!is_null($disponibilidade['polidores']))
                <div class="flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $disponibilidade['polidores'] >= 80 ? 'bg-green-100 text-green-700' : ($disponibilidade['polidores'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ number_format($disponibilidade['polidores'], 1, ',', '.') }}%
                </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide mb-1">Polidor</p>
                <p class="text-3xl font-bold text-amber-600 mb-3">{{ number_format($stats['polidores']['last'], 2, ',', '.') }} <span class="text-sm text-neutral-500">A</span></p>
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
        @endif

        @if($colunasVisiveis['temperatura'] && !empty($values['temperatura']))
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

        @if($colunasVisiveis['umidade'] && !empty($values['umidade']))
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
                    <div class="flex gap-4">
                        @if(!empty($values['tensao_r']))
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
                        @if(!empty($values['tensao_s']))
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
                        @if(!empty($values['tensao_t']))
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
                    <div class="flex gap-4">
                        @if(!empty($values['corrente_r']))
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
                        @if(!empty($values['corrente_s']))
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
                        @if(!empty($values['corrente_t']))
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
                    <div class="flex gap-4">
                        @if(!empty($values['potencia_ativa']))
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
                        @if(!empty($values['potencia_reativa']))
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
                    </div>
                </div>

                <div id="card-fator" class="hidden">
                    <div class="flex gap-4">
                        @if(!empty($values['fator_potencia']))
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
