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
            <div class="chart-header">
                <h3 class="chart-title">Tensão Elétrica (Fases R, S, T)</h3>
                <p class="chart-subtitle">Valores médios por hora (V)</p>
            </div>
            <div class="chart-wrapper">
                <canvas id="tensaoChart"></canvas>
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
    const tensaoCtx = document.getElementById('tensaoChart').getContext('2d');
    new Chart(tensaoCtx, {
        type: 'line',
        data: {
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
        options: chartOptions
    });
    @endif
</script>
