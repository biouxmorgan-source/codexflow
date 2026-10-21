<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Accès offert : repère de l'administrateur pour les comptes qu'il ne fait pas payer (amis, testeurs).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('complimentary')->default(false);
            $table->string('complimentary_note', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['complimentary', 'complimentary_note']);
        });
    }
};
