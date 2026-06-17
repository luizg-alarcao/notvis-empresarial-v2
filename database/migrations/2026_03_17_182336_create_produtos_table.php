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
            $table->string('nome');
            $table->string('codigo_interno')->nullable()->unique(); // Aquele código curto (Ex: 43691)
            $table->string('codigo_barras')->nullable()->unique();
            $table->string('unidade')->default('UN'); // UN, PC, LT, KG

            // Preços
            $table->decimal('preco_custo', 12, 2)->default(0);
            $table->decimal('preco_venda_vista', 12, 2)->default(0);
            $table->decimal('preco_venda_prazo', 12, 2)->default(0);

            // Estoque
            $table->integer('estoque_atual')->default(0);
            $table->integer('estoque_minimo')->default(0);
            $table->string('localizacao')->nullable(); // Ex: Prateleira A

            // Fiscal (Pensando no Futuro)
            $table->string('ncm', 8)->nullable();

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
