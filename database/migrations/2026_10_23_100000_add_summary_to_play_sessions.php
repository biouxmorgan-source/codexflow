<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Compte rendu de séance : le résumé écrit par le MJ.
    public function up(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->text('summary')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->dropColumn('summary');
        });
    }
};
