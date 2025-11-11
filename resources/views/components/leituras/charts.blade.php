@props(['leituras', 'colunasVisiveis', 'nomeEquipamento'])

@php
    $labels = [];
    $brunidoresData = [];
    $descascadoresData = [];
    $polidoresData = [];
    $temperaturaData = [];
    $umidadeData = [];
    $tensaoRData = [];
    $tensaoSData = [];
    $tensaoTData = [];
    $correnteRData = [];
    $correnteSData = [];
    $correnteTData = [];
    $potenciaAtivaData = [];
    $potenciaReativaData = [];
    $fatorPotenciaData = [];

    foreach($leituras as $leitura) {
        $labels[] = \Carbon\Carbon::parse($leitura->periodo_inicio)
            ->format('d/m H:i');

        $brunidoresData[] = $leitura->corrente_brunidores_media;
        $descascadoresData[] = $leitura->corrente_descascadores_media;
        $polidoresData[] = $leitura->corrente_polidores_media;
        $temperaturaData[] = $leitura->temperatura_media;
        $umidadeData[] = $leitura->umidade_media;
        $tensaoRData[] = $leitura->tensao_r_media;
        $tensaoSData[] = $leitura->tensao_s_media;
        $tensaoTData[] = $leitura->tensao_t_media;
        $correnteRData[] = $leitura->corrente_r_media;
        $correnteSData[] = $leitura->corrente_s_media;
        $correnteTData[] = $leitura->corrente_t_media;
        $potenciaAtivaData[] = $leitura->potencia_ativa_media;
        $potenciaReativaData[] = $leitura->potencia_reativa_media;
        $fatorPotenciaData[] = $leitura->fator_potencia_media;
    }
@endphp

<div class="space-y-6">
    @if($colunasVisiveis['brunidores'] || $colunasVisiveis['descascadores'] || $colunasVisiveis['polidores'])
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900">Corrente - {{ $nomeEquipamento }}</h3>
                <p class="text-sm text-neutral-500 mt-1">Valores médios por hora (A)</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
            </div>
        </div>
        <div class="relative" style="height: 400px;">
            <canvas id="correnteChart"></canvas>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['temperatura'])
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 card-hover mt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900">Temperatura - {{ $nomeEquipamento }}</h3>
                    <p class="text-sm text-neutral-500 mt-1">Valores médios por hora (°C)</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="relative" style="height: 400px;">
                <canvas id="temperaturaChart"></canvas>
            </div>
        </div>
        @endif

        @if($colunasVisiveis['umidade'])
        <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 card-hover mt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900">Umidade - {{ $nomeEquipamento }}</h3>
                    <p class="text-sm text-neutral-500 mt-1">Valores médios por hora (%)</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                </div>
            </div>
            <div class="relative" style="height: 400px;">
                <canvas id="umidadeChart"></canvas>
            </div>
        </div>
        @endif

    @if($colunasVisiveis['grandezas_eletricas'])
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 card-hover mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900">Grandezas Elétricas - {{ $nomeEquipamento }}</h3>
                <p class="text-sm text-neutral-500 mt-1" id="grandezas-subtitle">Tensão - Valores médios por hora (V)</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="switchGrandezas('tensao')" id="btn-tensao" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium transition-smooth hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Tensão
                </button>
                <button type="button" onclick="switchGrandezas('corrente')" id="btn-corrente" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium transition-smooth hover:bg-neutral-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Corrente
                </button>
                <button type="button" onclick="switchGrandezas('potencia')" id="btn-potencia" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium transition-smooth hover:bg-neutral-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Potência
                </button>
                <button type="button" onclick="switchGrandezas('fator')" id="btn-fator" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium transition-smooth hover:bg-neutral-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    FP
                </button>
            </div>
        </div>
        <div class="relative" style="height: 400px;">
            <canvas id="grandezasChart"></canvas>
        </div>
    </div>
    @endif
</div>

