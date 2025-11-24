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
        Schema::table('users', function (Blueprint $table) {
            // Cria a coluna VARCHAR(255), aceita NULL, posicionada após o email
            $table->string('external_client_id', 255)
                  ->nullable()
                  ->after('email');

            // Opcional, mas recomendado: Cria um índice para deixar as buscas rápidas
            $table->index('external_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove o índice e a coluna caso precise desfazer (rollback)
            $table->dropIndex(['external_client_id']);
            $table->dropColumn('external_client_id');
        });
    }
};
