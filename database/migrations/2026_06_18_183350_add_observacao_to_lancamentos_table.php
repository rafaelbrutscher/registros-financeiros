<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->string('observacao', 255)->nullable()->after('descricao');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropColumn('observacao');
        });
    }
};