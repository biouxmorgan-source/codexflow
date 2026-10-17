<?php

namespace Tests\Feature\Public;

use App\Models\Campaign;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_legal_page_says_users_own_and_answer_for_their_content(): void
    {
        $this->get(route('privacy'))->assertOk()
            ->assertSee(['Vos contenus vous appartiennent', 'Importation et structuration de contenus', 'Signaler un contenu illicite'])
            ->assertSee('id="importation"', false)
            ->assertSee('SagaWyn ne revendique aucun droit de propriété sur les créations de ses utilisateurs');
    }

    public function test_the_import_pages_point_to_the_import_section(): void
    {
        $gm = User::factory()->create();
        $world = World::factory()->for($gm, 'owner')->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create(['world_id' => $world->id]);
        $link = route('privacy').'#importation';

        $this->actingAs($gm);
        $this->get(route('imports.create', $campaign))->assertOk()->assertSee($link, false);
        $this->get(route('imports.ai', $campaign))->assertOk()->assertSee($link, false);
        $this->get(route('imports.ai.archive'))->assertOk()->assertSee($link, false);
    }
}
