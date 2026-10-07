<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Give;
use App\Livewire\Characters\Show;
use App\Livewire\Journal\Index as JournalIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class GrantTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, array{0: User, 1: PlayerCharacter}> */
    private array $table = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);

        foreach (['Harvey' => 'Alex', 'Jack' => 'Sam'] as $characterName => $playerName) {
            $player = User::factory()->create(['name' => $playerName]);
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $characterName, 'entity_type_id' => EntityType::standard('character')->id]);
            $this->table[$characterName] = [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
        }
    }

    private function sheet(string $name): TestResponse
    {
        [$player, $character] = $this->table[$name];

        return $this->actingAs($player)->get(route('characters.show', [$this->campaign, $character]));
    }

    public function test_an_information_revealed_to_one_character_reaches_only_that_player_and_the_journal(): void
    {
        [, $harvey] = $this->table['Harvey'];

        Livewire::actingAs($this->gm)->test(Give::class, ['campaign' => $this->campaign, 'characterId' => $harvey->id])
            ->set('title', 'Le carnet de Jackson')
            ->set('body', 'Une adresse à Harlem')
            ->call('give')
            ->assertHasNoErrors()
            ->assertSee('Révélé à 1 personnage.');

        $this->sheet('Harvey')->assertSee(['Connaissances', 'Le carnet de Jackson', 'Une adresse à Harlem']);
        $this->sheet('Jack')->assertDontSee('Le carnet de Jackson');

        $entry = ActivityLog::where('subject_type', 'grant')->sole();
        $this->assertSame('Le carnet de Jackson à Harvey', $entry->subject_label);
        Livewire::actingAs($this->gm)->test(JournalIndex::class, ['campaign' => $this->campaign])
            ->assertSee(['Morgane', 'a révélé', 'information', 'Le carnet de Jackson à Harvey']);
    }

    public function test_an_object_given_appears_in_possessions_and_can_be_taken_back(): void
    {
        [, $harvey] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];

        Livewire::actingAs($this->gm)->test(Give::class, ['campaign' => $this->campaign])
            ->set('kind', 'possession')
            ->set('title', 'Balles de .38')
            ->set('quantity', '12')
            ->set('selected', [$harvey->id, $jack->id])
            ->call('give')
            ->assertHasNoErrors()
            ->assertSee('Donné à 2 personnages.');

        $this->sheet('Harvey')->assertSee(['Possessions', 'Balles de .38 ×12']);
        $this->sheet('Jack')->assertSee('Balles de .38 ×12');

        $grant = $jack->grants()->sole();
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'character' => $jack])
            ->call('revoke', $grant->id);
        $this->sheet('Jack')->assertDontSee('Balles de .38');
        $this->assertSame(['created', 'created', 'deleted'], ActivityLog::where('subject_type', 'grant')->orderBy('id')->pluck('event')->all());

        // Un joueur ne peut ni donner ni reprendre.
        [$alex] = $this->table['Harvey'];
        Livewire::actingAs($alex)->test(Show::class, ['campaign' => $this->campaign, 'character' => $harvey])
            ->call('revoke', $harvey->grants()->sole()->id)
            ->assertForbidden();
        Livewire::actingAs($alex)->test(Give::class, ['campaign' => $this->campaign])->assertForbidden();
    }

    public function test_a_revealed_sheet_shows_only_its_public_zone_and_links_to_known_sheets(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $age = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Âge', 'type' => FieldType::Number, 'zone' => Zone::Public]);
        $cult = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Culte', 'type' => FieldType::Text, 'zone' => Zone::GameMaster]);

        $mukunga = Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create(['name' => 'Mukunga', 'description' => null]);
        $elias = Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create([
            'name' => 'Jackson Elias', 'summary' => 'Écrivain', 'description' => 'Craint [[Mukunga|'.$mukunga->id.']] et connaît [[Harvey]].',
            'gm_notes' => 'Déjà condamné', 'field_values' => [(string) $age->id => 41, (string) $cult->id => 'Langue Sanglante'],
        ]);

        Livewire::actingAs($this->gm)->test(Give::class, ['campaign' => $this->campaign, 'fixedKind' => 'entity', 'entityId' => $elias->id])
            ->set('selected', [$harvey->id])
            ->call('give')
            ->assertHasNoErrors()
            ->assertSee('déjà connu');

        $url = route('characters.entity', [$this->campaign, $harvey, $elias]);
        $this->sheet('Harvey')->assertSee([$url, 'Jackson Elias', 'Écrivain'], false);

        $this->actingAs($alex)->get($url)
            ->assertOk()
            ->assertSee(['Jackson Elias', 'Écrivain', 'Craint Mukunga', 'Âge', '41'])
            ->assertDontSee(['Déjà condamné', 'Culte', 'Langue Sanglante', route('characters.entity', [$this->campaign, $harvey, $mukunga])]);

        // Une fiche non révélée, ou révélée à un autre, reste introuvable ; le MJ peut la cacher à nouveau.
        $this->actingAs($alex)->get(route('characters.entity', [$this->campaign, $harvey, $mukunga]))->assertNotFound();
        $this->actingAs($sam)->get(route('characters.entity', [$this->campaign, $jack, $elias]))->assertNotFound();
        $this->actingAs($sam)->get($url)->assertForbidden();

        // Révéler deux fois ne crée pas de doublon.
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'entity', 'entity_id' => $elias->id]);
        $this->assertSame(1, $harvey->grants()->count());

        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'character' => $harvey])
            ->call('revoke', $harvey->grants()->sole()->id);
        $this->actingAs($alex)->get($url)->assertNotFound();
        $this->assertSame('a caché', ActivityLog::where('subject_type', 'grant')->latest('id')->first()->verb());
    }

    public function test_a_document_given_can_be_opened_by_that_player_only(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];

        $path = UploadedFile::fake()->create('lettre.pdf', 10, 'application/pdf')->store('documents', 'local');
        $document = new Document(['title' => 'Lettre de Jackson', 'disk' => 'local', 'path' => $path, 'original_name' => 'lettre.pdf', 'mime_type' => 'application/pdf', 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        Livewire::actingAs($this->gm)->test(Give::class, ['campaign' => $this->campaign, 'fixedKind' => 'document', 'documentId' => $document->id])
            ->set('selected', [$harvey->id])
            ->call('give')
            ->assertSee('Donné à 1 personnage.');

        $this->sheet('Harvey')->assertSee(['Documents', 'Lettre de Jackson']);
        $this->actingAs($alex)->get(route('characters.document', [$this->campaign, $harvey, $document]))->assertOk();
        $this->actingAs($sam)->get(route('characters.document', [$this->campaign, $jack, $document]))->assertNotFound();
        $this->actingAs($alex)->get(route('documents.file', $document))->assertForbidden();

        $this->actingAs($this->gm)->get(route('documents.show', [$this->campaign, $document]))->assertSee('Donner aux joueurs');
        $this->assertSame(1, CharacterGrant::count());
    }
}
