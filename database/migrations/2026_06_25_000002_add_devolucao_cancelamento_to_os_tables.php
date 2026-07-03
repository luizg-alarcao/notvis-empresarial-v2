<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('os_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('os_itens', 'quantidade_devolvida')) {
                $table->decimal('quantidade_devolvida', 10, 3)->default(0)->after('quantidade');
            }

            if (!Schema::hasColumn('os_itens', 'devolvido_em')) {
                $table->timestamp('devolvido_em')->nullable()->after('valor_total');
            }

            if (!Schema::hasColumn('os_itens', 'motivo_devolucao')) {
                $table->text('motivo_devolucao')->nullable()->after('devolvido_em');
            }
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            if (!Schema::hasColumn('ordens_servico', 'cancelado_em')) {
                $table->timestamp('cancelado_em')->nullable()->after('comprovante_emitido_em');
            }

            if (!Schema::hasColumn('ordens_servico', 'motivo_cancelamento')) {
                $table->text('motivo_cancelamento')->nullable()->after('cancelado_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('os_itens', function (Blueprint $table) {
            foreach (['motivo_devolucao', 'devolvido_em', 'quantidade_devolvida'] as $column) {
                if (Schema::hasColumn('os_itens', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            foreach (['motivo_cancelamento', 'cancelado_em'] as $column) {
                if (Schema::hasColumn('ordens_servico', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
