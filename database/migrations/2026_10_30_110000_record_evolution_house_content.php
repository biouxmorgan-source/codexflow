<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Demande de Morgan (2026-10-11), notée dans les évolutions et livrée en 0.60.0. */
    public function up(): void
    {
        DB::table('evolutions')->insert([
            'title' => 'Contenu maison et version officielle',
            'detail' => "Une coche « Contenu maison » sur les fiches (PNJ, sorts, objets…) ; les règles ont l’origine « Maison ». Sans coche, c’est officiel.\n"
                .'Pour un contenu maison, la version officielle remplacée se note à côté (ex. D&D 5 : initiative au D10 au lieu du D20), en zone MJ. Badge « Maison » et filtre dans les listes, repris par l’export et l’import.',
            'status' => 'done',
            'priority' => 'normal',
            'target' => 'v0.60.0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('evolutions')->where('title', 'Contenu maison et version officielle')->delete();
    }
};
