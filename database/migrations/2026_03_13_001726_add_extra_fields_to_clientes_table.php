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
        Schema::table('clientes', function (Blueprint $table) {
            // Documentação (depois do CPF/CNPJ)
            $table->string('rg')->nullable()->after('cpf_cnpj');
            $table->string('inscricao_estadual')->nullable()->after('rg');
            $table->string('inscricao_municipal')->nullable()->after('inscricao_estadual');

            // Dados Pessoais
            $table->date('data_nascimento')->nullable()->after('email');

            // Endereço Detalhado (Substituindo a ideia de um campo "endereco" genérico)
            $table->string('cep')->nullable()->after('estado');
            $table->string('rua')->nullable()->after('cep');
            $table->string('numero')->nullable()->after('rua');
            $table->string('bairro')->nullable()->after('numero');
            $table->string('complemento')->nullable()->after('bairro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            //
        });
    }
};
