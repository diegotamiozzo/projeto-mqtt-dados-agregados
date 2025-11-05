{{-- resources/views/components/leituras/table-row.blade.php --}}
@props(['leitura'])

@php
    $l = $leitura; // Apenas para facilitar o 'copiar e colar'
@endphp

<tr>
    {{-- Identificação --}}
    <td>{{ $l->id_cliente }}</td>
    <td>{{ $l->id_equipamento }}</td>
    <td>{{ \Carbon\Carbon::parse($l->periodo_inicio)->timezone('America/Sao_Paulo')->format('d/m H:i') }}</td>
    <td>{{ \Carbon\Carbon::parse($l->periodo_fim)->timezone('America/Sao_Paulo')->format('d/m H:i') }}</td>
    <td>{{ $l->registros_contagem }}</td>
    
    {{-- Corrente Brunidores --}}
    <td>{{ number_format($l->corrente_brunidores_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_brunidores_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_brunidores_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_brunidores_ultima, 2) }}A</td>
    
    {{-- Corrente Descascadores --}}
    <td>{{ number_format($l->corrente_descascadores_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_descascadores_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_descascadores_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_descascadores_ultima, 2) }}A</td>
    
    {{-- Corrente Polidores --}}
    <td>{{ number_format($l->corrente_polidores_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_polidores_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_polidores_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_polidores_ultima, 2) }}A</td>

    {{-- Temperatura --}}
    <td>{{ number_format($l->temperatura_media, 2) }}°C</td>
    <td>{{ number_format($l->temperatura_max, 2) }}°C</td>
    <td>{{ number_format($l->temperatura_min, 2) }}°C</td>
    <td>{{ number_format($l->temperatura_ultima, 2) }}°C</td>

    {{-- Umidade --}}
    <td>{{ number_format($l->umidade_media, 2) }}%</td>
    <td>{{ number_format($l->umidade_max, 2) }}%</td>
    <td>{{ number_format($l->umidade_min, 2) }}%</td>
    <td>{{ number_format($l->umidade_ultima, 2) }}%</td>

    {{-- Grandezas Elétricas - Fase R --}}
    <td>{{ number_format($l->tensao_r_media, 2) }}V</td>
    <td>{{ number_format($l->tensao_r_max, 2) }}V</td>
    <td>{{ number_format($l->tensao_r_min, 2) }}V</td>
    <td>{{ number_format($l->tensao_r_ultima, 2) }}V</td>
    <td>{{ number_format($l->corrente_r_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_r_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_r_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_r_ultima, 2) }}A</td>
    
    {{-- Grandezas Elétricas - Fase S --}}
    <td>{{ number_format($l->tensao_s_media, 2) }}V</td>
    <td>{{ number_format($l->tensao_s_max, 2) }}V</td>
    <td>{{ number_format($l->tensao_s_min, 2) }}V</td>
    <td>{{ number_format($l->tensao_s_ultima, 2) }}V</td>
    <td>{{ number_format($l->corrente_s_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_s_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_s_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_s_ultima, 2) }}A</td>

    {{-- Grandezas Elétricas - Fase T --}}
    <td>{{ number_format($l->tensao_t_media, 2) }}V</td>
    <td>{{ number_format($l->tensao_t_max, 2) }}V</td>
    <td>{{ number_format($l->tensao_t_min, 2) }}V</td>
    <td>{{ number_format($l->tensao_t_ultima, 2) }}V</td>
    <td>{{ number_format($l->corrente_t_media, 2) }}A</td>
    <td>{{ number_format($l->corrente_t_max, 2) }}A</td>
    <td>{{ number_format($l->corrente_t_min, 2) }}A</td>
    <td>{{ number_format($l->corrente_t_ultima, 2) }}A</td>

    {{-- Grandezas Elétricas - Potências --}}
    <td>{{ number_format($l->potencia_ativa_media, 2) }}kW</td>
    <td>{{ number_format($l->potencia_ativa_max, 2) }}kW</td>
    <td>{{ number_format($l->potencia_ativa_min, 2) }}kW</td>
    <td>{{ number_format($l->potencia_ativa_ultima, 2) }}kW</td>
    <td>{{ number_format($l->potencia_reativa_media, 2) }}kVAr</td>
    <td>{{ number_format($l->potencia_reativa_max, 2) }}kVAr</td>
    <td>{{ number_format($l->potencia_reativa_min, 2) }}kVAr</td>
    <td>{{ number_format($l->potencia_reativa_ultima, 2) }}kVAr</td>
    <td>{{ number_format($l->fator_potencia_media, 4) }}</td>
    <td>{{ number_format($l->fator_potencia_max, 4) }}</td>
    <td>{{ number_format($l->fator_potencia_min, 4) }}</td>
    <td>{{ number_format($l->fator_potencia_ultima, 4) }}</td>

    {{-- Data de Atualização --}}
    <td>{{ \Carbon\Carbon::parse($l->updated_at)->timezone('America/Sao_Paulo')->format('d/m H:i:s') }}</td>
</tr>
