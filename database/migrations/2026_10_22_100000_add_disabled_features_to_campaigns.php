<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Fonctions que le MJ a coupées dans cette campagne : vide, tout est disponible. */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->jsonb('disabled_features')->default('[]');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('disabled_features');
        });
    }
};
