<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Show;
use App\Livewire\Sessions\Live;
use App\Models\Campaign;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerSpaceTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, array{0: User, 1: PlayerCharacter}> */
    private array $table = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();

        foreach (['Harvey' => 'Alex', 'Jack' => 'Sam', 'Rita' => 'Lou'] as $characterName => $playerName) {
            $player = User::factory()->create(['name' => $playerName]);
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $characterName, 'entity_type_id' => EntityType::standard('character')->id]);
            $this->table[$characterName] = [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
        }
    }

    private function write(string $who, string $body, string $visibility, array $shareWith = []): void
    {
        [$player, $character] = $this->table[$who];

        Livewire::actingAs($player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->set('noteBody', $body)
            ->set('noteVisibility', $visibility)
            ->set('noteShares', array_map(fn ($name) => $this->table[$name][1]->id, $shareWith))
            ->call('saveNote')
            ->assertHasNoErrors();
    }

    private function page(User $user, string $character): TestResponse
    {
        return $this->actingAs($user)->get(route('characters.show', [$this->campaign, $this->table[$character][1]]));
    }

    public function test_note_sharing_is_enforced_for_each_reader(): void
    {
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);

        $this->write('Harvey', 'Journal intime', 'private');
        $this->write('Harvey', 'Pour le MJ seulement', 'gm');
        $this->write('Harvey', 'Entre Harvey et Jack', 'players', ['Jack']);
        $this->write('Harvey', 'Pour toute la table', 'group');

        $note = CharacterNote::where('body', 'Pour le MJ seulement')->sole();
        $this->assertNotNull($note->play_session_id, 'La note est rattachée à la séance en cours.');

        [$alex] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];
        [$lou] = $this->table['Rita'];

        $this->page($alex, 'Harvey')->assertSee(['Journal intime', 'Pour le MJ seulement', 'Entre Harvey et Jack', 'Pour toute la table', 'Moi seul']);
        $this->page($sam, 'Jack')->assertSee(['Entre Harvey et Jack', 'Pour toute la table'])->assertDontSee(['Journal intime', 'Pour le MJ seulement']);
        $this->page($lou, 'Rita')->assertSee('Pour toute la table')->assertDontSee(['Journal intime', 'Pour le MJ seulement', 'Entre Harvey et Jack']);
        $this->page($this->gm, 'Harvey')->assertSee(['Pour le MJ seulement', 'Entre Harvey et Jack', 'Pour toute la table'])->assertDontSee('Journal intime');

        // Seul l'auteur modifie ou supprime ses notes ; personne n'écrit pour un autre personnage.
        Livewire::actingAs($sam)->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->table['Harvey'][1]])->assertForbidden();
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->table['Harvey'][1]])
            ->call('deleteNote', $note->id)
            ->assertForbidden();
        $this->assertModelExists($note);
    }

    public function test_a_player_asks_to_test_a_rule_and_the_game_master_plays_it(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        $chase = new Rule(['title' => 'Poursuite', 'zone' => Zone::Public]);
        $chase->owner()->associate($this->gm);
        $chase->campaign()->associate($this->campaign);
        $chase->save();
        $secret = new Rule(['title' => 'Règle secrète', 'zone' => Zone::GameMaster]);
        $secret->owner()->associate($this->gm);
        $secret->campaign()->associate($this->campaign);
        $secret->save();

        Livewire::actingAs($alex)->test(Show::class, ['campaign' => $this->campaign, 'character' => $harvey])
            ->assertSee('Tester : Poursuite')
            ->assertDontSee('Règle secrète')
            ->set('intentionRuleId', (string) $secret->id)
            ->call('addIntention')
            ->assertHasErrors('intentionRuleId')
            ->set('intentionRuleId', (string) $chase->id)
            ->call('addIntention')
            ->assertHasNoErrors();

        $item = ToPlayItem::sole();
        $this->assertSame($harvey->id, $item->player_character_id);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('start');
        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->assertSee(['Demande à tester la règle « Poursuite »', 'Demandé par Harvey'])
            ->call('markPlayed', $item->id);

        $this->page($alex, 'Harvey')->assertSee(['Demande à tester la règle « Poursuite »', 'jouée']);
    }

    public function test_the_player_journal_only_lists_what_concerns_their_character(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];
        $this->actingAs($this->gm);
        $give = app(GiveToCharacters::class);
        $give->handle($this->campaign, [$harvey->id], ['kind' => 'possession', 'title' => 'Lampe tempête']);
        $give->handle($this->campaign, [$jack->id], ['kind' => 'information', 'title' => 'Le secret de Jack']);
        GiveToCharacters::revoke($harvey->grants()->sole());

        $this->page($alex, 'Harvey')
            ->assertSee(['Journal', 'Le MJ a donné objet', 'Le MJ a repris objet', 'Lampe tempête'])
            ->assertDontSee('Le secret de Jack');

        // Le MJ peut aussi révéler en pleine séance.
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
        $this->actingAs($this->gm)->get(route('sessions.live', $this->campaign))->assertSee('Révéler ou donner');
    }

    /** Parcours de recette 12 : un joueur ne trouve que ce que son personnage connaît, jamais la zone MJ. */
    public function test_a_player_searches_only_what_their_character_knows(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];
        $secretField = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Culte', 'type' => FieldType::Text, 'zone' => Zone::GameMaster]);
        $publicField = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Métier', 'type' => FieldType::Text, 'zone' => Zone::Public]);

        $elias = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Jackson Elias', 'summary' => 'Écrivain', 'description' => 'Ami de longue date',
            'gm_notes' => 'Indicateur secret', 'field_values' => [(string) $secretField->id => 'Langue Sanglante', (string) $publicField->id => 'Romancier'],
        ]);
        Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Mukunga', 'summary' => 'Écrivain inconnu']);

        $this->actingAs($this->gm);
        $give = app(GiveToCharacters::class);
        $give->handle($this->campaign, [$harvey->id], ['kind' => 'entity', 'entity_id' => $elias->id]);
        $give->handle($this->campaign, [$harvey->id], ['kind' => 'information', 'title' => 'Adresse à Harlem', 'body' => 'Chez un écrivain']);
        $this->write('Jack', 'Un écrivain nous suit', 'group');
        $this->write('Jack', 'Écrivain : mon secret', 'private');

        $search = fn (User $user, string $q) => collect((new GlobalSearch($this->campaign, $user, $q))->run())
            ->map(fn ($items) => $items->pluck('title')->all())->all();

        $this->assertSame([
            'entities' => ['Jackson Elias'],
            'knowledge' => ['Adresse à Harlem'],
            'notes' => ['Note de Jack'],
        ], $search($alex, 'écrivain'));
        $this->assertSame(['entities' => ['Jackson Elias']], $search($alex, 'romancier'));
        $this->assertSame([], $search($alex, 'indicateur'));
        $this->assertSame([], $search($alex, 'Sanglante'));
        $this->assertSame([], $search($alex, 'Mukunga'));
        $this->assertSame(['notes' => ['Note de Jack', 'Note de Jack']], $search($sam, 'écrivain'), 'Sam lit ses propres notes, Harvey ne connaît rien pour lui.');

        $this->actingAs($alex)->get(route('search.index', [$this->campaign, 'q' => 'écrivain']))
            ->assertOk()
            ->assertSee([route('characters.entity', [$this->campaign, $harvey, $elias]), 'Fiches connues'], false)
            ->assertDontSee(['Mukunga', 'Indicateur', 'mon secret']);
    }
}
