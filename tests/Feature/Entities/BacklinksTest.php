<?php

namespace Tests\Feature\Entities;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\SessionNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sheet_lists_the_sheets_rules_and_session_notes_that_mention_it(): void
    {
        $gm = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $morel = Entity::factory()->for($gm, 'owner')->create(['name' => 'Morel', 'campaign_id' => $campaign->id]);
        Entity::factory()->for($gm, 'owner')->create(['name' => 'La boutique', 'campaign_id' => $campaign->id, 'description' => 'Tenue par [[Morel|'.$morel->id.']].']);
        Entity::factory()->for($gm, 'owner')->create(['name' => 'Sans lien', 'campaign_id' => $campaign->id, 'description' => 'Rien à voir.']);

        $rule = new Rule(['title' => 'Marchander', 'procedure' => 'Contre [[Morel]], malus de 20 %.']);
        $rule->owner()->associate($gm);
        $rule->campaign()->associate($campaign);
        $rule->save();

        $session = $campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
        $note = new SessionNote(['body' => 'Les joueurs soupçonnent [[Morel|'.$morel->id.']].']);
        $note->author()->associate($gm);
        $session->notes()->save($note);

        $this->actingAs($gm)->get(route('entities.show', [$campaign, $morel]))
            ->assertOk()
            ->assertSee('La boutique')
            ->assertSee('Marchander')
            ->assertSee('Session 1')
            ->assertSee('Les joueurs soupçonnent Morel.')
            ->assertDontSee('Sans lien');
    }
}
