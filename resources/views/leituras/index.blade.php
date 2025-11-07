<x-layout title="Monitoramento de Equipamentos">
    {{-- Botão para abrir painel de filtros --}}
    <button class="toggle-panel-btn" onclick="togglePanel()" title="Filtros e Ações">
        <span id="panel-icon">⚙️</span>
    </button>

    {{-- Painel flutuante com filtros e ações --}}
    <div class="floating-panel" id="floatingPanel">
        <div class="floating-panel-content">
            <h4 class="mb-3">Filtros e Ações</h4>

            {{-- Componente de Filtros --}}
            <x-leituras.filters
                :clientes="$clientes"
                :equipamentos="$equipamentos"
                :filters="$filters"
            />

            {{-- Componente de Ações --}}
            <x-leituras.actions />
        </div>
    </div>

    <div class="container-fluid mt-4" style="padding-bottom: 180px;">

        {{-- 1. CABEÇALHO E ALERTAS --}}
        <div class="d-flex align-items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo da Empresa" style="height: 60px; margin-right: 15px;">
            <h1 class="mb-0">Monitoramento de Equipamentos</h1>
        </div>

        @if($totalLeituras > 0)
            <p class="text-muted">Exibindo as últimas {{ $totalLeituras }} horas de dados.</p>
        @else
            <p class="text-muted">Nenhum dado para exibir. Clique em "Atualizar" para processar.</p>
        @endif

        @if($ultimaAtualizacao)
            <p class="text-muted mb-1">
                Última atualização: <strong>{{ \Carbon\Carbon::parse($ultimaAtualizacao)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</strong>
            </p>
            <p class="text-muted">
                Próxima atualização em: <strong id="countdown-timer" class="text-primary">60</strong> segundos
            </p>
        @endif

        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif

        {{-- GRÁFICOS COM ÊNFASE --}}
        @if(isset($filters['id_equipamento']) && !empty($filters['id_equipamento']))
            @if($leituras->isNotEmpty())
                {{-- Gráficos interativos com ênfase --}}
                <x-leituras.charts :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
            @else
                <p class="alert alert-warning">Nenhum dado encontrado para o equipamento selecionado e filtros aplicados.</p>
            @endif
        @else
            <p class="alert alert-info">Selecione um equipamento para visualizar os dados em gráficos.</p>
        @endif

    </div>

    {{-- Botão para mostrar/esconder cards de estatísticas --}}
    @if(isset($filters['id_equipamento']) && !empty($filters['id_equipamento']) && $leituras->isNotEmpty())
    <button class="toggle-stats-btn" onclick="toggleStats()" title="Estatísticas">
        <span id="stats-icon">📊</span>
    </button>

    {{-- Overlay flutuante com estatísticas --}}
    <div class="stats-overlay collapsed" id="statsOverlay">
        <x-leituras.stats :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
    </div>
    @endif

    <script>
        function togglePanel() {
            const panel = document.getElementById('floatingPanel');
            const icon = document.getElementById('panel-icon');
            panel.classList.toggle('active');
            icon.textContent = panel.classList.contains('active') ? '✕' : '⚙️';
        }

        function toggleStats() {
            const overlay = document.getElementById('statsOverlay');
            const icon = document.getElementById('stats-icon');
            overlay.classList.toggle('collapsed');
            icon.textContent = overlay.classList.contains('collapsed') ? '📊' : '✕';
        }
    </script>
</x-layout>