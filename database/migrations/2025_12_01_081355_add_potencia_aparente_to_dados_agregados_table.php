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
        Schema::table('dados_agregados', function (Blueprint $table) {
            // Adiciona as colunas de Potência Aparente após Potência Reativa
            $table->decimal('potencia_aparente_media', 10, 2)->nullable()->after('potencia_reativa_ultima');
            $table->decimal('potencia_aparente_max', 10, 2)->nullable()->after('potencia_aparente_media');
            $table->decimal('potencia_aparente_min', 10, 2)->nullable()->after('potencia_aparente_max');
            $table->decimal('potencia_aparente_ultima', 10, 2)->nullable()->after('potencia_aparente_min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dados_agregados', function (Blueprint $table) {
            $table->dropColumn([
                'potencia_aparente_media',
                'potencia_aparente_max',
                'potencia_aparente_min',
                'potencia_aparente_ultima'
            ]);
        });
    }
};