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
        Schema::table('produtos', function (Blueprint $table) {
            // Renomeia para a lógica de desconto que você pediu
            $table->renameColumn('preco_minimo', 'preco_venda_vista_desconto');

            // Garante que o estoque possa ser nulo se o controle estiver desligado
            $table->integer('estoque_atual')->nullable()->change();
            $table->integer('estoque_minimo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
