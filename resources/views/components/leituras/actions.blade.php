{{-- resources/views/components/leituras/actions.blade.php --}}
<div class="mb-3">
    <form id="form-agregar" action="{{ route('leituras.agregar') }}" method="POST" class="d-inline" onsubmit="toggleSpinner(true);">
        @csrf
        <button type="submit" class="btn btn-primary" id="atualizar-button">
            Atualizar <span id="spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
        </button>
    </form>
    <a href="{{ route('leituras.exportar', request()->query()) }}" class="btn btn-success">Exportar Dados Filtrados</a>
</div>