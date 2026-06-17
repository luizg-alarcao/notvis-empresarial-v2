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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // ID do cliente

            // Dados para o NOTVIS identificar quem é quem
            $table->string('nome');
            $table->string('cpf_cnpj')->unique()->nullable();

            // Campo vital para enviarmos mensagens depois!
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();

            // Endereço (útil para futuras Notas Fiscais)
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
