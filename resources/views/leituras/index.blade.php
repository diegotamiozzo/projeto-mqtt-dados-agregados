<x-layout title="Monitoramento de Equipamentos">
    {{-- Botão para abrir sidebar --}}
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()" title="Menu">
        <span id="sidebar-icon">☰</span>
        <span>Menu</span>
    </button>

    {{-- Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- Sidebar com filtros e ações --}}
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-1">Monitoramento</h4>
            <small>Filtros e Ações</small>
        </div>
        <div class="sidebar-content">
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

    {{-- Cabeçalho flutuante --}}
    <div class="floating-header" id="floatingHeader">

        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo da Empresa" style="height: 50px; margin-right: 15px;">
                <div>
                    <h3 class="mb-0">Monitoramento de Equipamentos</h3>
                    @if($ultimaAtualizacao)
                        <small class="text-muted">
                            Última atualização: {{ \Carbon\Carbon::parse($ultimaAtualizacao)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}
                            | Próxima em: <strong id="countdown-timer" class="text-primary">60</strong>s
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid main-content">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" id="success-alert" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(isset($filters['id_equipamento']) && !empty($filters['id_equipamento']))
            @if($leituras->isNotEmpty())
                {{-- Cards de estatísticas no topo --}}
                <div class="stats-container">
                    <h5 class="mb-3">Estatísticas Resumidas</h5>
                    <x-leituras.stats :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
                </div>

                {{-- Gráficos interativos com ênfase --}}
                <x-leituras.charts :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
            @else
                <p class="alert alert-warning">Nenhum dado encontrado para o equipamento selecionado e filtros aplicados.</p>
            @endif
        @else
            <p class="alert alert-info">Selecione um equipamento para visualizar os dados em gráficos.</p>
        @endif

    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        let lastScrollTop = 0;
        const header = document.getElementById('floatingHeader');

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.classList.add('hidden');
            } else {
                header.classList.remove('hidden');
            }
            lastScrollTop = scrollTop;
        });
    </script>
</x-layout>