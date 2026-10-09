<?php

namespace Database\Seeders\Browser;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignEvent;
use App\Support\CampaignFeatures;
use Illuminate\Database\Seeder;

/**
 * Ce qui arrive « d'ailleurs » pendant qu'un test navigateur a sa page ouverte,
 * choisi par la variable E2E_ACTION : notify (une notification pour le joueur) ou
 * disable-graph (le MJ coupe le graphe des relations).
 */
class TransversalActionSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = Campaign::where('name', TransversalSeeder::CAMPAIGN)->latest('id')->firstOrFail();

        match (getenv('E2E_ACTION')) {
            'notify' => User::where('email', 'e2e-joueur-t@loremundi.test')->firstOrFail()
                ->notify(new CampaignEvent($campaign, 'reveal', 'Une nouvelle piste vous attend.', route('campaigns.show', $campaign))),
            'disable-graph' => in_array('graph', $campaign->disabled_features ?? [], true) ?: CampaignFeatures::toggle($campaign, 'graph'),
            default => throw new \InvalidArgumentException('E2E_ACTION attendu : notify ou disable-graph'),
        };
    }
}