<script>
    const labels = @json($labels);
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 750,
            easing: 'easeInOutQuart'
        },
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 13,
                        family: "'Inter', sans-serif",
                        weight: '500'
                    },
                    padding: 16,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(31, 41, 55, 0.95)',
                padding: 12,
                titleFont: {
                    size: 14,
                    family: "'Inter', sans-serif",
                    weight: '600'
                },
                bodyFont: {
                    size: 13,
                    family: "'Inter', sans-serif"
                },
                cornerRadius: 8,
                displayColors: true,
                borderColor: 'rgba(209, 213, 219, 0.3)',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    color: 'rgba(229, 231, 235, 0.8)',
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 12,
                        family: "'Inter', sans-serif"
                    },
                    color: '#6B7280',
                    padding: 8
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 11,
                        family: "'Inter', sans-serif"
                    },
                    color: '#6B7280',
                    maxRotation: 45,
                    minRotation: 45,
                    padding: 8
                }
            }
        }
    };

    @if($colunasVisiveis['brunidores'] || $colunasVisiveis['descascadores'] || $colunasVisiveis['polidores'])
    const correnteCtx = document.getElementById('correnteChart').getContext('2d');
    new Chart(correnteCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                @if($colunasVisiveis['brunidores'])
                {
                    label: 'Brunidores',
                    data: @json($brunidoresData),
                    borderColor: '#285995',
                    backgroundColor: 'rgba(40, 89, 149, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#285995',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                },
                @endif
                @if($colunasVisiveis['descascadores'])
                {
                    label: 'Descascadores',
                    data: @json($descascadoresData),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#16a34a',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                },
                @endif
                @if($colunasVisiveis['polidores'])
                {
                    label: 'Polidores',
                    data: @json($polidoresData),
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#d97706',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }
                @endif
            ]
        },
        options: chartOptions
    });
    @endif

    @if($colunasVisiveis['temperatura'])
    const temperaturaCtx = document.getElementById('temperaturaChart').getContext('2d');
    new Chart(temperaturaCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Temperatura (°C)',
                data: @json($temperaturaData),
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#dc2626',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: chartOptions
    });
    @endif

    @if($colunasVisiveis['umidade'])
    const umidadeCtx = document.getElementById('umidadeChart').getContext('2d');
    new Chart(umidadeCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Umidade (%)',
                data: @json($umidadeData),
                borderColor: '#0891b2',
                backgroundColor: 'rgba(8, 145, 178, 0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#0891b2',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: chartOptions
    });
    @endif

    @if($colunasVisiveis['grandezas_eletricas'])
    const grandezasCtx = document.getElementById('grandezasChart').getContext('2d');

    const grandezasData = {
        tensao: {
            labels: labels,
            datasets: [
                {
                    label: 'Fase R',
                    data: @json($tensaoRData),
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase S',
                    data: @json($tensaoSData),
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase T',
                    data: @json($tensaoTData),
                    borderColor: '#285995',
                    backgroundColor: 'rgba(40, 89, 149, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        corrente: {
            labels: labels,
            datasets: [
                {
                    label: 'Fase R',
                    data: @json($correnteRData),
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase S',
                    data: @json($correnteSData),
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase T',
                    data: @json($correnteTData),
                    borderColor: '#285995',
                    backgroundColor: 'rgba(40, 89, 149, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        potencia: {
            labels: labels,
            datasets: [
                {
                    label: 'Potência Ativa (kW)',
                    data: @json($potenciaAtivaData),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Potência Reativa (kVAr)',
                    data: @json($potenciaReativaData),
                    borderColor: '#9333ea',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        fator: {
            labels: labels,
            datasets: [
                {
                    label: 'Fator de Potência',
                    data: @json($fatorPotenciaData),
                    borderColor: '#285995',
                    backgroundColor: 'rgba(40, 89, 149, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        }
    };

    let grandezasChart = new Chart(grandezasCtx, {
        type: 'line',
        data: grandezasData.tensao,
        options: chartOptions
    });

    window.switchGrandezas = function(tipo) {
        const subtitles = {
            tensao: 'Tensão - Valores médios por hora (V)',
            corrente: 'Corrente - Valores médios por hora (A)',
            potencia: 'Potência - Valores médios por hora',
            fator: 'Fator de Potência - Valores médios por hora'
        };

        document.getElementById('grandezas-subtitle').textContent = subtitles[tipo];

        ['tensao', 'corrente', 'potencia', 'fator'].forEach(t => {
            const btn = document.getElementById(`btn-${t}`);
            if (t === tipo) {
                btn.classList.remove('bg-neutral-100', 'text-neutral-700', 'hover:bg-neutral-200');
                btn.classList.add('bg-primary-600', 'text-white', 'hover:bg-primary-700');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white', 'hover:bg-primary-700');
                btn.classList.add('bg-neutral-100', 'text-neutral-700', 'hover:bg-neutral-200');
            }
        });

        grandezasChart.data = grandezasData[tipo];
        grandezasChart.update('active');

        if (typeof window.switchGrandezasCards === 'function') {
            window.switchGrandezasCards(tipo);
        }
    };
    @endif
</script>
