<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Dernier joueur d'un personnage libéré : s'il revient, le MJ peut le lui rendre d'un clic. */
    public function up(): void
    {
        Schema::table('player_characters', function (Blueprint $table) {
            $table->foreignId('previous_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('player_characters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_user_id');
        });
    }
};
