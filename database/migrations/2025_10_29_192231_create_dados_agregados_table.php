<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTA: Os campos de dados e índices estão baseados no seu DDL e no index.blade.php.
        // A chave UNIQUE KEY foi adicionada para o ON DUPLICATE KEY UPDATE.
        Schema::create('dados_agregados', function (Blueprint $table) {
            $table->id();

            // Identificação do cliente e equipamento
            $table->string('id_cliente')->collation('utf8mb4_unicode_ci');
            $table->string('id_equipamento')->collation('utf8mb4_unicode_ci');

            // Período da agregação
            $table->timestamp('periodo_inicio');
            $table->timestamp('periodo_fim');

            // Corrente Brunidores
            $table->decimal('corrente_brunidores_media', 10, 2)->nullable();
            $table->decimal('corrente_brunidores_max', 10, 2)->nullable();
            $table->decimal('corrente_brunidores_min', 10, 2)->nullable();
            $table->decimal('corrente_brunidores_ultima', 10, 2)->nullable();

            // Corrente Descascadores
            $table->decimal('corrente_descascadores_media', 10, 2)->nullable();
            $table->decimal('corrente_descascadores_max', 10, 2)->nullable();
            $table->decimal('corrente_descascadores_min', 10, 2)->nullable();
            $table->decimal('corrente_descascadores_ultima', 10, 2)->nullable();

            // Corrente Polidores
            $table->decimal('corrente_polidores_media', 10, 2)->nullable();
            $table->decimal('corrente_polidores_max', 10, 2)->nullable();
            $table->decimal('corrente_polidores_min', 10, 2)->nullable();
            $table->decimal('corrente_polidores_ultima', 10, 2)->nullable();

            // Temperaturas
            $table->decimal('temperatura_media', 10, 2)->nullable();
            $table->decimal('temperatura_max', 10, 2)->nullable();
            $table->decimal('temperatura_min', 10, 2)->nullable();
            $table->decimal('temperatura_ultima', 10, 2)->nullable();

            // Umidades
            $table->decimal('umidade_media', 10, 2)->nullable();
            $table->decimal('umidade_max', 10, 2)->nullable();
            $table->decimal('umidade_min', 10, 2)->nullable();
            $table->decimal('umidade_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Tensão R
            $table->decimal('tensao_r_media', 10, 2)->nullable();
            $table->decimal('tensao_r_max', 10, 2)->nullable();
            $table->decimal('tensao_r_min', 10, 2)->nullable();
            $table->decimal('tensao_r_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Tensão S
            $table->decimal('tensao_s_media', 10, 2)->nullable();
            $table->decimal('tensao_s_max', 10, 2)->nullable();
            $table->decimal('tensao_s_min', 10, 2)->nullable();
            $table->decimal('tensao_s_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Tensão T
            $table->decimal('tensao_t_media', 10, 2)->nullable();
            $table->decimal('tensao_t_max', 10, 2)->nullable();
            $table->decimal('tensao_t_min', 10, 2)->nullable();
            $table->decimal('tensao_t_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Corrente R
            $table->decimal('corrente_r_media', 10, 2)->nullable();
            $table->decimal('corrente_r_max', 10, 2)->nullable();
            $table->decimal('corrente_r_min', 10, 2)->nullable();
            $table->decimal('corrente_r_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Corrente S
            $table->decimal('corrente_s_media', 10, 2)->nullable();
            $table->decimal('corrente_s_max', 10, 2)->nullable();
            $table->decimal('corrente_s_min', 10, 2)->nullable();
            $table->decimal('corrente_s_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Corrente T
            $table->decimal('corrente_t_media', 10, 2)->nullable();
            $table->decimal('corrente_t_max', 10, 2)->nullable();
            $table->decimal('corrente_t_min', 10, 2)->nullable();
            $table->decimal('corrente_t_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Potência Ativa (kW)
            $table->decimal('potencia_ativa_media', 10, 2)->nullable();
            $table->decimal('potencia_ativa_max', 10, 2)->nullable();
            $table->decimal('potencia_ativa_min', 10, 2)->nullable();
            $table->decimal('potencia_ativa_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Potência Reativa (kVAr)
            $table->decimal('potencia_reativa_media', 10, 2)->nullable();
            $table->decimal('potencia_reativa_max', 10, 2)->nullable();
            $table->decimal('potencia_reativa_min', 10, 2)->nullable();
            $table->decimal('potencia_reativa_ultima', 10, 2)->nullable();

            // Grandezas elétricas - Fator de Potência (FP)
            $table->decimal('fator_potencia_media', 10, 4)->nullable();
            $table->decimal('fator_potencia_max', 10, 4)->nullable();
            $table->decimal('fator_potencia_min', 10, 4)->nullable();
            $table->decimal('fator_potencia_ultima', 10, 4)->nullable();

            // Contagem de registros agregados
            $table->integer('registros_contagem');

            // Timestamps Laravel
            $table->timestamps();

            // --- CHAVE ÚNICA ESSENCIAL PARA O 'ON DUPLICATE KEY UPDATE' ---
            // Garante que só haja uma linha para um equipamento/cliente/período.
            $table->unique(['id_cliente', 'id_equipamento', 'periodo_inicio'], 'unique_agregacao');

            // Índices para acelerar consultas (mantidos os seus índices de consulta)
            $table->index('id_cliente');
            $table->index('id_equipamento');
            $table->index('periodo_fim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dados_agregados');
    }
};