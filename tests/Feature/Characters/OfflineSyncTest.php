<?php

namespace Tests\Feature\Characters;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Show;
use App\Models\Campaign;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

/** Mode hors ligne du joueur : ce qu'il change sans réseau rejoint la fiche sans effacer le MJ. */
class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    private PlayerCharacter $character;

    private FieldDefinition $health;

    private FieldDefinition $job;

    private FieldDefinition $mood;

    private FieldDefinition $secret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);

        $this->health = $this->field('PV', FieldType::Counter);
        $this->job = $this->field('Métier', FieldType::Text);
        $this->mood = $this->field('Humeur', FieldType::Text);
        $this->secret = $this->field('Secret', FieldType::Text, editable: false);

        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Agathe', 'entity_type_id' => EntityType::standard('character')->id]);
        $entity->setFieldValues([$this->health->id => ['value' => 12, 'max' => 12], $this->job->id => 'Archéologue', $this->mood->id => 'Calme']);
        $entity->save();
        $this->character = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $this->player->id]);
    }

    private function field(string $name, FieldType $type, bool $editable = true): FieldDefinition
    {
        return $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => $name, 'type' => $type, 'zone' => Zone::Public, 'player_editable' => $editable,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
    }

    private function sync(array $changes, ?User $user = null, ?string $id = null): TestResponse
    {
        return $this->actingAs($user ?? $this->player)
            ->postJson(route('characters.offline-sync', [$this->campaign, $this->character]), ['sync_id' => $id ?? (string) Str::uuid(), ...$changes]);
    }

    private function value(FieldDefinition $definition): mixed
    {
        return $this->character->entity->fresh()->fieldValue($definition);
    }

    public function test_offline_changes_are_merged_field_by_field_without_erasing_the_game_master(): void
    {
        // Pendant que le joueur est hors ligne, le MJ soigne le personnage et change son humeur.
        $entity = $this->character->entity;
        $entity->setFieldValues([$this->health->id => ['value' => 12, 'max' => 14], $this->mood->id => 'Inquiète']);
        $entity->save();

        $this->sync([
            'counters' => [$this->health->id => -5],
            'fields' => [
                $this->job->id => ['base' => 'Archéologue', 'value' => 'Antiquaire'],
                $this->mood->id => ['base' => 'Calme', 'value' => 'Furieuse'],
            ],
            'notes' => [['body' => 'Le guide ment.', 'visibility' => 'gm', 'written_at' => now()->subHour()->toIso8601String()]],
        ])->assertOk()->assertExactJson([
            'counters' => ['PV'],
            'fields' => ['Métier'],
            'conflicts' => ['Humeur'],
            'rejected' => [],
            'notes' => 1,
        ]);

        $this->assertEquals(['value' => 7, 'max' => 14], $this->value($this->health), 'Les −5 du joueur s’appliquent à la valeur du MJ.');
        $this->assertSame('Antiquaire', $this->value($this->job), 'Un champ que le MJ n’a pas touché prend la valeur du joueur.');
        $this->assertSame('Inquiète', $this->value($this->mood), 'Le MJ a changé le même champ : sa version est gardée.');

        $note = CharacterNote::sole();
        $this->assertSame('Le guide ment.', $note->body);
        $this->assertSame($this->player->id, $note->user_id);
        $this->assertTrue($note->created_at->lessThan(now()->subMinutes(50)), 'La note garde la date où elle a été écrite.');
    }

    public function test_the_same_sync_sent_twice_is_applied_once(): void
    {
        $id = (string) Str::uuid();

        $first = $this->sync(['counters' => [$this->health->id => -2]], id: $id)->assertOk()->json();
        $this->sync(['counters' => [$this->health->id => -2]], id: $id)->assertOk()->assertExactJson($first);

        $this->assertSame(10, $this->value($this->health)['value']);
    }

    public function test_only_what_the_player_may_still_edit_is_applied(): void
    {
        $this->sync([
            'counters' => [$this->secret->id => 3],
            'fields' => [$this->secret->id => ['base' => '', 'value' => 'Je sais tout']],
        ])->assertOk()->assertJson(['rejected' => ['Secret', 'Secret'], 'fields' => []]);
        $this->assertNull($this->value($this->secret));

        // Fiche verrouillée entre-temps : compteurs et champs restent tels quels, les notes passent.
        $this->character->update(['locked' => true]);
        $this->sync([
            'counters' => [$this->health->id => -4],
            'notes' => [['body' => 'Noté quand même', 'visibility' => 'private']],
        ])->assertOk()->assertJson(['counters' => [], 'rejected' => ['PV'], 'notes' => 1]);
        $this->assertSame(12, $this->value($this->health)['value']);
        $this->assertSame('private', CharacterNote::sole()->visibility);
    }

    public function test_nobody_else_syncs_the_sheet(): void
    {
        $other = User::factory()->create();
        $this->campaign->members()->attach($other, ['role' => CampaignRole::Player->value]);

        $this->sync(['counters' => [$this->health->id => -12]], $other)->assertForbidden();
        $this->sync(['counters' => [$this->health->id => -12]], $this->gm)->assertForbidden();
        $this->sync(['counters' => [$this->health->id => -12]], User::factory()->create())->assertNotFound();

        $this->assertSame(12, $this->value($this->health)['value']);
    }

    public function test_note_sharing_is_limited_to_the_companions(): void
    {
        $this->sync(['notes' => [['body' => 'Pour personne', 'visibility' => 'players', 'shares' => [999]]]])->assertOk();

        $this->assertSame('gm', CharacterNote::sole()->visibility, 'Sans compagnon valable, la note reste entre le joueur et le MJ.');
        $this->assertCount(0, CharacterNote::sole()->sharedWith);
    }

    public function test_the_player_page_carries_what_the_offline_mode_needs_and_the_game_master_page_does_not(): void
    {
        $data = Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->character])
            ->assertSee('offline-sheet-data', false)
            ->instance()->offlineData;

        $this->assertSame([$this->health->id => ['name' => 'PV', 'value' => 12, 'max' => 12]], $data['counters']);
        $this->assertSame(['Métier', 'Humeur'], array_column($data['fields'], 'name'), 'Seuls les champs modifiables par le joueur.');
        $this->assertTrue($data['canNote']);

        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->character])
            ->assertDontSee('offline-sheet-data', false);
    }
}
