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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();

            // Identificação do Produto
            $table->string('nome');
            $table->string('codigo_barras')->nullable()->unique();
            $table->string('marca')->nullable();

            // Valores Financeiros
            // O tipo 'decimal' é o melhor para dinheiro (10 dígitos no total, 2 após a vírgula)
            $table->decimal('preco_custo', 10, 2)->default(0);
            $table->decimal('preco_venda', 10, 2)->default(0);

            // Este campo é para a nossa futura IA!
            $table->decimal('preco_sugerido_ia', 10, 2)->nullable();

            // Controle de Estoque
            $table->integer('estoque_atual')->default(0);
            $table->integer('estoque_minimo')->default(5); // Avisa quando estiver acabando

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
