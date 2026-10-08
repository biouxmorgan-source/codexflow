<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Characters\Index as CharacterIndex;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Library\GameShow;
use App\Livewire\Library\WorldShow;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\World;
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

    public function test_a_new_character_takes_what_the_game_master_chooses_from_the_old_one(): void
    {
        $give = app(GiveToCharacters::class);
        $give->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $this->morel->id]);
        $give->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Morel ment']);
        $give->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Revolver', 'quantity' => 1]);
        $give->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Lettre de Morel']);
        $grants = $this->harvey->grants()->with('entity')->get()->keyBy(fn ($grant) => $grant->label());
        [$morel, $lie, $revolver, $letter] = [$grants['Morel'], $grants['Morel ment'], $grants['Revolver'], $grants['Lettre de Morel']];

        // Le joueur reçoit un nouveau personnage : le choix de ce qui passe s'ouvre, tout coché.
        $page = Livewire::actingAs($this->gm)->test(CharacterIndex::class, ['campaign' => $this->campaign])
            ->set('name', 'Lucy')
            ->set('playerId', (string) $this->alex->id)
            ->call('create')
            ->assertHasNoErrors()
            ->assertSee('Ce qui passe à Lucy')
            ->assertSee('Revolver');
        $lucy = $this->campaign->playerCharacters()->whereHas('entity', fn ($q) => $q->where('name', 'Lucy'))->firstOrFail();
        $this->assertSame($lucy->id, $page->get('transferTo'));
        $this->assertEqualsCanonicalizing([$morel->id, $lie->id, $revolver->id, $letter->id], $page->get('transferIds'));
        $this->assertFalse($this->harvey->fresh()->is_active);

        // Le MJ décoche l'information : la connaissance est recopiée, le revolver change de main.
        $page->set('transferIds', [$morel->id, $revolver->id])->call('transfer')->assertSet('transferTo', null);

        $this->assertEqualsCanonicalizing(['Morel', 'Revolver'], $lucy->grants()->get()->map->label()->all());
        $this->assertEqualsCanonicalizing(['Morel', 'Morel ment', 'Lettre de Morel'], $this->harvey->grants()->get()->map->label()->all());

        // Rouvert plus tard, le choix ne recopie pas une fiche déjà connue.
        $page->call('openTransfer', $lucy->id)->set('transferIds', [$morel->id, $letter->id])->call('transfer');
        $this->assertSame(1, $lucy->grants()->where('entity_id', $this->morel->id)->count());
        $this->assertTrue($lucy->grants()->where('title', 'Lettre de Morel')->exists());

        // Un joueur ne transmet rien lui-même.
        Livewire::actingAs($this->alex)->test(CharacterIndex::class, ['campaign' => $this->campaign])->assertForbidden();
    }

    public function test_my_campaigns_show_the_last_session_offer_to_resume_and_keep_archives_apart(): void
    {
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()->subWeeks(2), 'ended_at' => now()->subWeeks(2)->addHours(3)]);
        $this->campaign->playSessions()->create(['number' => 2, 'started_at' => now()->setDate(2026, 10, 3)]);

        $page = Livewire::actingAs($this->gm)->test(CampaignIndex::class)
            ->assertSee('Dernière séance le 3 octobre 2026 (2 séances en tout)')
            ->assertSee(route('sessions.live', $this->campaign))
            ->assertSee('Reprendre ▶')
            ->assertDontSee('campagne archivée');

        $page->call('toggleArchive', $this->campaign->id)
            ->assertSee('1 campagne archivée')
            ->assertSee('Réactiver')
            ->assertDontSee('Reprendre ▶');
        $this->assertSame(CampaignStatus::Archived, $this->campaign->fresh()->status);
        $this->assertNotNull($this->campaign->fresh()->archived_at);

        $page->call('toggleArchive', $this->campaign->id);
        $this->assertSame(CampaignStatus::Active, $this->campaign->fresh()->status);
        $this->assertNull($this->campaign->fresh()->archived_at);

        // Le joueur reprend sur la page de son personnage ; il n'archive pas la campagne de son MJ.
        Livewire::actingAs($this->alex)->test(CampaignIndex::class)
            ->assertSee(route('characters.show', [$this->campaign, $this->harvey]))
            ->assertDontSee('Archiver')
            ->call('toggleArchive', $this->campaign->id)
            ->assertForbidden();
    }

    public function test_games_and_worlds_have_their_own_page_for_their_owner(): void
    {
        $world = World::factory()->for($this->gm, 'owner')->create(['name' => 'Arkham']);
        $this->campaign->update(['world_id' => $world->id]);
        $library = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Bibliothèque Orne', 'world_id' => $world->id, 'campaign_id' => null]);
        $game = $this->campaign->gameSystem;
        $rule = $game->rules()->make(['title' => 'Santé mentale', 'zone' => 'public', 'procedure' => 'Jet de SAN.']);
        $rule->forceFill(['user_id' => $this->gm->id])->save();

        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))
            ->assertSee(route('games.show', $game))
            ->assertSee(route('worlds.show', $world));

        $this->actingAs($this->gm)->get(route('worlds.show', $world))
            ->assertOk()
            ->assertSee('Les Ombres')
            ->assertSee('Bibliothèque Orne')
            ->assertSee(route('entities.show', [$this->campaign, $library]));

        $this->actingAs($this->gm)->get(route('games.show', $game))
            ->assertOk()
            ->assertSee('Les Ombres')
            ->assertSee(route('rules.show', [$this->campaign, $rule]));

        Livewire::actingAs($this->gm)->test(WorldShow::class, ['world' => $world])
            ->set('editing', true)
            ->set('description', '**Ville** maudite')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSeeHtml('<strong>Ville</strong>');
        Livewire::actingAs($this->gm)->test(GameShow::class, ['gameSystem' => $game])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors('name');

        // Un joueur de la campagne n'ouvre ni le jeu ni le monde de son MJ.
        $this->actingAs($this->alex)->get(route('worlds.show', $world))->assertForbidden();
        $this->actingAs($this->alex)->get(route('games.show', $game))->assertForbidden();
    }
}
