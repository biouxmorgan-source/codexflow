<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\Characters\Show as CharacterShow;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Évolutions retenues après la recette V1 (backlog de la console d'administration).
 */
class BacklogV1Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private Entity $morel;

    private Entity $manoir;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->morel = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel']);
        $this->manoir = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Manoir Morgause']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    public function test_player_notes_suggest_only_the_sheets_the_character_knows(): void
    {
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $this->morel->id]);

        $page = Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey]);

        $this->assertSame(['Morel'], array_column($page->instance()->suggestEntities(''), 'name'));
        $this->assertSame([], $page->instance()->suggestEntities('Manoir'));

        // Le lien choisi est enregistré tel quel et s'affiche comme un lien vers ce que connaît le personnage.
        $page->set('noteBody', "**Soupçon** : [[Morel|{$this->morel->id}]] ment.")
            ->set('noteVisibility', 'gm')
            ->call('saveNote')
            ->assertHasNoErrors()
            ->assertSeeHtml('<strong>Soupçon</strong>')
            ->assertSeeHtml(route('characters.entity', [$this->campaign, $this->harvey, $this->morel]));
    }

    public function test_the_session_page_shows_the_player_notes_taken_during_it_except_private_ones(): void
    {
        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
        $note = fn (string $body, string $visibility, ?int $sessionId) => $this->harvey->notes()->make(['body' => $body, 'visibility' => $visibility])
            ->forceFill(['user_id' => $this->alex->id, 'play_session_id' => $sessionId])->save();
        $note('Le manoir cache une crypte.', 'gm', $session->id);
        $note('Je ne fais pas confiance au MJ.', 'private', $session->id);
        $note('Une autre séance.', 'gm', null);

        $this->actingAs($this->gm)->get(route('sessions.show', [$this->campaign, $session]))
            ->assertOk()
            ->assertSee('Notes des joueurs')
            ->assertSee('Harvey')
            ->assertSee('Le manoir cache une crypte.')
            ->assertDontSee('Je ne fais pas confiance au MJ.')
            ->assertDontSee('Une autre séance.');

        $this->actingAs($this->alex)->get(route('sessions.show', [$this->campaign, $session]))->assertForbidden();
    }
}
