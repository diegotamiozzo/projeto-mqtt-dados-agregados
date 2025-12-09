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
        // Adiciona índice na coluna 'agregado' para otimizar queries de agregação
        Schema::table('corrente_brunidores', function (Blueprint $table) {
            $table->index('agregado');
        });

        Schema::table('corrente_descascadores', function (Blueprint $table) {
            $table->index('agregado');
        });

        Schema::table('corrente_polidores', function (Blueprint $table) {
            $table->index('agregado');
        });

        Schema::table('temperaturas', function (Blueprint $table) {
            $table->index('agregado');
        });

        Schema::table('umidades', function (Blueprint $table) {
            $table->index('agregado');
        });

        Schema::table('grandezas_eletricas', function (Blueprint $table) {
            $table->index('agregado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corrente_brunidores', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });

        Schema::table('corrente_descascadores', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });

        Schema::table('corrente_polidores', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });

        Schema::table('temperaturas', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });

        Schema::table('umidades', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });

        Schema::table('grandezas_eletricas', function (Blueprint $table) {
            $table->dropIndex(['agregado']);
        });
    }
};
