{{-- resources/views/components/leituras/table.blade.php --}}
@props(['leituras'])

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle table-sm">
        
        {{-- O cabeçalho complexo foi movido para seu próprio componente --}}
        <x-leituras.table-header />

        <tbody class="text-center">
            @forelse($leituras as $leitura)
                {{-- Cada linha da tabela agora é seu próprio componente --}}
                <x-leituras.table-row :leitura="$leitura" />
            @empty
                <tr>
                    <td colspan="60" class="text-center">Nenhum dado agregado encontrado para os filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>