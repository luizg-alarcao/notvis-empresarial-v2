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
            // Identificação e Categorias
            $table->text('descricao_detalhada')->nullable()->after('nome');
            $table->string('marca')->nullable()->after('tipo');
            $table->string('categoria')->nullable()->after('marca');

            // Financeiro Pro
            $table->decimal('margem_lucro', 8, 2)->default(0)->after('preco_custo');
            $table->decimal('preco_minimo', 12, 2)->default(0)->after('preco_venda_prazo');
            $table->boolean('permite_desconto')->default(true)->after('preco_minimo');

            // Controle de Estoque Avançado
            $table->boolean('controla_estoque')->default(true)->after('estoque_minimo');
            $table->integer('estoque_maximo')->nullable()->after('controla_estoque');
            $table->string('lote')->nullable()->after('estoque_maximo');
            $table->date('data_validade')->nullable()->after('lote');

            // Dados Fiscais para NF-e
            $table->string('cfop', 4)->nullable()->after('ncm');
            $table->string('cst_csosn', 3)->nullable()->after('cfop');
            $table->integer('origem')->default(0)->after('cst_csosn'); // 0 = Nacional
            $table->decimal('aliquota_icms', 5, 2)->default(0)->after('origem');

            // Status
            $table->boolean('ativo')->default(true)->after('updated_at');
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
