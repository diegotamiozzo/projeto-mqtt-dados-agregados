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
        Schema::table('grandezas_eletricas', function (Blueprint $table) {
            // Adiciona a coluna de Potência Aparente após Potência Reativa
            $table->decimal('potencia_aparente', 10, 2)->nullable()->after('potencia_reativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grandezas_eletricas', function (Blueprint $table) {
            $table->dropColumn('potencia_aparente');
        });
    }
};