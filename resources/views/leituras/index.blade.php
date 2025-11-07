<x-layout title="Monitoramento de Equipamentos">
    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-80 bg-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
            <!-- Sidebar Header -->
            <div class="bg-gradient-to-br from-primary-600 to-primary-700 text-white p-2">
                <div class="flex items-center justify-between">
                    <!-- Imagem logo -->
                    <img src="{{ asset('images/logo-branco.png') }}" alt="Logo" class="h-12 w-12 mb-4 block mx-auto">
                    <button onclick="toggleSidebar()" class="lg:hidden text-white hover:bg-primary-500 p-2 rounded-lg transition-smooth">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Sidebar Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Filtros -->
                <x-leituras.filters :clientes="$clientes" :equipamentos="$equipamentos" :filters="$filters" />
                <!-- Ações -->
                <x-leituras.actions />
            </div>
        </aside>


        <!-- Overlay for mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-neutral-900 bg-opacity-50 z-40 lg:hidden opacity-0 invisible transition-all duration-300"></div>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-80">
            <!-- Floating Header -->
            <header id="floatingHeader" class="sticky top-0 z-30 bg-white border-b border-neutral-200 shadow-sm transition-transform duration-300">
                <div class="px-4 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Mobile Menu Button -->
                        <button onclick="toggleSidebar()" class="lg:hidden text-neutral-600 hover:text-primary-600 p-2 hover:bg-neutral-100 rounded-lg transition-smooth">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Title -->
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/icone.png') }}" alt="Logo" class="h-12 w-auto">
                                <div>
                                <h1 class="text-xl lg:text-2xl font-bold text-neutral-900">Monitore seus Equipamentos em Tempo Real</h1>
                                
                                @if($ultimaAtualizacao)
                                    <p class="text-sm text-neutral-500 mt-0.5">
                                        Última atualização: <span class="font-medium">{{ \Carbon\Carbon::parse($ultimaAtualizacao)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                                        <span class="mx-2">|</span>
                                        Próxima em atualização em: <span id="countdown-timer" class="font-semibold text-primary-600">60</span>s
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="p-4 lg:p-8">
                @if(session('success'))
                    <div id="success-alert" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center transition-smooth shadow-sm">
                        <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($totalLeituras > 0)
                    <p class="text-neutral-600 mb-6">Exibindo as últimas <span class="font-semibold text-neutral-900">{{ $totalLeituras }}</span> horas de dados.</p>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6">
                        Nenhum dado para exibir. Clique em "Atualizar" para processar.
                    </div>
                @endif

                @if(isset($filters['id_equipamento']) && !empty($filters['id_equipamento']))
                    @if($leituras->isNotEmpty())
                        <!-- Stats Cards -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Estatísticas Resumidas - {{ $nomeEquipamento }}</h2>
                            <x-leituras.stats :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" />
                        </div>

                        <!-- Charts -->
                        <x-leituras.charts :leituras="$leituras" :colunasVisiveis="$colunasVisiveis" :nomeEquipamento="$nomeEquipamento" />
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
                            Nenhum dado encontrado para o equipamento selecionado e filtros aplicados.
                        </div>
                    @endif
                @else
                    <div class="bg-primary-50 border border-primary-200 text-primary-800 px-4 py-3 rounded-lg">
                        Selecione um equipamento para visualizar os dados em gráficos.
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('opacity-0');
            overlay.classList.toggle('invisible');
        }

        let lastScrollTop = 0;
        const header = document.getElementById('floatingHeader');

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            lastScrollTop = scrollTop;
        });
    </script>
</x-layout>
