@props(['leituras', 'colunasVisiveis'])

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle table-sm">
        
        <x-leituras.table-header :colunasVisiveis="$colunasVisiveis" />

        <tbody class="text-center">
            @forelse($leituras as $leitura)
                <x-leituras.table-row :leitura="$leitura" :colunasVisiveis="$colunasVisiveis" />
            @empty
                <tr>
                    <td colspan="60" class="text-center">Nenhum dado agregado encontrado para os filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>