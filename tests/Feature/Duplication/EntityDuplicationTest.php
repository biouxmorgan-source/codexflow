<?php

namespace Tests\Feature\Duplication;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Entities\Show;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EntityDuplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private World $world;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entity::FILES_DISK);

        $this->gm = User::factory()->create();
        $this->world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->world->id]);
    }

    private function relate(Entity $from, Entity $to, string $label, ?Campaign $campaign = null): EntityRelation
    {
        $relation = new EntityRelation(['label' => $label, 'zone' => Zone::Public]);
        $relation->owner()->associate($this->gm);
        $relation->from()->associate($from);
        $relation->to()->associate($to);
        $relation->campaign()->associate($campaign);
        $relation->save();

        return $relation;
    }

    public function test_gm_duplicates_a_campaign_sheet_with_its_content_files_tags_and_relations(): void
    {
        $disk = Storage::disk(Entity::FILES_DISK);
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Aldric',
            'entity_type_id' => EntityType::standard('character')->id,
            'summary' => 'Forgeron bourru',
            'description' => 'Grand, barbu.',
            'gm_notes' => 'Espion de la Guilde',
            'image_path' => UploadedFile::fake()->image('aldric.png')->store('entities', Entity::FILES_DISK),
        ]);
        $npc->setFieldValues([12 => 'Fort']);
        $npc->save();

        $tag = Tag::forceCreate(['name' => 'Village', 'user_id' => $this->gm->id]);
        $npc->tags()->attach($tag);

        $attachment = new Attachment([
            'zone' => Zone::GameMaster, 'disk' => Entity::FILES_DISK,
            'path' => UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf')->store('attachments/'.$npc->id, Entity::FILES_DISK),
            'original_name' => 'plan.pdf', 'mime_type' => 'application/pdf', 'size' => 10240,
        ]);
        $attachment->owner()->associate($this->gm);
        $npc->attachments()->save($attachment);

        $guild = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'La Guilde']);
        $wife = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Mira']);
        $this->relate($npc, $guild, 'travaille pour', $this->campaign);
        $this->relate($wife, $npc, 'amoureux de', $this->campaign);

        $component = Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $npc])
            ->assertSee(__('Dupliquer'))
            ->call('duplicate');

        $copy = Entity::where('name', 'Aldric (copie)')->sole();
        $component->assertRedirect(route('entities.show', [$this->campaign, $copy]));

        $this->assertSame($this->campaign->id, $copy->campaign_id);
        $this->assertNull($copy->world_id);
        $this->assertSame(['Forgeron bourru', 'Grand, barbu.', 'Espion de la Guilde'], [$copy->summary, $copy->description, $copy->gm_notes]);
        $this->assertSame(['12' => 'Fort'], $copy->field_values);
        $this->assertSame([$tag->id], $copy->tags()->pluck('tags.id')->all());

        // Image et pièce jointe : nouveaux fichiers, l'original reste en place.
        $this->assertNotNull($copy->image_path);
        $this->assertNotSame($npc->image_path, $copy->image_path);
        $disk->assertExists([$npc->image_path, $copy->image_path]);
        $copiedAttachment = $copy->attachments()->sole();
        $this->assertNotSame($attachment->path, $copiedAttachment->path);
        $this->assertStringStartsWith('attachments/'.$copy->id.'/', $copiedAttachment->path);
        $disk->assertExists([$attachment->path, $copiedAttachment->path]);
        $this->assertSame(['plan.pdf', 'gm'], [$copiedAttachment->original_name, $copiedAttachment->zone->value]);

        // Relations dans les deux sens, vers les mêmes fiches.
        $this->assertTrue(EntityRelation::where(['from_entity_id' => $copy->id, 'to_entity_id' => $guild->id, 'label' => 'travaille pour', 'campaign_id' => $this->campaign->id])->exists());
        $this->assertTrue(EntityRelation::where(['from_entity_id' => $wife->id, 'to_entity_id' => $copy->id, 'label' => 'amoureux de'])->exists());
        $this->assertSame(4, EntityRelation::count());

        // Supprimer la copie ne touche pas aux fichiers de l'original.
        $copy->delete();
        $disk->assertExists([$npc->image_path, $attachment->path]);
    }

    public function test_a_world_sheet_is_copied_in_the_world_without_its_campaign_states(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Shen Chu']);
        $npc->stateIn($this->campaign)->fill(['status' => 'Mort', 'gm_notes' => 'Tué par les joueurs'])->save();

        $document = new Document(['title' => 'Portrait', 'zone' => Zone::Public, 'disk' => 'local', 'path' => 'documents/x.pdf', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 1]);
        $document->owner()->associate($this->gm);
        $document->world()->associate($this->world);
        $document->save();
        $npc->documents()->attach($document);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $npc])
            ->call('duplicate');

        $copy = Entity::where('name', 'Shen Chu (copie)')->sole();
        $this->assertSame($this->world->id, $copy->world_id);
        $this->assertNull($copy->campaign_id);
        $this->assertSame(0, $copy->campaignStates()->count());
        $this->assertSame(1, $npc->campaignStates()->count());
        $this->assertSame([$document->id], $copy->documents()->pluck('documents.id')->all());

        // Une seule entrée au journal : la création de la copie.
        $this->assertSame(1, ActivityLog::where('subject_type', 'entity')->where('subject_id', $copy->id)->count());
    }

    public function test_a_player_cannot_duplicate_a_sheet(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create();

        Livewire::actingAs($player)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $npc])
            ->assertForbidden();

        $this->assertSame(1, Entity::count());
    }
}
