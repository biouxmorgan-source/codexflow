<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Formule du compte : gratuite par défaut (joueurs invités compris), premium réglée par l'administrateur.
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan', 20)->default('free');
            $table->date('plan_started_at')->nullable();
            $table->date('plan_ends_at')->nullable();
            // Quota propre au compte, en Mo ; vide = celui de sa formule.
            $table->unsignedInteger('storage_quota_mb')->nullable();
        });

        // Connexions : la date seulement, jamais l'adresse IP ni l'appareil.
        Schema::create('user_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('logged_in_at');
            $table->index(['user_id', 'logged_in_at']);
        });

        // Réglages de l'installation (formules, quotas, fonctions de la formule gratuite).
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->jsonb('value');
            $table->timestamps();
        });

        // Les signalements deviennent le backlog : bugs et évolutions, avec statut et priorité.
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->string('kind', 20)->default('bug');
            $table->string('title')->nullable();
            $table->string('status', 20)->default('new');
            $table->string('priority', 10)->default('normal');
            $table->string('fixed_in', 32)->nullable();
            $table->string('source', 20)->default('report');
            $table->text('admin_note')->nullable();
        });
        DB::table('bug_reports')->whereNotNull('resolved_at')->update(['status' => 'fixed']);
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->dropColumn('resolved_at');
        });

        // Cahiers de recette, conservés version après version.
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('version', 32);
            $table->date('tested_on');
            $table->decimal('score', 4, 1)->nullable();
            $table->decimal('score_before', 4, 1)->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->unique(['title', 'version']);
        });

        Schema::create('recette_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recette_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->unsignedInteger('position');
            $table->text('feature');
            $table->text('expected');
            $table->text('tests');
            $table->text('gaps');
            $table->text('result');
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedTinyInteger('score_before')->nullable();
        });

        // Les cahiers livrés avec l'application (database/data/recettes) et leurs suites au backlog.
        // Pas en test : chaque test part d'une base vide.
        if (! app()->runningUnitTests()) {
            Artisan::call('codexflow:recettes');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recette_items');
        Schema::dropIfExists('recettes');

        Schema::table('bug_reports', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable();
        });
        DB::table('bug_reports')->where('status', 'fixed')->update(['resolved_at' => now()]);
        DB::table('bug_reports')->where('source', '!=', 'report')->delete();
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->dropColumn(['kind', 'title', 'status', 'priority', 'fixed_in', 'source', 'admin_note']);
        });

        Schema::dropIfExists('settings');
        Schema::dropIfExists('user_logins');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_started_at', 'plan_ends_at', 'storage_quota_mb']);
        });
    }
};
