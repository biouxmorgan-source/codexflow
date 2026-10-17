<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lancers enregistrés par chacun pour lui-même dans une campagne (rechargés, modifiés au fil du jeu). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_memberships', function (Blueprint $table) {
            // [{id, label, formula}]
            $table->jsonb('dice_presets')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_memberships', function (Blueprint $table) {
            $table->dropColumn('dice_presets');
        });
    }
};
