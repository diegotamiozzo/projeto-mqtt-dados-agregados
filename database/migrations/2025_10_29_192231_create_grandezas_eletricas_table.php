<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grandezas_eletricas', function (Blueprint $table) {
            $table->id();
            $table->string('id_cliente')->collation('utf8mb4_unicode_ci');
            $table->string('id_equipamento')->collation('utf8mb4_unicode_ci');

            $table->decimal('tensao_r', 10, 2)->nullable();
            $table->decimal('tensao_s', 10, 2)->nullable();
            $table->decimal('tensao_t', 10, 2)->nullable();

            $table->decimal('corrente_r', 10, 2)->nullable();
            $table->decimal('corrente_s', 10, 2)->nullable();
            $table->decimal('corrente_t', 10, 2)->nullable();

            $table->decimal('potencia_ativa', 10, 2)->nullable();
            $table->decimal('potencia_reativa', 10, 2)->nullable();
            $table->decimal('fator_potencia', 10, 4)->nullable();

            $table->boolean('agregado')->default(false); 
            $table->timestamp('timestamp')->useCurrent();

            $table->index(['id_cliente', 'id_equipamento', 'timestamp']);

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('grandezas_eletricas');
    }
};
