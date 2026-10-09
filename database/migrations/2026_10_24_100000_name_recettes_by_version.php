<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Les cahiers de recette portent le nom de la version testée (« Recette v0.25.0 »),
 * et le cahier de la recette finale v0.34.0 est ajouté s'il manque.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('recettes')->update(['title' => DB::raw("'Recette v' || version")]);

        if (! app()->runningUnitTests()) {
            Artisan::call('codexflow:recettes');
        }
    }
};
