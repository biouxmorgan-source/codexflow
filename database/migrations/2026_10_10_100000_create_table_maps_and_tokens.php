<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cartes de la table : une image de la campagne, une grille facultative et la vue montrée à l'écran.
        // Les coordonnées sont en pixels de l'image d'origine.
        Schema::create('table_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->boolean('grid_enabled')->default(false);
            $table->decimal('grid_size', 8, 2);
            $table->decimal('grid_offset_x', 8, 2)->default(0);
            $table->decimal('grid_offset_y', 8, 2)->default(0);
            $table->string('grid_color', 7)->default('#1c1917');
            $table->decimal('scale_value', 8, 2)->nullable();
            $table->string('scale_unit', 20)->nullable();
            // Vue de l'écran de table : centre (x, y) et zoom (1 = carte entière).
            $table->decimal('view_x', 10, 2)->nullable();
            $table->decimal('view_y', 10, 2)->nullable();
            $table->decimal('view_zoom', 6, 3)->default(1);
            // Règle temporaire : {x1, y1, x2, y2}, effacée par le MJ.
            $table->jsonb('ruler')->nullable();
            $table->timestamps();
        });

        Schema::create('map_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_map_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 100);
            $table->string('color', 7)->default('#b45309');
            $table->decimal('x', 10, 2);
            $table->decimal('y', 10, 2);
            // Diamètre en cases.
            $table->decimal('size', 4, 2)->default(1);
            $table->boolean('hidden')->default(false);
            $table->boolean('show_label')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_tokens');
        Schema::dropIfExists('table_maps');
    }
};
