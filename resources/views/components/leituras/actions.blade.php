{{-- resources/views/components/leituras/actions.blade.php --}}
<div class="mb-3">
    <form action="{{ route('leituras.agregar') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
    <a href="{{ route('leituras.exportar', request()->query()) }}" class="btn btn-success">Exportar Dados Filtrados</a>
</div>