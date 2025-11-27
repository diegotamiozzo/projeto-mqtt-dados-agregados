@props(['clientes', 'equipamentos', 'filters'])

@php
    // Cliente do usuário logado
    $rawClient = auth()->user()->external_client_id ?? '';
    $clientName = str_replace('MQTT_', '', $rawClient);
@endphp

<div class="space-y-6">
    <h3 class="text-sm font-semibold text-neutral-700 uppercase tracking-wide">Filtros</h3>

    <form method="GET" action="{{ route('leituras.index') }}" class="space-y-4" id="filtrosForm">

        <!-- Campo de E-mail do Usuário -->
        @auth
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
            <label class="block text-xs font-medium text-primary-700 uppercase tracking-wide mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Usuário Logado
            </label>
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-semibold text-primary-900">{{ auth()->user()->email }}</span>
            </div>
            @if(auth()->user()->name)
                <p class="text-xs text-primary-600 mt-1 ml-7">{{ auth()->user()->name }}</p>
            @endif
        </div>
        @endauth


        <!-- Campo Cliente fixo (vem do usuário logado) -->
        <div>
            <label class="block text-sm font-medium text-neutral-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Cliente
            </label>

            <!-- Campo somente leitura -->
            <input type="text"
                   class="w-full px-4 py-2.5 border border-neutral-300 rounded-lg bg-neutral-100 text-neutral-700"
                   value="{{ $clientName }}"
                   readonly>

            <!-- Campo oculto para enviar no GET -->
            <input type="hidden" name="id_cliente" value="{{ $rawClient }}">
        </div>


        <!-- Equipamentos -->
        <div id="equipamentoContainer">
            <label for="id_equipamento" class="block text-sm font-medium text-neutral-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                Equipamento
            </label>

            <select name="id_equipamento" id="id_equipamento"
                    class="w-full px-4 py-2.5 border border-neutral-300 rounded-lg
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth bg-white text-neutral-900">
                <option value="">Todos os equipamentos</option>

                @foreach($equipamentos as $equipamento)
                    <option value="{{ $equipamento }}"
                        {{ ($filters['id_equipamento'] ?? '') == $equipamento ? 'selected' : '' }}>
                        {{ $equipamento }}
                    </option>
                @endforeach
            </select>
        </div>


        <!-- Datas -->
        <div>
            <label for="data_inicio" class="block text-sm font-medium text-neutral-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Data Início <span class="text-red-600">*</span>
            </label>
            <input type="date" name="data_inicio" id="data_inicio"
                   value="{{ $filters['data_inicio'] ?? '' }}" required
                   class="w-full px-4 py-2.5 border border-neutral-300 rounded-lg
                          focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth bg-white text-neutral-900">
        </div>

        <div>
            <label for="data_fim" class="block text-sm font-medium text-neutral-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Data Fim <span class="text-red-600">*</span>
            </label>
            <input type="date" name="data_fim" id="data_fim"
                   value="{{ $filters['data_fim'] ?? '' }}" required
                   class="w-full px-4 py-2.5 border border-neutral-300 rounded-lg
                          focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth bg-white text-neutral-900">
            <p id="data_erro" class="text-red-600 text-sm mt-1 hidden">
                A data de fim não pode ser anterior à data de início
            </p>
        </div>

        <!-- Botões -->
        <div class="pt-2 space-y-2">
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg
                           transition-smooth shadow-sm hover:shadow-md flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Aplicar Filtros</span>
            </button>

            <a href="{{ route('leituras.index') }}"
               class="w-full block bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-medium py-2.5 px-4 rounded-lg transition-smooth text-center">
                Limpar Filtros
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('filtrosForm');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    const dataErro = document.getElementById('data_erro');

    function validarDatas() {
        if (dataInicio.value && dataFim.value) {
            const inicio = new Date(dataInicio.value);
            const fim = new Date(dataFim.value);

            if (fim < inicio) {
                dataErro.classList.remove('hidden');
                dataFim.classList.add('border-red-500');
                return false;
            } else {
                dataErro.classList.add('hidden');
                dataFim.classList.remove('border-red-500');
                return true;
            }
        }
        return true;
    }

    dataInicio.addEventListener('change', () => {
        if (dataFim.value) {
            dataFim.setAttribute('min', dataInicio.value);
            validarDatas();
        } else {
            dataFim.setAttribute('min', dataInicio.value);
        }
    });

    dataFim.addEventListener('change', validarDatas);

    if (dataInicio.value) {
        dataFim.setAttribute('min', dataInicio.value);
    }

    form.addEventListener('submit', function(e) {
        if (!validarDatas()) {
            e.preventDefault();
            dataFim.focus();
        }
    });

});
</script>
