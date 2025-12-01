@props(['colunasVisiveis'])

<thead class="table-dark text-center">
    <tr>
        <th rowspan="3">Cliente</th>
        <th rowspan="3">Equipamento</th>
        <th rowspan="3">Período Início</th>
        <th rowspan="3">Período Fim</th>
        <th rowspan="3">Registros</th>
        
        @if($colunasVisiveis['brunidores'])
            <th colspan="4" class="table-primary">Corrente Brunidores</th>
        @endif
        
        @if($colunasVisiveis['descascadores'])
            <th colspan="4" class="table-info">Corrente Descascadores</th>
        @endif
        
        @if($colunasVisiveis['polidores'])
            <th colspan="4" class="table-warning">Corrente Polidores</th>
        @endif
        
        @if($colunasVisiveis['temperatura'])
            <th colspan="4" class="table-success">Temperatura</th>
        @endif
        
        @if($colunasVisiveis['umidade'])
            <th colspan="4" class="table-danger">Umidade</th>
        @endif
        
        @if($colunasVisiveis['grandezas_eletricas'])
            <th colspan="36" class="table-secondary">Grandezas Elétricas</th>
        @endif
        
        <th rowspan="3">Atualizado</th>
    </tr>
    <tr>
        @if($colunasVisiveis['brunidores'])
            <th class="table-primary" rowspan="2">Média</th>
            <th class="table-primary" rowspan="2">Máx</th>
            <th class="table-primary" rowspan="2">Mín</th>
            <th class="table-primary" rowspan="2">Última</th>
        @endif
        
        @if($colunasVisiveis['descascadores'])
            <th class="table-info" rowspan="2">Média</th>
            <th class="table-info" rowspan="2">Máx</th>
            <th class="table-info" rowspan="2">Mín</th>
            <th class="table-info" rowspan="2">Última</th>
        @endif
        
        @if($colunasVisiveis['polidores'])
            <th class="table-warning" rowspan="2">Média</th>
            <th class="table-warning" rowspan="2">Máx</th>
            <th class="table-warning" rowspan="2">Mín</th>
            <th class="table-warning" rowspan="2">Última</th>
        @endif

        @if($colunasVisiveis['temperatura'])
            <th class="table-success" rowspan="2">Média</th>
            <th class="table-success" rowspan="2">Máx</th>
            <th class="table-success" rowspan="2">Mín</th>
            <th class="table-success" rowspan="2">Última</th>
        @endif

        @if($colunasVisiveis['umidade'])
            <th class="table-danger" rowspan="2">Média</th>
            <th class="table-danger" rowspan="2">Máx</th>
            <th class="table-danger" rowspan="2">Mín</th>
            <th class="table-danger" rowspan="2">Última</th>
        @endif

        @if($colunasVisiveis['grandezas_eletricas'])
            <th colspan="4" class="table-secondary">Tensão R</th>
            <th colspan="4" class="table-secondary">Corrente R</th>
            <th colspan="4" class="table-light">Tensão S</th>
            <th colspan="4" class="table-light">Corrente S</th>
            <th colspan="4" class="table-secondary">Tensão T</th>
            <th colspan="4" class="table-secondary">Corrente T</th>
            <th colspan="4" class="table-warning">Potência Ativa</th>
            <th colspan="4" class="table-info">Potência Reativa</th>
            <th colspan="4" class="table-primary">Potência Aparente</th>
            <th colspan="4" class="table-success">Fator Potência</th>
        @endif
    </tr>
    <tr>
        @if($colunasVisiveis['grandezas_eletricas'])
            {{-- Tensão R --}}
            <th class="table-secondary">Média</th>
            <th class="table-secondary">Máx</th>
            <th class="table-secondary">Mín</th>
            <th class="table-secondary">Última</th>

            {{-- Corrente R --}}
            <th class="table-secondary">Média</th>
            <th class="table-secondary">Máx</th>
            <th class="table-secondary">Mín</th>
            <th class="table-secondary">Última</th>

            {{-- Tensão S --}}
            <th class="table-light">Média</th>
            <th class="table-light">Máx</th>
            <th class="table-light">Mín</th>
            <th class="table-light">Última</th>

            {{-- Corrente S --}}
            <th class="table-light">Média</th>
            <th class="table-light">Máx</th>
            <th class="table-light">Mín</th>
            <th class="table-light">Última</th>

            {{-- Tensão T --}}
            <th class="table-secondary">Média</th>
            <th class="table-secondary">Máx</th>
            <th class="table-secondary">Mín</th>
            <th class="table-secondary">Última</th>

            {{-- Corrente T --}}
            <th class="table-secondary">Média</th>
            <th class="table-secondary">Máx</th>
            <th class="table-secondary">Mín</th>
            <th class="table-secondary">Última</th>

            {{-- Potência Ativa --}}
            <th class="table-warning">Média</th>
            <th class="table-warning">Máx</th>
            <th class="table-warning">Mín</th>
            <th class="table-warning">Última</th>

            {{-- Potência Reativa --}}
            <th class="table-info">Média</th>
            <th class="table-info">Máx</th>
            <th class="table-info">Mín</th>
            <th class="table-info">Última</th>

            {{-- Potência Aparente --}}
            <th class="table-primary">Média</th>
            <th class="table-primary">Máx</th>
            <th class="table-primary">Mín</th>
            <th class="table-primary">Última</th>

            {{-- Fator Potência --}}
            <th class="table-success">Média</th>
            <th class="table-success">Máx</th>
            <th class="table-success">Mín</th>
            <th class="table-success">Última</th>
        @endif
    </tr>
</thead>