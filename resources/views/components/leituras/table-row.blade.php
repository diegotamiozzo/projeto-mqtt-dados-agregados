@props(['leitura', 'colunasVisiveis'])

@php
    $l = $leitura;
@endphp

<tr>
    {{-- Identificação --}}
    <td>{{ $l->id_cliente }}</td>
    <td>{{ $l->id_equipamento }}</td>
    <td>{{ \Carbon\Carbon::parse($l->periodo_inicio)->format('d/m H:i') }}</td>
    <td>{{ \Carbon\Carbon::parse($l->periodo_fim)->format('d/m H:i') }}</td>
    <td>{{ $l->registros_contagem }}</td>
    
    {{-- Corrente Brunidores --}}
    @if($colunasVisiveis['brunidores'])
        <td>{{ $l->corrente_brunidores_media ? number_format($l->corrente_brunidores_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_brunidores_max ? number_format($l->corrente_brunidores_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_brunidores_min ? number_format($l->corrente_brunidores_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_brunidores_ultima ? number_format($l->corrente_brunidores_ultima, 2) . 'A' : '-' }}</td>
    @endif
    
    {{-- Corrente Descascadores --}}
    @if($colunasVisiveis['descascadores'])
        <td>{{ $l->corrente_descascadores_media ? number_format($l->corrente_descascadores_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_descascadores_max ? number_format($l->corrente_descascadores_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_descascadores_min ? number_format($l->corrente_descascadores_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_descascadores_ultima ? number_format($l->corrente_descascadores_ultima, 2) . 'A' : '-' }}</td>
    @endif
    
    {{-- Corrente Polidores --}}
    @if($colunasVisiveis['polidores'])
        <td>{{ $l->corrente_polidores_media ? number_format($l->corrente_polidores_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_polidores_max ? number_format($l->corrente_polidores_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_polidores_min ? number_format($l->corrente_polidores_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_polidores_ultima ? number_format($l->corrente_polidores_ultima, 2) . 'A' : '-' }}</td>
    @endif

    {{-- Temperatura --}}
    @if($colunasVisiveis['temperatura'])
        <td>{{ $l->temperatura_media ? number_format($l->temperatura_media, 2) . '°C' : '-' }}</td>
        <td>{{ $l->temperatura_max ? number_format($l->temperatura_max, 2) . '°C' : '-' }}</td>
        <td>{{ $l->temperatura_min ? number_format($l->temperatura_min, 2) . '°C' : '-' }}</td>
        <td>{{ $l->temperatura_ultima ? number_format($l->temperatura_ultima, 2) . '°C' : '-' }}</td>
    @endif

    {{-- Umidade --}}
    @if($colunasVisiveis['umidade'])
        <td>{{ $l->umidade_media ? number_format($l->umidade_media, 2) . '%' : '-' }}</td>
        <td>{{ $l->umidade_max ? number_format($l->umidade_max, 2) . '%' : '-' }}</td>
        <td>{{ $l->umidade_min ? number_format($l->umidade_min, 2) . '%' : '-' }}</td>
        <td>{{ $l->umidade_ultima ? number_format($l->umidade_ultima, 2) . '%' : '-' }}</td>
    @endif

    {{-- Grandezas Elétricas --}}
    @if($colunasVisiveis['grandezas_eletricas'])
        {{-- Fase R --}}
        <td>{{ $l->tensao_r_media ? number_format($l->tensao_r_media, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_r_max ? number_format($l->tensao_r_max, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_r_min ? number_format($l->tensao_r_min, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_r_ultima ? number_format($l->tensao_r_ultima, 2) . 'V' : '-' }}</td>
        <td>{{ $l->corrente_r_media ? number_format($l->corrente_r_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_r_max ? number_format($l->corrente_r_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_r_min ? number_format($l->corrente_r_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_r_ultima ? number_format($l->corrente_r_ultima, 2) . 'A' : '-' }}</td>
        
        {{-- Fase S --}}
        <td>{{ $l->tensao_s_media ? number_format($l->tensao_s_media, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_s_max ? number_format($l->tensao_s_max, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_s_min ? number_format($l->tensao_s_min, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_s_ultima ? number_format($l->tensao_s_ultima, 2) . 'V' : '-' }}</td>
        <td>{{ $l->corrente_s_media ? number_format($l->corrente_s_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_s_max ? number_format($l->corrente_s_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_s_min ? number_format($l->corrente_s_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_s_ultima ? number_format($l->corrente_s_ultima, 2) . 'A' : '-' }}</td>

        {{-- Fase T --}}
        <td>{{ $l->tensao_t_media ? number_format($l->tensao_t_media, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_t_max ? number_format($l->tensao_t_max, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_t_min ? number_format($l->tensao_t_min, 2) . 'V' : '-' }}</td>
        <td>{{ $l->tensao_t_ultima ? number_format($l->tensao_t_ultima, 2) . 'V' : '-' }}</td>
        <td>{{ $l->corrente_t_media ? number_format($l->corrente_t_media, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_t_max ? number_format($l->corrente_t_max, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_t_min ? number_format($l->corrente_t_min, 2) . 'A' : '-' }}</td>
        <td>{{ $l->corrente_t_ultima ? number_format($l->corrente_t_ultima, 2) . 'A' : '-' }}</td>

        {{-- Potências --}}
        <td>{{ $l->potencia_ativa_media ? number_format($l->potencia_ativa_media, 2) . 'kW' : '-' }}</td>
        <td>{{ $l->potencia_ativa_max ? number_format($l->potencia_ativa_max, 2) . 'kW' : '-' }}</td>
        <td>{{ $l->potencia_ativa_min ? number_format($l->potencia_ativa_min, 2) . 'kW' : '-' }}</td>
        <td>{{ $l->potencia_ativa_ultima ? number_format($l->potencia_ativa_ultima, 2) . 'kW' : '-' }}</td>
        <td>{{ $l->potencia_reativa_media ? number_format($l->potencia_reativa_media, 2) . 'kVAr' : '-' }}</td>
        <td>{{ $l->potencia_reativa_max ? number_format($l->potencia_reativa_max, 2) . 'kVAr' : '-' }}</td>
        <td>{{ $l->potencia_reativa_min ? number_format($l->potencia_reativa_min, 2) . 'kVAr' : '-' }}</td>
        <td>{{ $l->potencia_reativa_ultima ? number_format($l->potencia_reativa_ultima, 2) . 'kVAr' : '-' }}</td>
        <td>{{ $l->fator_potencia_media ? number_format($l->fator_potencia_media, 4) : '-' }}</td>
        <td>{{ $l->fator_potencia_max ? number_format($l->fator_potencia_max, 4) : '-' }}</td>
        <td>{{ $l->fator_potencia_min ? number_format($l->fator_potencia_min, 4) : '-' }}</td>
        <td>{{ $l->fator_potencia_ultima ? number_format($l->fator_potencia_ultima, 4) : '-' }}</td>
    @endif

    {{-- Data de Atualização --}}
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($l->updated_at)->format('d/m H:i:s') }}</td>
</tr>