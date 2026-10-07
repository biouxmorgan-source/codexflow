<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un secret : une information du MJ, indépendante des fiches, reliée à plusieurs éléments.
        Schema::create('secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->index(['campaign_id', 'title']);
        });

        foreach (['entity' => 'entities', 'document' => 'documents', 'scene' => 'scenes'] as $single => $table) {
            Schema::create("{$single}_secret", function (Blueprint $pivot) use ($single, $table) {
                $pivot->foreignId('secret_id')->constrained()->cascadeOnDelete();
                $pivot->foreignId("{$single}_id")->constrained($table)->cascadeOnDelete();
                $pivot->primary(['secret_id', "{$single}_id"]);
            });
        }

        // Historique des révélations : pendant quelle séance et quelle scène.
        Schema::table('character_grants', function (Blueprint $table) {
            // Un secret supprimé reste connu des personnages à qui il a été révélé.
            $table->foreignId('secret_id')->nullable()->after('rule_id')->constrained()->nullOnDelete();
            $table->foreignId('play_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::statement('create unique index character_grants_secret_once on character_grants (player_character_id, secret_id) where secret_id is not null');
    }

    public function down(): void
    {
        Schema::table('character_grants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scene_id');
            $table->dropConstrainedForeignId('play_session_id');
            $table->dropConstrainedForeignId('secret_id');
        });

        foreach (['scene', 'document', 'entity'] as $single) {
            Schema::dropIfExists("{$single}_secret");
        }

        Schema::dropIfExists('secrets');
    }
};
