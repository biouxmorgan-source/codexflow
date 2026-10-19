<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les champs d'une feuille personnelle se rangent dans des blocs nommés, déplaçables :
        // [{id, title, column, rows: [{type, label, value, max?, note?, checkbox?, checked?}]}].
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->jsonb('blocks')->default('[]');
        });

        DB::update("update personal_characters set blocks = jsonb_build_array(jsonb_build_object(
            'id', substr(md5(random()::text), 1, 10), 'title', '', 'column', 'left', 'rows', fields
        )) where jsonb_array_length(fields) > 0");

        Schema::table('personal_characters', function (Blueprint $table) {
            $table->dropColumn('fields');
        });
    }

    public function down(): void
    {
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->jsonb('fields')->default('[]');
        });

        DB::update("update personal_characters set fields = coalesce((
            select jsonb_agg(row_value) from jsonb_array_elements(blocks) as block, jsonb_array_elements(block->'rows') as row_value
        ), '[]'::jsonb)");

        Schema::table('personal_characters', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }
};
