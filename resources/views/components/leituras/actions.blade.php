<div class="space-y-4">
    <div class="border-t border-neutral-200 pt-6">
        <h3 class="text-sm font-semibold text-neutral-700 uppercase tracking-wide mb-4">Ações</h3>

        <form id="form-agregar" action="{{ route('leituras.agregar') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="preserve_filters" value="1">
            @if(request('id_cliente'))
                <input type="hidden" name="id_cliente" value="{{ request('id_cliente') }}">
            @endif
            @if(request('id_equipamento'))
                <input type="hidden" name="id_equipamento" value="{{ request('id_equipamento') }}">
            @endif
            @if(request('data_inicio'))
                <input type="hidden" name="data_inicio" value="{{ request('data_inicio') }}">
            @endif
            @if(request('data_fim'))
                <input type="hidden" name="data_fim" value="{{ request('data_fim') }}">
            @endif

            <button type="submit" id="atualizar-button" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg transition-smooth shadow-sm hover:shadow-md flex items-center justify-center space-x-2 group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span id="button-text">Atualizar Dados</span>
                <svg id="spinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>

            <a href="{{ route('leituras.exportar', request()->query()) }}" class="w-full block bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-lg transition-smooth shadow-sm hover:shadow-md text-center group">
                <svg class="w-5 h-5 inline-block mr-2 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Dados
            </a>
        </form>
    </div>
</div>

<script>
    let countdownSeconds = 60;
    let countdownInterval;
    let isUpdating = false;

    function toggleSpinner(show) {
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('button-text');
        const button = document.getElementById('atualizar-button');
        const icon = button.querySelector('svg:first-child');

        if (show) {
            spinner.classList.remove('hidden');
            icon.classList.add('hidden');
            buttonText.textContent = 'Atualizando...';
            button.disabled = true;
            button.classList.add('opacity-75', 'cursor-not-allowed');
            isUpdating = true;
            stopCountdown();
        } else {
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
            buttonText.textContent = 'Atualizar Dados';
            button.disabled = false;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
            isUpdating = false;
            startCountdown();
        }
    }

    function startCountdown() {
        countdownSeconds = 60;
        updateCountdownDisplay();

        countdownInterval = setInterval(() => {
            countdownSeconds--;
            updateCountdownDisplay();

            if (countdownSeconds <= 0) {
                autoSubmitForm();
            }
        }, 1000);
    }

    function stopCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
    }

    function updateCountdownDisplay() {
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            countdownElement.textContent = countdownSeconds;
        }
    }

    function autoSubmitForm() {
        if (!isUpdating) {
            toggleSpinner(true);
            document.getElementById('form-agregar').submit();
        }
    }

    document.getElementById('form-agregar').addEventListener('submit', function(e) {
        e.preventDefault();
        toggleSpinner(true);
        this.submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            toggleSpinner(false);
        } else {
            startCountdown();
        }
    });

    window.addEventListener('beforeunload', function() {
        stopCountdown();
    });
</script>
