<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Carte de campagne : une des cartes, la vue d'ensemble d'où l'on descend vers les sous-cartes.
        Schema::table('table_maps', function (Blueprint $table) {
            $table->boolean('is_campaign_map')->default(false);
        });
        DB::statement('CREATE UNIQUE INDEX table_maps_one_campaign_map ON table_maps (campaign_id) WHERE is_campaign_map');

        // Points d'intérêt posés par le MJ sur une carte : ils mènent à une sous-carte, une scène ou une fiche.
        // Coordonnées en pixels de l'image d'origine, comme les jetons.
        Schema::create('map_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_map_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100);
            $table->text('note')->nullable();
            $table->string('color', 7)->default('#b45309');
            $table->decimal('x', 10, 2);
            $table->decimal('y', 10, 2);
            $table->foreignId('target_map_id')->nullable()->constrained('table_maps')->nullOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('entity_id')->nullable()->constrained()->nullOnDelete();
            // Le nom du point s'affiche sur l'écran de table ; la note et les liens restent au MJ.
            $table->boolean('shown_at_table')->default(false);
            $table->timestamps();
            $table->index('target_map_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_points');
        DB::statement('DROP INDEX IF EXISTS table_maps_one_campaign_map');
        Schema::table('table_maps', function (Blueprint $table) {
            $table->dropColumn('is_campaign_map');
        });
    }
};
