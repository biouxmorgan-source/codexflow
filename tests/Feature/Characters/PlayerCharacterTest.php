<?php

namespace Tests\Feature\Characters;

use App\Actions\Imports\ImportFieldDefinitions;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Index;
use App\Livewire\Characters\Show;
use App\Livewire\Fields\Manage;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\World;
use App\Support\Import\TabularFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerCharacterTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(PlayerCharacter::DISK);
        Storage::fake(Entity::FILES_DISK);

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->player = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
    }

    private function field(string $name, FieldType $type, Zone $zone = Zone::Public, bool $editable = false): FieldDefinition
    {
        return $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => $name, 'type' => $type, 'zone' => $zone, 'player_editable' => $editable,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
    }

    private function createCharacter(string $name = 'Harvey Walters'): PlayerCharacter
    {
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('name', $name)
            ->set('playerId', (string) $this->player->id)
            ->call('create')
            ->assertHasNoErrors();

        return PlayerCharacter::latest('id')->firstOrFail();
    }

    public function test_the_player_reads_only_the_public_part_of_their_character_and_its_pdf_sheet(): void
    {
        $strength = $this->field('FOR', FieldType::Number);
        $secret = $this->field('Folie latente', FieldType::Text, Zone::GameMaster);

        $character = $this->createCharacter();
        $entity = $character->entity;
        $this->assertSame($this->campaign->id, $entity->campaign_id, 'Le personnage est une fiche propre à la campagne.');
        $this->assertSame($this->player->id, $character->user_id);

        $entity->fill(['summary' => 'Journaliste', 'description' => 'Ami de [[Jackson Elias]]', 'gm_notes' => 'Sera possédé'])
            ->setFieldValues([$strength->id => 60, $secret->id => 'Phobie des profondeurs']);
        $entity->save();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('sheets.'.$character->id, UploadedFile::fake()->create('harvey.pdf', 120, 'application/pdf'))
            ->assertHasNoErrors()
            ->assertSee('harvey.pdf');
        Storage::disk(PlayerCharacter::DISK)->assertExists($character->fresh()->sheet_path);

        // Le joueur retrouve son personnage depuis ses campagnes.
        $this->actingAs($this->player)->get(route('campaigns.index'))
            ->assertSee([route('characters.show', [$this->campaign, $character]), 'Votre personnage', 'Harvey Walters']);

        $this->get(route('characters.show', [$this->campaign, $character]))
            ->assertOk()
            ->assertSee(['Harvey Walters', 'Journaliste', 'Ami de Jackson Elias', 'FOR', '60', route('characters.sheet', [$this->campaign, $character])])
            ->assertDontSee(['Sera possédé', 'Folie latente', 'Phobie des profondeurs', '[[Jackson']);

        $this->get(route('characters.sheet', [$this->campaign, $character]))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        // Personne d'autre : ni un autre joueur, ni un inconnu.
        $other = User::factory()->create();
        $this->campaign->members()->attach($other, ['role' => CampaignRole::Player->value]);
        $this->actingAs($other)->get(route('characters.show', [$this->campaign, $character]))->assertForbidden();
        $this->get(route('characters.sheet', [$this->campaign, $character]))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('characters.show', [$this->campaign, $character]))->assertForbidden();

        // Un joueur retiré de la campagne perd l'accès.
        $this->campaign->members()->detach($this->player);
        $this->actingAs($this->player)->get(route('characters.show', [$this->campaign, $character]))->assertForbidden();
    }

    public function test_counters_and_editable_fields_are_changed_by_the_player_and_kept_in_the_journal(): void
    {
        $hp = $this->field('PV', FieldType::Counter, editable: true);
        $sanity = $this->field('SAN', FieldType::Counter);
        $money = $this->field('Argent', FieldType::Text, editable: true);
        $character = $this->createCharacter();
        $character->entity->setFieldValues([$hp->id => ['value' => 10, 'max' => 12], $sanity->id => ['value' => 55]]);
        $character->entity->save();

        $sheet = Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->assertSee(['PV', '10 / 12', 'SAN', '55'])
            ->call('adjust', $hp->id, -1)
            ->call('adjust', $hp->id, -1);
        $this->assertEquals(['value' => 8, 'max' => 12], $character->entity->fresh()->fieldValue($hp));

        $sheet->call('adjust', $sanity->id, -1)->assertForbidden();

        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->call('edit')
            ->set('values.'.$hp->id, '7 / 13')
            ->set('values.'.$money->id, '35 $')
            ->call('save')
            ->assertHasNoErrors();
        $entity = $character->entity->fresh();
        $this->assertEquals(['value' => 7, 'max' => 13], $entity->fieldValue($hp));
        $this->assertSame('35 $', $entity->fieldValue($money));
        $this->assertSame(['value' => 55], $entity->fieldValue($sanity), 'Un champ non modifiable ne bouge pas.');

        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->call('edit')
            ->set('values.'.$hp->id, 'beaucoup')
            ->call('save')
            ->assertHasErrors('values.'.$hp->id);

        $entry = ActivityLog::where('subject_type', 'entity')->where('event', 'updated')->where('user_id', $this->player->id)->oldest('id')->firstOrFail();
        $this->assertEquals(['field_values.'.$hp->id => ['old' => ['value' => 10, 'max' => 12], 'new' => ['value' => 9, 'max' => 12]]], $entry->diff);
        $this->assertContains(['PV', '10 / 12', '9 / 12'], $entry->lines([$hp->id => $hp]));

        // Fiche verrouillée : le joueur ne peut plus rien changer, le MJ si.
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('toggleLock', $character->id);
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character->fresh()])
            ->assertSee('fiche verrouillée')
            ->assertDontSee('Retirer 1 à PV')
            ->call('adjust', $hp->id, -1)
            ->assertForbidden();
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character->fresh()])
            ->assertSee('Vous voyez la fiche comme Alex la voit')
            ->call('adjust', $hp->id, 1);
        $this->assertEquals(['value' => 8, 'max' => 13], $character->entity->fresh()->fieldValue($hp));
    }

    public function test_a_player_has_one_active_character_per_campaign(): void
    {
        $first = $this->createCharacter('Harvey Walters');
        $second = $this->createCharacter('Jack Brady');

        $this->assertFalse($first->fresh()->is_active, 'Le nouveau personnage remplace l\'ancien.');
        $this->assertTrue($second->fresh()->is_active);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('toggleActive', $first->id)
            ->assertSee(['Harvey Walters', 'Jack Brady', 'au repos']);
        $this->assertTrue($first->fresh()->is_active);
        $this->assertFalse($second->fresh()->is_active);

        // Une fiche existante de la campagne peut devenir un personnage joueur ; la retirer garde la fiche.
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Rebecca', 'entity_type_id' => EntityType::standard('character')->id]);
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('entityChoice', (string) $npc->id)
            ->call('create')
            ->assertHasNoErrors()
            ->call('remove', PlayerCharacter::where('entity_id', $npc->id)->value('id'));
        $this->assertModelExists($npc);

        $this->actingAs($this->player)->get(route('characters.index', $this->campaign))->assertForbidden();
    }

    public function test_a_pregenerated_world_character_is_copied_into_the_campaign(): void
    {
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign->update(['world_id' => $world->id]);
        $hp = $this->field('PV', FieldType::Counter, editable: true);
        Storage::disk(Entity::FILES_DISK)->put('entities/harvey.jpg', 'portrait');
        $pregen = Entity::factory()->for($this->gm, 'owner')->for($world)->create([
            'name' => 'Harvey Walters', 'summary' => 'Journaliste', 'gm_notes' => 'Secret',
            'entity_type_id' => EntityType::standard('character')->id, 'image_path' => 'entities/harvey.jpg',
        ]);
        $pregen->setFieldValues([$hp->id => ['value' => 11, 'max' => 11]]);
        $pregen->save();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['Fiches du monde', 'Harvey Walters'])
            ->set('entityChoice', (string) $pregen->id)
            ->set('playerId', (string) $this->player->id)
            ->call('create')
            ->assertHasNoErrors();

        $character = PlayerCharacter::latest('id')->firstOrFail();
        $copy = $character->entity;
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertDontSee('Fiches du monde');
        $this->assertNotEquals($pregen->id, $copy->id);
        $this->assertSame($this->campaign->id, $copy->campaign_id);
        $this->assertNull($copy->world_id);
        $this->assertSame(['Harvey Walters', 'Journaliste', 'Secret'], [$copy->name, $copy->summary, $copy->gm_notes]);
        $this->assertEquals(['value' => 11, 'max' => 11], $copy->fieldValue($hp));
        $this->assertNotSame($pregen->image_path, $copy->image_path);
        Storage::disk(Entity::FILES_DISK)->assertExists($copy->image_path);

        // Le joueur abîme sa copie : le prétiré du monde reste intact pour une autre partie.
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->call('adjust', $hp->id, -3);
        $this->assertEquals(['value' => 8, 'max' => 11], $copy->fresh()->fieldValue($hp));
        $this->assertEquals(['value' => 11, 'max' => 11], $pregen->fresh()->fieldValue($hp));
    }

    public function test_a_number_field_turned_into_a_counter_keeps_its_values(): void
    {
        $hp = $this->field('PV', FieldType::Number);
        $character = $this->createCharacter();
        $entity = $character->entity;
        $entity->setFieldValues([$hp->id => 11]);
        $entity->save();

        Livewire::actingAs($this->gm)->test(Manage::class, ['campaign' => $this->campaign])
            ->call('edit', $hp->id)
            ->set('type', FieldType::Counter->value)
            ->set('playerEditable', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['value' => 11, 'max' => 11], $entity->fresh()->fieldValue($hp->fresh()));
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->call('adjust', $hp->id, -2)
            ->assertSee('9 / 11');

        $hp->fresh()->update(['type' => FieldType::Number]);
        $this->assertEquals(9, $entity->fresh()->fieldValue($hp));
    }

    public function test_counter_fields_are_typed_imported_and_exported_like_the_others(): void
    {
        $this->assertSame([['value' => 9, 'max' => 12], null], FieldType::Counter->parse(' 9 / 12 '));
        $this->assertSame([['value' => 2.5], null], FieldType::Counter->parse('2,5'));
        $this->assertNotNull(FieldType::Counter->parse('9/12/3')[1]);
        $this->assertSame('9 / 12', FieldType::Counter->format(['value' => 9, 'max' => 12]));
        $this->assertSame(['value' => 0, 'max' => 3], FieldType::adjustCounter(['value' => 1, 'max' => 3], -5));
        $this->assertSame(FieldType::Counter, FieldType::fromLabel('Compteur'));

        // Un champ de la zone MJ n'est jamais modifiable par le joueur.
        Livewire::actingAs($this->gm)->test(Manage::class, ['campaign' => $this->campaign])
            ->set('name', 'Folie')->set('type', 'counter')->set('zone', 'gm')->set('playerEditable', true)
            ->call('save')
            ->set('name', 'PM')->set('type', 'counter')->set('zone', 'public')->set('playerEditable', true)
            ->call('save');
        $this->assertFalse(FieldDefinition::where('name', 'Folie')->sole()->player_editable);
        $this->assertTrue(FieldDefinition::where('name', 'PM')->sole()->player_editable);

        $export = $this->actingAs($this->gm)->get(route('exports.download', [$this->campaign, 'champs']))->streamedContent();
        $this->assertStringContainsString('Modifiable par le joueur', $export);
        $this->assertStringContainsString('PM;;compteur;publique;;;oui', $export);
        $this->assertStringContainsString('Folie;;compteur;MJ;;;non', $export);

        // Le réglage revient à l'import ; sans la colonne, un champ existant le garde.
        $import = fn (array $headers, array $cells) => (new ImportFieldDefinitions($this->campaign->gameSystem, $this->gm, new TabularFile($headers, [['line' => 2, 'cells' => $cells]])))->run();
        $import(['Nom', 'Type', 'Zone', 'Modifiable par le joueur'], ['Munitions', 'compteur', 'publique', 'oui']);
        $import(['Nom', 'Type', 'Zone'], ['PM', 'compteur', 'publique']);
        $this->assertTrue(FieldDefinition::where('name', 'Munitions')->sole()->player_editable);
        $this->assertTrue(FieldDefinition::where('name', 'PM')->sole()->player_editable);
    }
}
