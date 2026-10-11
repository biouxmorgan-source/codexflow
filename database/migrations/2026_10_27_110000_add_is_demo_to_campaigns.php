<?php

use App\Actions\Demo\LoadDemoCampaign;
use App\Models\Campaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La campagne de démonstration est repérée, pour ne pas fausser les statistiques de la console.
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('status');
        });

        // Démonstrations déjà chargées : retrouvées par leur nom, dans chaque langue (« Nom (2) » si rechargée).
        foreach (LoadDemoCampaign::locales() as $locale) {
            $name = LoadDemoCampaign::text($locale)['campaign']['name'];
            Campaign::where(fn ($query) => $query->where('name', $name)->orWhere('name', 'like', addcslashes($name, '%_\\').' (%)'))
                ->update(['is_demo' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
