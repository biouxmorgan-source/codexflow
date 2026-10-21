<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre des déclarations de droits : une ligne par import confirmé, avec la version exacte
 * du texte accepté (App\Support\ContentDeclarations). Gardée après la suppression du compte,
 * le temps de la prescription, pour prouver l'engagement pris.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_declarations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_email');
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            // « import » (import privé) ; « publication » prévu pour une future bibliothèque publique.
            $table->string('kind', 20);
            // csv-entities, csv-fields, csv-rules, csv-scenes, archive, template
            $table->string('import_type', 30);
            $table->string('declaration_version', 20);
            $table->boolean('declaration_accepted');
            $table->string('locale', 5);
            $table->timestampTz('accepted_at');
            // pending → success | failed
            $table->string('import_status', 10)->default('pending');
            $table->timestampTz('account_deleted_at')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_declarations');
    }
};
