<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Cahier de recette finale v0.34.0 (database/data/recettes/v3.json) dans la console d'administration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pas en test : chaque test part d'une base vide.
        if (! app()->runningUnitTests()) {
            Artisan::call('codexflow:recettes');
        }
    }
};
