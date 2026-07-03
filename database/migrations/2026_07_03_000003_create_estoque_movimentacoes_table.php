<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('estoque_atual', 12, 3)->nullable()->default(0)->change();
            $table->decimal('estoque_minimo', 12, 3)->nullable()->default(0)->change();
            $table->decimal('estoque_maximo', 12, 3)->nullable()->change();
        });

        Schema::create('estoque_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20);
            $table->decimal('quantidade', 12, 3);
            $table->decimal('estoque_anterior', 12, 3)->default(0);
            $table->decimal('estoque_posterior', 12, 3)->default(0);
            $table->string('motivo', 120);
            $table->text('observacao')->nullable();
            $table->string('origem', 80)->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->timestamps();

            $table->index(['produto_id', 'created_at']);
            $table->index(['tipo', 'created_at']);
            $table->index(['origem', 'origem_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentacoes');
    }
};
