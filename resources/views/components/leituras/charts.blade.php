@props(['leituras', 'colunasVisiveis'])

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

    foreach($leituras->reverse() as $leitura) {
        $labels[] = \Carbon\Carbon::parse($leitura->periodo_inicio)
            ->timezone('America/Sao_Paulo')
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

<div class="row">
    @if($colunasVisiveis['brunidores'] || $colunasVisiveis['descascadores'] || $colunasVisiveis['polidores'])
    <div class="col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Corrente dos Equipamentos</h3>
                <p class="chart-subtitle">Valores médios por hora (A)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="correnteChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['temperatura'] && $colunasVisiveis['umidade'])
    <div class="col-lg-6 col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Temperatura</h3>
                <p class="chart-subtitle">Valores médios por hora (°C)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="temperaturaChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Umidade</h3>
                <p class="chart-subtitle">Valores médios por hora (%)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="umidadeChart"></canvas>
            </div>
        </div>
    </div>
    @elseif($colunasVisiveis['temperatura'])
    <div class="col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Temperatura</h3>
                <p class="chart-subtitle">Valores médios por hora (°C)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="temperaturaChart"></canvas>
            </div>
        </div>
    </div>
    @elseif($colunasVisiveis['umidade'])
    <div class="col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Umidade</h3>
                <p class="chart-subtitle">Valores médios por hora (%)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="umidadeChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if($colunasVisiveis['grandezas_eletricas'])
    <div class="col-12 mb-4">
        <div class="chart-container">
            <div class="chart-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="chart-title">Grandezas Elétricas</h3>
                    <p class="chart-subtitle" id="grandezas-subtitle">Tensão - Valores médios por hora (V)</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-primary" onclick="switchGrandezasChart('tensao')" id="btn-tensao">Tensão</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchGrandezasChart('corrente')" id="btn-corrente">Corrente</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchGrandezasChart('potencia')" id="btn-potencia">Potência</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchGrandezasChart('fator')" id="btn-fator">FP</button>
                </div>
            </div>
            <div class="chart-wrapper">
                <canvas id="grandezasChart"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    const labels = @json($labels);
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 12,
                        family: "'Inter', sans-serif"
                    },
                    padding: 15,
                    usePointStyle: true
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14
                },
                bodyFont: {
                    size: 13
                },
                cornerRadius: 8
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    maxRotation: 45,
                    minRotation: 45
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
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                @endif
                @if($colunasVisiveis['descascadores'])
                {
                    label: 'Descascadores',
                    data: @json($descascadoresData),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                @endif
                @if($colunasVisiveis['polidores'])
                {
                    label: 'Polidores',
                    data: @json($polidoresData),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
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
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6
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
                borderColor: 'rgb(14, 165, 233)',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6
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
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase S',
                    data: @json($tensaoSData),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase T',
                    data: @json($tensaoTData),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
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
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase S',
                    data: @json($correnteSData),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Fase T',
                    data: @json($correnteTData),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
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
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Potência Reativa (kVAr)',
                    data: @json($potenciaReativaData),
                    borderColor: 'rgb(168, 85, 247)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
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
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
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

    window.switchGrandezasChart = function(tipo) {
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
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            }
        });

        grandezasChart.data = grandezasData[tipo];
        grandezasChart.update();
    };
    @endif
</script>
