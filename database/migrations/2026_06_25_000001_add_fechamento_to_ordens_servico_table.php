<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            if (!Schema::hasColumn('ordens_servico', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('forma_pagamento');
            }

            if (!Schema::hasColumn('ordens_servico', 'finalizado_em')) {
                $table->timestamp('finalizado_em')->nullable()->after('status_pagamento');
            }

            if (!Schema::hasColumn('ordens_servico', 'cupom_fiscal_emitido')) {
                $table->boolean('cupom_fiscal_emitido')->default(false)->after('finalizado_em');
            }

            if (!Schema::hasColumn('ordens_servico', 'comprovante_emitido_em')) {
                $table->timestamp('comprovante_emitido_em')->nullable()->after('cupom_fiscal_emitido');
            }

            if (!Schema::hasColumn('ordens_servico', 'observacao_fechamento')) {
                $table->text('observacao_fechamento')->nullable()->after('comprovante_emitido_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            foreach (['observacao_fechamento', 'comprovante_emitido_em', 'cupom_fiscal_emitido', 'finalizado_em', 'data_vencimento'] as $column) {
                if (Schema::hasColumn('ordens_servico', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
