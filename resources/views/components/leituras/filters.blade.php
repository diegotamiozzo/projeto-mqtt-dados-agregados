@props(['clientes', 'equipamentos', 'filters'])

<form method="GET" action="{{ route('leituras.index') }}" class="mb-4 p-3 border rounded bg-light">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label for="id_cliente" class="form-label">Cliente</label>
            <select name="id_cliente" id="id_cliente" class="form-select">
                <option value="">Todos</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente }}" {{ ($filters['id_cliente'] ?? '') == $cliente ? 'selected' : '' }}>{{ $cliente }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="id_equipamento" class="form-label">Equipamento</label>
            <select name="id_equipamento" id="id_equipamento" class="form-select">
                <option value="">Todos</option>
                @foreach($equipamentos as $equipamento)
                    <option value="{{ $equipamento }}" {{ ($filters['id_equipamento'] ?? '') == $equipamento ? 'selected' : '' }}>{{ $equipamento }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="data_inicio" class="form-label">Data Início</label>
            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $filters['data_inicio'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label for="data_fim" class="form-label">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $filters['data_fim'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-info">Filtrar</button>
            <a href="{{ route('leituras.index') }}" class="btn btn-secondary">Limpar Filtros</a>
        </div>
    </div>
</form>