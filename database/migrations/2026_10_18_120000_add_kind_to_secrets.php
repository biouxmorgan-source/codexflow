<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nature d'un secret : rumeur (peut-être fausse), indice (une piste) ou vérité.
        Schema::table('secrets', function (Blueprint $table) {
            $table->string('kind', 10)->default('truth');
        });

        DB::table('bug_reports')
            ->where('source', 'recette')
            ->where('title', 'Secrets sans type ni état')
            ->update(['status' => 'fixed', 'fixed_in' => '0.19.0', 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('secrets', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
