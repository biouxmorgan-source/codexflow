<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Show;
use App\Livewire\Search\Index as SearchIndex;
use App\Models\Campaign;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * « Voir comme… » : le MJ voit la campagne exactement comme le joueur d'un personnage, en lecture seule.
 */
class ViewAsTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private User $alex;

    private PlayerCharacter $harvey;

    private User $sam;

    private PlayerCharacter $jack;

    private FieldDefinition $hitPoints;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        [$this->alex, $this->harvey] = $this->character('Harvey', 'Alex');
        [$this->sam, $this->jack] = $this->character('Jack', 'Sam');

        $this->hitPoints = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Points de vie', 'type' => FieldType::Counter, 'zone' => Zone::Public, 'player_editable' => true,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
        $this->harvey->entity->update(['gm_notes' => 'Sera possédé au chapitre 3']);
    }

    /** @return array{0: User, 1: PlayerCharacter} */
    private function character(string $name, string $playerName): array
    {
        $player = User::factory()->create(['name' => $playerName]);
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $name, 'entity_type_id' => EntityType::standard('character')->id]);

        return [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
    }

    private function entity(string $name, string $summary): Entity
    {
        return Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $name, 'summary' => $summary]);
    }

    private function give(PlayerCharacter $character, array $data): void
    {
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$character->id], $data);
    }

    private function note(User $author, PlayerCharacter $character, string $body, string $visibility, array $shareWith = []): void
    {
        $note = new CharacterNote(['body' => $body, 'visibility' => $visibility]);
        $note->character()->associate($character);
        $note->author()->associate($author);
        $note->save();
        $note->sharedWith()->sync(array_map(fn (PlayerCharacter $c) => $c->id, $shareWith));
    }

    public function test_the_game_master_sees_the_page_as_the_player_without_any_game_master_control(): void
    {
        $elias = $this->entity('Jackson Elias', 'Romancier');
        $this->give($this->harvey, ['kind' => 'entity', 'entity_id' => $elias->id]);
        $this->give($this->harvey, ['kind' => 'information', 'title' => 'Adresse à Harlem']);
        $this->give($this->jack, ['kind' => 'information', 'title' => 'Code du coffre']);

        $this->note($this->alex, $this->harvey, 'Journal intime de Harvey', 'private');
        $this->note($this->alex, $this->harvey, 'Harvey pour le MJ', 'gm');
        $this->note($this->sam, $this->jack, 'Jack pour toute la table', 'group');
        $this->note($this->sam, $this->jack, 'Jack se confie à Harvey', 'players', [$this->harvey]);
        $this->note($this->sam, $this->jack, 'Jack pour le MJ', 'gm');

        $normal = route('characters.show', [$this->campaign, $this->harvey]);

        // Vue MJ habituelle : contrôles présents, entrée vers « Voir comme ».
        $this->actingAs($this->gm)->get($normal)
            ->assertOk()
            ->assertSee(['Cacher', 'Ajouter 1 à Points de vie', 'Voir comme Harvey'])
            ->assertDontSee('Lecture seule');

        $this->actingAs($this->gm)->get(route('characters.show', [$this->campaign, $this->harvey, 'comme' => 1]))
            ->assertOk()
            ->assertSee(['Vous voyez la campagne comme Harvey voit sa fiche. Lecture seule.', 'Quitter', $normal], false)
            ->assertSee(['Jackson Elias', 'Adresse à Harlem', 'Harvey pour le MJ', 'Jack pour toute la table', 'Jack se confie à Harvey'])
            ->assertSee(route('characters.entity', [$this->campaign, $this->harvey, $elias, 'comme' => 1]), false)
            // Aucun contrôle du MJ.
            ->assertDontSee(['Cacher', 'Retirer', 'Ajouter 1 à Points de vie', 'Écrire au joueur', 'Fiche complète', 'wire:click="edit"'], false)
            // Ni la zone MJ, ni ce que savent les autres personnages, ni les notes que le joueur ne lit pas.
            ->assertDontSee(['Sera possédé', 'Code du coffre', 'Jack pour le MJ', 'Journal intime'])
            // La recherche de l'en-tête passe par le personnage.
            ->assertSee('name="comme" value="'.$this->harvey->id.'"', false);

        // Les liens de la page restent ouverts au MJ, toujours en « Voir comme ».
        $this->actingAs($this->gm)->get(route('characters.entity', [$this->campaign, $this->harvey, $elias, 'comme' => 1]))
            ->assertOk()
            ->assertSee(['Romancier', 'Lecture seule.']);
    }

    public function test_every_change_is_refused_in_view_as_mode(): void
    {
        $this->give($this->harvey, ['kind' => 'possession', 'title' => 'Revolver']);
        $grant = $this->harvey->grants()->sole();

        $calls = [
            ['revoke', $grant->id],
            ['validateGrant', $grant->id],
            ['adjust', $this->hitPoints->id, 1],
            ['edit'],
            ['save'],
            ['openAdd', 'possession'],
            ['addOwn'],
            ['removeOwn', $grant->id],
            ['startExchange', $grant->id],
            ['exchange'],
            ['saveNote'],
            ['deleteNote', 1],
            ['addIntention'],
        ];

        foreach ($calls as $call) {
            [$method, $arguments] = [array_shift($call), $call];
            Livewire::withQueryParams(['comme' => 1])->actingAs($this->gm)
                ->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
                ->call($method, ...$arguments)
                ->assertForbidden();
        }

        $this->assertModelExists($grant);
        $this->assertNull($this->harvey->entity->fresh()->fieldValue($this->hitPoints));
    }

    public function test_a_player_cannot_view_as_another_character(): void
    {
        $this->actingAs($this->alex)->get(route('characters.show', [$this->campaign, $this->jack, 'comme' => 1]))->assertForbidden();
        $this->actingAs($this->alex)->get(route('characters.show', [$this->campaign, $this->harvey, 'comme' => 1]))->assertForbidden();
        $this->actingAs($this->alex)->get(route('search.index', [$this->campaign, 'q' => 'Harlem', 'comme' => $this->jack->id]))->assertForbidden();
        Livewire::actingAs($this->alex)->test(SearchIndex::class, ['campaign' => $this->campaign])
            ->set('asCharacterId', (string) $this->jack->id)
            ->assertForbidden();

        // Ni un personnage d'une autre campagne pour le MJ.
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $stranger = $other->playerCharacters()->create(['entity_id' => Entity::factory()->for($this->gm, 'owner')->for($other)->create()->id]);
        $this->actingAs($this->gm)->get(route('search.index', [$this->campaign, 'q' => 'Harlem', 'comme' => $stranger->id]))->assertForbidden();
    }

    public function test_search_as_a_character_finds_only_what_it_knows(): void
    {
        $elias = $this->entity('Jackson Elias', 'Écrivain disparu');
        $this->entity('Mukunga', 'Écrivain du culte');
        $this->give($this->harvey, ['kind' => 'entity', 'entity_id' => $elias->id]);
        $this->give($this->jack, ['kind' => 'information', 'title' => 'Un écrivain à Harlem']);

        $titles = fn (array $results) => collect($results)->map(fn ($items) => $items->pluck('title')->all())->all();

        $this->assertSame(['entities' => ['Jackson Elias']], $titles((new GlobalSearch($this->campaign, $this->gm, 'écrivain', $this->harvey))->run()));
        $this->assertContains('Mukunga', $titles((new GlobalSearch($this->campaign, $this->gm, 'écrivain'))->run())['entities']);

        $this->actingAs($this->gm)->get(route('search.index', [$this->campaign, 'q' => 'écrivain', 'comme' => $this->harvey->id]))
            ->assertOk()
            ->assertSee(['Vous voyez la campagne comme Harvey voit sa fiche. Lecture seule.', 'Jackson Elias', 'Fiches connues'])
            ->assertSee(route('characters.entity', [$this->campaign, $this->harvey, $elias, 'comme' => 1]), false)
            ->assertDontSee(['Mukunga', 'Un écrivain à Harlem']);
    }
}
