{{-- resources/views/components/leituras/actions.blade.php --}}
<div class="mb-3">
    <form id="form-agregar" action="{{ route('leituras.agregar') }}" method="POST" class="d-inline">
        @csrf
        {{-- Campos ocultos para manter os filtros --}}
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
        
        <button type="submit" class="btn btn-primary" id="atualizar-button">
            <span id="button-text">Atualizar</span>
            <span id="spinner" class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true" style="display: none;"></span>
        </button>
    </form>
    <a href="{{ route('leituras.exportar', request()->query()) }}" class="btn btn-success">Exportar Dados Filtrados</a>
</div>

<script>
    let countdownSeconds = 60;
    let countdownInterval;
    let isUpdating = false;

    // Função para alternar o spinner e animação do botão
    function toggleSpinner(show) {
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('button-text');
        const button = document.getElementById('atualizar-button');
        
        if (show) {
            spinner.style.display = 'inline-block';
            buttonText.textContent = 'Atualizando...';
            button.disabled = true;
            button.classList.add('btn-updating');
            isUpdating = true;
            stopCountdown();
        } else {
            spinner.style.display = 'none';
            buttonText.textContent = 'Atualizar';
            button.disabled = false;
            button.classList.remove('btn-updating');
            isUpdating = false;
            startCountdown();
        }
    }

    // Função para iniciar a contagem regressiva
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

    // Função para parar a contagem regressiva
    function stopCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
    }

    // Função para atualizar o display da contagem
    function updateCountdownDisplay() {
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            countdownElement.textContent = countdownSeconds;
        }
    }

    // Função para submeter o formulário automaticamente
    function autoSubmitForm() {
        if (!isUpdating) {
            toggleSpinner(true);
            document.getElementById('form-agregar').submit();
        }
    }

    // Intercepta o submit do formulário para mostrar o spinner
    document.getElementById('form-agregar').addEventListener('submit', function(e) {
        e.preventDefault();
        toggleSpinner(true);
        this.submit();
    });

    // Inicia a contagem regressiva quando a página carrega
    document.addEventListener('DOMContentLoaded', function() {
        // Se acabou de fazer uma atualização (tem mensagem de sucesso), reseta o contador
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            toggleSpinner(false);
        } else {
            startCountdown();
        }
    });

    // Para o contador quando o usuário sai da página
    window.addEventListener('beforeunload', function() {
        stopCountdown();
    });
</script>

<style>
    #atualizar-button:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }

    #spinner {
        width: 1rem;
        height: 1rem;
        border-width: 0.15rem;
    }

    /* Animação pulsante no botão durante atualização */
    .btn-updating {
        animation: btnPulse 1.5s ease-in-out infinite;
    }

    @keyframes btnPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
        }
    }
</style>