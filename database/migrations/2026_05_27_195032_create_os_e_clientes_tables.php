<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABELA DE FUNCIONÁRIOS (Atendentes e Mecânicos)
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('cargo', ['ATENDENTE', 'MECANICO', 'GERENTE']);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 2. TABELA PRINCIPAL DA ORDEM DE SERVIÇO (O.S.)
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();

            // Relacionamentos: Linkando com a sua tabela de clientes JÁ EXISTENTE
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('atendente_id')->nullable()->constrained('funcionarios')->nullOnDelete();

            // Dados do Veículo na própria O.S.
            $table->string('placa_veiculo', 10)->nullable();
            $table->string('marca_modelo_veiculo')->nullable();
            $table->string('km_veiculo')->nullable();

            // Textos Base
            $table->text('sintoma_reclamacao')->nullable();
            $table->text('observacoes_internas')->nullable();
            $table->text('observacoes_nota')->nullable();

            // Controle de Status e Finanças
            $table->enum('status', ['RASCUNHO', 'ORCAMENTO', 'APROVADO', 'EM_SERVICO', 'FINALIZADO', 'CANCELADO'])->default('RASCUNHO');
            $table->decimal('valor_total_pecas', 10, 2)->default(0);
            $table->decimal('valor_total_servicos', 10, 2)->default(0);

            // Descontos Gerais
            $table->enum('desconto_geral_tipo', ['VALOR', 'PORCENTAGEM'])->nullable();
            $table->decimal('desconto_geral_valor', 10, 2)->default(0);

            $table->decimal('valor_total_liquido', 10, 2)->default(0);

            // Faturamento
            $table->string('forma_pagamento')->nullable();
            $table->enum('status_pagamento', ['PENDENTE', 'PARCIAL', 'PAGO'])->default('PENDENTE');

            $table->timestamps();
        });

        // 3. TABELA DE ITENS DA O.S.
        Schema::create('os_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();

            $table->enum('tipo', ['PECA', 'SERVICO']);

            // Linkando com a sua tabela de produtos JÁ EXISTENTE
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();

            $table->string('descricao');
            $table->decimal('quantidade', 10, 3);
            $table->decimal('valor_unitario', 10, 2);

            $table->enum('desconto_tipo', ['VALOR', 'PORCENTAGEM'])->nullable();
            $table->decimal('desconto_valor', 10, 2)->default(0);

            $table->decimal('valor_total', 10, 2);
            $table->timestamps();
        });

        // 4. TABELA PIVÔ: MECÂNICOS QUE TRABALHARAM NA O.S.
        Schema::create('ordem_servico_mecanico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('mecanico_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_mecanico');
        Schema::dropIfExists('os_itens');
        Schema::dropIfExists('ordens_servico');
        Schema::dropIfExists('funcionarios');
        // Não apagamos os clientes aqui também para proteger seu banco em caso de rollback
    }
};
