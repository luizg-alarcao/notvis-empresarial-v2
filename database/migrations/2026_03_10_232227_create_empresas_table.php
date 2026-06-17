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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id(); // Cria um número de identificação único para cada empresa (ID)

            // Dados principais
            $table->string('nome_fantasia'); // O nome que aparecerá no WhatsApp
            $table->string('razao_social')->nullable(); // Nome formal (opcional)
            $table->string('cnpj')->unique(); // CNPJ (o unique não deixa cadastrar dois iguais)

            // Contatos e Localização
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('endereco')->nullable();

            // Identidade Visual
            $table->string('logomarca')->nullable(); // Aqui guardaremos o caminho da foto do logo

            $table->timestamps(); // Cria as colunas 'criado em' e 'atualizado em' automaticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
