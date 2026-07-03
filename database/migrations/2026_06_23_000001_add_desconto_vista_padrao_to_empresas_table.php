<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'desconto_vista_padrao')) {
                $table->decimal('desconto_vista_padrao', 5, 2)->default(5)->after('logomarca');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'desconto_vista_padrao')) {
                $table->dropColumn('desconto_vista_padrao');
            }
        });
    }
};
