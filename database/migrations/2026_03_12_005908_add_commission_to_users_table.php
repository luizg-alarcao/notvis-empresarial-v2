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
        Schema::table('users', function (Blueprint $table) {
            // Seguindo sua ideia: opção para marcar se é comissionado
            $table->boolean('eh_comissionado')->default(false)->after('password');

            // Se for comissionado, qual a porcentagem padrão?
            $table->decimal('percentual_comissao', 5, 2)->nullable()->after('eh_comissionado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
