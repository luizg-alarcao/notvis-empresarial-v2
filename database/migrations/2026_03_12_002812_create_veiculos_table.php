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
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();

            // Esta linha cria a ligação com a tabela de clientes
            // Ela diz: "Este veículo pertence ao cliente tal"
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Dados do Veículo
            $table->string('placa')->unique(); // Placa é única, não pode repetir
            $table->string('marca');           // Ex: Volkswagen, Scania, Honda
            $table->string('modelo');          // Ex: Saveiro, R440, Biz
            $table->string('ano')->nullable();
            $table->string('cor')->nullable();
            $table->text('observacoes')->nullable(); // Para detalhes como "batido", "riscado", etc.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
