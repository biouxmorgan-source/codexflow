<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gabarits de zone posés sur une carte (cercle, carré, rectangle, cône, ligne), pour voir
 * qui est pris dans un sort ou une explosion. Dimensions en cases, position en pixels de l'image.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_map_id')->constrained()->cascadeOnDelete();
            $table->string('shape', 12);
            // Rayon du cercle, côté du carré, largeur du rectangle, longueur du cône ou de la ligne.
            $table->decimal('size', 6, 2);
            // Hauteur du rectangle, largeur de la ligne.
            $table->decimal('size2', 6, 2)->nullable();
            $table->decimal('x', 10, 2);
            $table->decimal('y', 10, 2);
            $table->decimal('angle', 6, 2)->default(0);
            $table->string('color', 7)->default('#b91c1c');
            $table->boolean('hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_templates');
    }
};
