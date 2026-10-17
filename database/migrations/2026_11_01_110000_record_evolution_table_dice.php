<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Demande de Morgan (2026-10-17), notée dans les évolutions et livrée en 0.64.0. */
    public function up(): void
    {
        DB::table('evolutions')->insert([
            'title' => 'Lancers de dés à la table',
            'detail' => "Bouton « Dés » sur chaque page de la campagne : type et nombre de dés, lecture (additionner, atteindre un seuil, rester sous un seuil, compter les réussites, garder les meilleurs, lire en chiffres), bonus, dés qui relancent sur le max ou sur le 1.\n"
                .'Le MJ demande un lancer à toute la table ou à certains personnages, à la vue de tous ou en discret, avec un seuil fixé, saisi par chaque joueur ou caché. Lancers enregistrés pour la table, affichage sur l’écran de table, aucun nom de jeu.',
            'status' => 'done',
            'priority' => 'normal',
            'target' => 'v0.64.0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('evolutions')->where('title', 'Lancers de dés à la table')->delete();
    }
};
