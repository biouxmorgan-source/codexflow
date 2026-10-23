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
            ->assertSee(['Importation et structuration de contenus', 'Signaler un contenu illicite'])
            ->assertSee('id="importation"', false)
            ->assertSee('SagaWyn ne revendique aucun droit de propriété sur les créations de ses utilisateurs')
            ->assertSee(['est votre propriété', 'Vous pouvez télécharger à tout moment le contenu de votre compte', 'appartient à l’utilisateur qui les dépose']);
    }

    public function test_the_publisher_page_names_its_owner_even_without_the_env_line(): void
    {
        // Sur le serveur, LEGAL_OWNER peut exister mais vide.
        putenv('LEGAL_OWNER=');
        $this->assertSame('Morgan Bioux', (require config_path('codexflow.php'))['legal']['owner']);
        putenv('LEGAL_OWNER');

        config(['codexflow.legal.owner' => 'Morgan Bioux']);
        $this->get(route('privacy'))->assertOk()->assertSee('nom commercial de Morgan Bioux');
    }

    public function test_the_import_pages_point_to_the_import_section(): void
    {
        $gm = User::factory()->create();
        $world = World::factory()->for($gm, 'owner')->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create(['world_id' => $world->id]);
        $link = route('privacy').'#importation';

        $this->actingAs($gm);
        $this->get(route('imports.ai', $campaign))->assertOk()->assertSee($link, false);
        $this->get(route('imports.ai.archive'))->assertOk()->assertSee($link, false);
    }
}
