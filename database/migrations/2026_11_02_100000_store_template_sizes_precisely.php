<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les gabarits se règlent dans l'unité de l'échelle (3 m, 10 ft) : en cases, il faut plus de
 * deux décimales pour retrouver exactement la mesure saisie (1 m à 1,5 m la case = 0,6667 case).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_templates', function (Blueprint $table) {
            $table->decimal('size', 10, 4)->change();
            $table->decimal('size2', 10, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('map_templates', function (Blueprint $table) {
            $table->decimal('size', 6, 2)->change();
            $table->decimal('size2', 6, 2)->nullable()->change();
        });
    }
};
