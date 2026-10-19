<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les notes d'une feuille personnelle deviennent des blocs « note », déplaçables comme les autres :
        // la note unique d'avant devient le dernier bloc de la première colonne.
        DB::update("update personal_characters set blocks = blocks || jsonb_build_array(jsonb_build_object(
            'id', substr(md5(random()::text), 1, 10), 'kind', 'note', 'title', 'Notes', 'column', 'left', 'text', notes, 'rows', '[]'::jsonb
        )) where notes is not null and notes <> ''");

        Schema::table('personal_characters', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }

    public function down(): void
    {
        Schema::table('personal_characters', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });

        DB::update("update personal_characters set
            notes = (select string_agg(block->>'text', E'\\n\\n') from jsonb_array_elements(blocks) as block where block->>'kind' = 'note'),
            blocks = coalesce((select jsonb_agg(block) from jsonb_array_elements(blocks) as block where coalesce(block->>'kind', 'fields') <> 'note'), '[]'::jsonb)");
    }
};
