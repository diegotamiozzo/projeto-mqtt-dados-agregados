{{-- resources/views/leituras/index.blade.php --}}
<x-layout title="Monitoramento de Equipamentos">
    <div class="container-fluid mt-4">

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
            <p class="text-muted">
                Última atualização: <strong>{{ \Carbon\Carbon::parse($ultimaAtualizacao)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</strong>
            </p>
        @endif

        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif

        
        {{-- 2. COMPONENTE DE FILTROS --}}
        <x-leituras.filters 
            :clientes="$clientes" 
            :equipamentos="$equipamentos" 
            :filters="$filters" 
        />

        {{-- 3. COMPONENTE DE AÇÕES --}}
        <x-leituras.actions />

        {{-- 4. COMPONENTE DA TABELA (exibido somente se houver filtro de equipamento e/ou resultados) --}}
        @if(isset($filters['id_equipamento']) && !empty($filters['id_equipamento']))
            @if($leituras->isNotEmpty())
                <x-leituras.table :leituras="$leituras" />
            @else
                <p class="alert alert-warning">Nenhum dado encontrado para o equipamento selecionado e filtros aplicados.</p>
            @endif
        @else
            <p class="alert alert-info">Selecione um equipamento para visualizar os dados na tabela.</p>
        @endif

    </div>
</x-layout>