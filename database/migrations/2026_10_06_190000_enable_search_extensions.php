<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * unaccent est une extension « de confiance » : le propriétaire de la base peut l'activer
     * sans être superutilisateur. Elle permet de trouver « Arrivée » en tapant « arrivee ».
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
    }

    public function down(): void
    {
        // L'extension peut servir ailleurs : on la laisse en place.
    }
};
