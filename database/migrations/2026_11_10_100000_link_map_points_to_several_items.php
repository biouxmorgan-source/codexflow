<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un point d'intérêt se relie à plusieurs scènes et plusieurs fiches (une ville, ses PNJ, les scènes qui s'y jouent).
        Schema::create('map_point_scene', function (Blueprint $table) {
            $table->foreignId('map_point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['map_point_id', 'scene_id']);
            $table->index('scene_id');
        });

        Schema::create('map_point_entity', function (Blueprint $table) {
            $table->foreignId('map_point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['map_point_id', 'entity_id']);
            $table->index('entity_id');
        });

        DB::statement('INSERT INTO map_point_scene (map_point_id, scene_id) SELECT id, scene_id FROM map_points WHERE scene_id IS NOT NULL');
        DB::statement('INSERT INTO map_point_entity (map_point_id, entity_id) SELECT id, entity_id FROM map_points WHERE entity_id IS NOT NULL');

        Schema::table('map_points', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scene_id');
            $table->dropConstrainedForeignId('entity_id');
        });
    }

    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('entity_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::statement('UPDATE map_points SET scene_id = (SELECT scene_id FROM map_point_scene WHERE map_point_id = map_points.id ORDER BY position LIMIT 1)');
        DB::statement('UPDATE map_points SET entity_id = (SELECT entity_id FROM map_point_entity WHERE map_point_id = map_points.id ORDER BY position LIMIT 1)');

        Schema::dropIfExists('map_point_entity');
        Schema::dropIfExists('map_point_scene');
    }
};
