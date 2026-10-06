<?php

namespace Tests\Feature\Entities;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Entities\Form;
use App\Livewire\Entities\Show;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EntityFilesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $npc;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entity::FILES_DISK);

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->npc = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Shen Chu']);
    }

    public function test_gm_sets_and_replaces_the_main_image(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->set('image', UploadedFile::fake()->image('portrait.png', 300, 300))
            ->call('save')
            ->assertHasNoErrors();

        $first = $this->npc->fresh()->image_path;
        Storage::disk(Entity::FILES_DISK)->assertExists($first);

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->npc->fresh()])
            ->set('image', UploadedFile::fake()->image('nouveau.jpg'))
            ->call('save');

        Storage::disk(Entity::FILES_DISK)->assertMissing($first);
        Storage::disk(Entity::FILES_DISK)->assertExists($this->npc->fresh()->image_path);

        $this->actingAs($this->gm)->get(route('entities.image', $this->npc))->assertOk();
    }

    public function test_main_image_can_be_removed(): void
    {
        $this->npc->update(['image_path' => UploadedFile::fake()->image('a.png')->store('entities', Entity::FILES_DISK)]);
        $path = $this->npc->image_path;

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->set('removeImage', true)
            ->call('save');

        $this->assertNull($this->npc->fresh()->image_path);
        Storage::disk(Entity::FILES_DISK)->assertMissing($path);
    }

    public function test_non_images_are_refused_as_main_image(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->set('image', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('image');
    }

    public function test_gm_attaches_files_to_each_zone_and_downloads_them(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->set('uploadZone', 'public')
            ->set('uploads', [UploadedFile::fake()->image('carte.png')])
            ->call('saveUploads')
            ->assertHasNoErrors()
            ->set('uploadZone', 'gm')
            ->set('uploads', [UploadedFile::fake()->create('secret.pdf', 50, 'application/pdf')])
            ->call('saveUploads')
            ->assertHasNoErrors()
            ->assertSee(['carte.png', 'secret.pdf']);

        $public = Attachment::where('original_name', 'carte.png')->sole();
        $secret = Attachment::where('original_name', 'secret.pdf')->sole();

        $this->assertSame(Zone::Public, $public->zone);
        $this->assertSame(Zone::GameMaster, $secret->zone);
        Storage::disk(Entity::FILES_DISK)->assertExists($secret->path);

        $this->actingAs($this->gm)
            ->get(route('attachments.show', $secret))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_dangerous_file_types_are_refused(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->set('uploads', [UploadedFile::fake()->create('piege.html', 1, 'text/html')])
            ->call('saveUploads')
            ->assertHasErrors('uploads.0');

        $this->assertSame(0, Attachment::count());
    }

    public function test_files_are_not_served_to_players_or_strangers(): void
    {
        $attachment = $this->attach('secret.pdf');
        $this->npc->update(['image_path' => UploadedFile::fake()->image('a.png')->store('entities', Entity::FILES_DISK)]);

        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        foreach ([$player, User::factory()->create()] as $user) {
            $this->actingAs($user)->get(route('attachments.show', $attachment))->assertForbidden();
            $this->actingAs($user)->get(route('entities.image', $this->npc))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('attachments.show', $attachment))->assertRedirect(route('login'));
    }

    public function test_deleting_an_attachment_or_the_entity_removes_the_files(): void
    {
        $kept = $this->attach('a.pdf');
        $removed = $this->attach('b.pdf');

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->call('deleteAttachment', $removed->id);

        $this->assertModelMissing($removed);
        Storage::disk(Entity::FILES_DISK)->assertMissing($removed->path);

        $this->npc->delete();

        $this->assertModelMissing($kept);
        Storage::disk(Entity::FILES_DISK)->assertMissing($kept->path);
    }

    private function attach(string $name): Attachment
    {
        $attachment = new Attachment([
            'zone' => Zone::GameMaster,
            'disk' => Entity::FILES_DISK,
            'path' => UploadedFile::fake()->create($name, 10, 'application/pdf')->store('attachments', Entity::FILES_DISK),
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size' => 10240,
        ]);
        $attachment->owner()->associate($this->gm);
        $this->npc->attachments()->save($attachment);

        return $attachment;
    }
}
