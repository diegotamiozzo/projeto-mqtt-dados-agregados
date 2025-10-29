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
        Schema::create('umidades', function (Blueprint $table) {
            $table->id();
            $table->string('id_cliente')->collation('utf8mb4_unicode_ci');
            $table->string('id_equipamento')->collation('utf8mb4_unicode_ci');
            $table->decimal('umidade', 10, 2);
            $table->timestamp('timestamp')->useCurrent();
            $table->index(['id_cliente', 'id_equipamento', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umidades');
    }
};
