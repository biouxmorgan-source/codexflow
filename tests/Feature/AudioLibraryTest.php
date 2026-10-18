<?php

namespace Tests\Feature;

use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Livewire\Audio\Index as AudioIndex;
use App\Livewire\Library\Sounds as LibrarySounds;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Sessions\Live;
use App\Livewire\Table\Screen;
use App\Livewire\Tags\Manage as TagsManage;
use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use App\Support\Plans\StorageUsage;
use App\Support\TableAudio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bibliothèque sonore : préparer ses musiques, les lier aux scènes, les lancer en session
 * sur l'appareil du MJ ou sur l'écran de table.
 */
class AudioLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private User $spectator;

    private Campaign $campaign;

    private Scene $scene;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->spectator = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
        $this->campaign->members()->attach($this->spectator, ['role' => CampaignRole::Spectator->value]);
        $this->scene = $this->campaign->scenarios()->create(['name' => 'Acte I', 'position' => 1])->scenes()->create(['name' => 'La taverne', 'position' => 1]);
    }

    private static function mp3(string $name = 'Taverne.mp3'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "ID3\x03\x00\x00\x00\x00\x00\x00\xff\xfb\x90\x64".str_repeat("\x00", 400));
    }

    private function track(string $title = 'Taverne', bool $loop = true): AudioTrack
    {
        $track = new AudioTrack([
            'title' => $title, 'loop' => $loop, 'disk' => AudioTrack::DISK, 'original_name' => $title.'.mp3', 'mime_type' => 'audio/mpeg', 'size' => 414,
            'path' => self::mp3()->storeAs('audio', $title.'.mp3', AudioTrack::DISK),
        ]);
        $track->owner()->associate($this->gm);
        $track->campaign()->associate($this->campaign);
        $track->save();

        return $track;
    }

    public function test_gm_uploads_tracks_with_tags_and_they_count_in_storage(): void
    {
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [self::mp3('Taverne du Pendu.mp3')])
            ->set('tags', 'taverne, calme')
            ->set('loop', false)
            ->call('saveUploads')
            ->assertHasNoErrors()
            ->assertSee('Taverne du Pendu')
            ->assertSee('Une fois');

        $track = AudioTrack::sole();
        $this->assertSame('audio/mpeg', $track->mime_type);
        $this->assertFalse($track->loop);
        $this->assertStringEndsWith('.mp3', $track->path);
        Storage::disk('local')->assertExists($track->path);
        $this->assertSame(['calme', 'taverne'], $track->tags->pluck('name')->all());
        $this->assertSame($track->size, StorageUsage::bytes($this->gm->fresh()));
    }

    public function test_only_sound_files_are_accepted(): void
    {
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [UploadedFile::fake()->createWithContent('faux.mp3', '<html><script>alert(1)</script></html>')])
            ->call('saveUploads')
            ->assertHasErrors('uploads.0');

        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [UploadedFile::fake()->createWithContent('vrai-son.txt', "ID3\x03\x00\x00\x00\x00\x00\x00\xff\xfb\x90\x64".str_repeat("\x00", 400))])
            ->call('saveUploads')
            ->assertHasErrors('uploads.0');

        $this->assertSame(0, AudioTrack::count());
    }

    public function test_library_is_for_game_masters_only(): void
    {
        $track = $this->track();

        Livewire::actingAs($this->player)->test(AudioIndex::class, ['campaign' => $this->campaign])->assertForbidden();
        $this->actingAs($this->player)->get(route('audio.file', $track))->assertNotFound();
        $this->actingAs($this->spectator)->get(route('audio.file', $track))->assertNotFound();
        $this->actingAs($this->gm)->get(route('audio.file', $track))->assertOk()->assertHeader('Content-Type', 'audio/mpeg');
    }

    public function test_the_file_is_served_by_ranges_to_allow_seeking(): void
    {
        $track = $this->track();

        $this->actingAs($this->gm)->get(route('audio.file', $track), ['Range' => 'bytes=0-9'])
            ->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 0-9/414');
    }

    public function test_tracks_are_linked_to_a_scene_and_come_first_in_session(): void
    {
        $other = $this->track('Abysses');
        $tavern = $this->track('Taverne');

        Livewire::actingAs($this->gm)->test(SceneForm::class, ['campaign' => $this->campaign, 'scene' => $this->scene])
            ->set('pickedTrackId', $tavern->id)
            ->call('addTrack')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([$tavern->id], $this->scene->audioTracks()->pluck('audio_tracks.id')->all());

        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
        $session->currentScene()->associate($this->scene)->save();

        $component = Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign]);
        $this->assertSame([$tavern->id, $other->id], $component->instance()->tracks->pluck('id')->all());
        $component->assertSee(['Musique', 'Taverne', 'Abysses']);
    }

    public function test_gm_plays_a_track_on_the_table_screen_and_controls_it(): void
    {
        $track = $this->track('Orage', loop: false);
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('playOnTable', $track->id)
            ->assertSee('Sur la table :')
            ->call('tableVolume', 40)
            ->call('loopTableAudio')
            ->call('pauseTableAudio');

        $state = $this->campaign->fresh()->table_audio;
        $this->assertEquals(['track' => $track->id, 'loop' => true, 'volume' => 40, 'playing' => false], array_diff_key($state, ['key' => 1]));

        // Le spectateur (la télé) entend la musique ; un joueur qui suit l'écran, non.
        $this->campaign->forceFill(['table_shared' => true])->save();
        $screen = Livewire::actingAs($this->spectator)->test(Screen::class, ['campaign' => $this->campaign]);
        $this->assertSame(route('table.audio', [$this->campaign, 'v' => $state['key']]), $screen->get('audio')['url']);
        $this->actingAs($this->spectator)->get(route('table.audio', $this->campaign))->assertOk();

        $this->assertNull(Livewire::actingAs($this->player)->test(Screen::class, ['campaign' => $this->campaign])->get('audio'));
        $this->actingAs($this->player)->get(route('table.audio', $this->campaign))->assertForbidden();

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('stopTableAudio');
        $this->assertNull($this->campaign->fresh()->table_audio);
        $this->actingAs($this->spectator)->get(route('table.audio', $this->campaign))->assertNotFound();
    }

    public function test_table_music_follows_the_table_feature(): void
    {
        $track = $this->track();
        $this->campaign->forceFill(['disabled_features' => ['table']])->save();

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('playOnTable', $track->id)
            ->assertForbidden();

        $this->campaign->forceFill(['disabled_features' => [], 'table_audio' => ['track' => $track->id, 'playing' => true, 'key' => 'abc']])->save();
        $this->assertNotNull(TableAudio::current($this->campaign->fresh()));
        $this->campaign->forceFill(['disabled_features' => ['table']])->save();
        $this->assertNull(TableAudio::current($this->campaign->fresh()));
    }

    public function test_deleting_a_track_removes_its_file_and_stops_the_table(): void
    {
        $track = $this->track();
        TableAudio::play($this->campaign, $track);

        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->call('delete', $track->id);

        $this->assertModelMissing($track);
        Storage::disk('local')->assertMissing($track->path);
        $this->assertNull($this->campaign->fresh()->table_audio);

        $kept = $this->track('Forêt');
        $this->campaign->delete();
        Storage::disk('local')->assertMissing($kept->path);
    }

    public function test_tags_count_and_merge_tracks(): void
    {
        $track = $this->track();
        $combat = Tag::idsFromInput($this->gm, 'combat, bagarre');
        $track->tags()->sync($combat);

        Livewire::actingAs($this->gm)->test(TagsManage::class)->assertSee('1 son');

        Tag::find($combat[1])->mergeInto(Tag::find($combat[0]));
        $this->assertSame(['combat'], $track->fresh()->tags->pluck('name')->all());
    }

    public function test_duplication_and_archive_keep_tracks_and_scene_links(): void
    {
        $track = $this->track('Taverne');
        $track->tags()->sync(Tag::idsFromInput($this->gm, 'taverne'));
        $this->scene->audioTracks()->attach($track, ['position' => 0]);

        $copy = app(DuplicateCampaign::class)->handle($this->campaign, $this->gm);
        $copied = $copy->audioTracks()->sole();
        $this->assertNotSame($track->path, $copied->path);
        Storage::disk('local')->assertExists($copied->path);
        $this->assertSame([$copied->id], $copy->scenes()->sole()->audioTracks()->pluck('audio_tracks.id')->all());

        $path = (new CampaignExport($this->campaign->fresh()))->write();
        $imported = (new CampaignImport($this->gm))->handle($path);
        $restored = $imported->audioTracks()->with('tags')->sole();

        $this->assertSame(['Taverne', true, 'audio/mpeg', ['taverne']], [$restored->title, $restored->loop, $restored->mime_type, $restored->tags->pluck('name')->all()]);
        $this->assertSame(Storage::disk('local')->get($track->path), Storage::disk('local')->get($restored->path));
        $this->assertSame([$restored->id], $imported->scenes()->sole()->audioTracks()->pluck('audio_tracks.id')->all());
    }

    private function libraryTrack(User $owner, string $title = 'Orage'): AudioTrack
    {
        $track = new AudioTrack([
            'title' => $title, 'loop' => true, 'disk' => AudioTrack::DISK, 'original_name' => $title.'.mp3', 'mime_type' => 'audio/mpeg', 'size' => 414,
            'path' => self::mp3()->storeAs('audio', 'lib-'.$title.'.mp3', AudioTrack::DISK),
        ]);
        $track->owner()->associate($owner);
        $track->save();

        return $track;
    }

    public function test_gm_keeps_sounds_in_a_personal_library_and_adds_them_to_campaigns_without_copy(): void
    {
        $second = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Le Puits']);

        Livewire::actingAs($this->gm)->test(LibrarySounds::class)
            ->set('uploads', [self::mp3('Orage lointain.mp3')])
            ->set('tags', 'météo')
            ->call('saveUploads')
            ->assertHasNoErrors()
            ->assertSee('Orage lointain')
            ->assertSee('aucune campagne');

        $track = AudioTrack::sole();
        $this->assertNull($track->campaign_id);
        $this->actingAs($this->gm)->get(route('library.sounds'))->assertOk()->assertSee('Orage lointain');
        $this->actingAs($this->player)->get(route('library.sounds'))->assertOk()->assertDontSee('Orage lointain');
        $this->assertSame(['météo'], $track->tags->pluck('name')->all());

        $library = Livewire::actingAs($this->gm)->test(LibrarySounds::class);
        foreach ([$this->campaign, $second] as $campaign) {
            $library->set("addTo.{$track->id}", (string) $campaign->id)->call('addToCampaign', $track->id);
        }

        // Un seul fichier, compté une fois, utilisable dans les deux campagnes.
        $this->assertSame(1, AudioTrack::count());
        $this->assertSame($track->size, StorageUsage::bytes($this->gm->fresh()));
        $this->assertSame([$track->id], $this->campaign->availableTracks()->pluck('id')->all());
        $this->assertSame([$track->id], $second->availableTracks()->pluck('id')->all());

        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $second])
            ->assertSee('Orage lointain')
            ->assertSee('Ma bibliothèque');

        // Lié à une scène, joué sur la table, servi aux MJ de la campagne seulement.
        Livewire::actingAs($this->gm)->test(SceneForm::class, ['campaign' => $this->campaign, 'scene' => $this->scene])
            ->set('pickedTrackId', $track->id)->call('addTrack')->call('save')->assertHasNoErrors();
        $this->assertSame([$track->id], $this->scene->audioTracks()->pluck('audio_tracks.id')->all());
        TableAudio::play($this->campaign, $track);
        $this->assertSame($track->id, TableAudio::current($this->campaign->fresh())['track']->id);
        $this->actingAs($this->gm)->get(route('audio.file', $track))->assertOk();
        $this->actingAs($this->player)->get(route('audio.file', $track))->assertNotFound();

        // Retirer d'une campagne garde le fichier et arrête la table qui le jouait.
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->call('removeFromCampaign', $track->id);
        $this->assertModelExists($track);
        $this->assertSame([], $this->campaign->availableTracks()->pluck('id')->all());
        $this->assertSame([], $this->scene->audioTracks()->pluck('audio_tracks.id')->all());
        $this->assertNull($this->campaign->fresh()->table_audio);

        // Supprimer de la bibliothèque l'enlève partout, avec son fichier.
        Livewire::actingAs($this->gm)->test(LibrarySounds::class)->call('delete', $track->id);
        $this->assertModelMissing($track);
        Storage::disk('local')->assertMissing($track->path);
        $this->assertSame([], $second->availableTracks()->pluck('id')->all());
    }

    public function test_campaign_upload_can_go_to_the_library(): void
    {
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [self::mp3('Forêt.mp3')])
            ->set('toLibrary', true)
            ->call('saveUploads')
            ->assertHasNoErrors();

        $track = AudioTrack::sole();
        $this->assertTrue($track->inLibrary());
        $this->assertSame([$track->id], $this->campaign->availableTracks()->pluck('id')->all());
    }

    public function test_moving_a_campaign_sound_to_the_library_merges_identical_copies(): void
    {
        $second = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Le Puits']);
        $first = $this->track('Taverne');
        $this->scene->audioTracks()->attach($first, ['position' => 0]);

        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->call('moveToLibrary', $first->id)
            ->assertSee('Ma bibliothèque');
        $this->assertNull($first->fresh()->campaign_id);
        $this->assertSame([$first->id], $this->campaign->availableTracks()->pluck('id')->all());

        // La même musique téléversée dans une autre campagne : la copie disparaît, l'original la remplace.
        $copy = new AudioTrack(['title' => 'Taverne (copie)', 'loop' => true, 'disk' => AudioTrack::DISK, 'original_name' => 'Taverne.mp3', 'mime_type' => 'audio/mpeg', 'size' => 414,
            'path' => self::mp3()->storeAs('audio', 'copie.mp3', AudioTrack::DISK)]);
        $copy->owner()->associate($this->gm);
        $copy->campaign()->associate($second);
        $copy->save();
        $otherScene = $second->scenarios()->create(['name' => 'Acte I', 'position' => 1])->scenes()->create(['name' => 'Le puits', 'position' => 1]);
        $otherScene->audioTracks()->attach($copy, ['position' => 0]);
        $this->assertSame(828, StorageUsage::bytes($this->gm->fresh()));

        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $second])->call('moveToLibrary', $copy->id);

        $this->assertModelMissing($copy);
        Storage::disk('local')->assertMissing($copy->path);
        $this->assertSame([$first->id], $second->availableTracks()->pluck('id')->all());
        $this->assertSame([$first->id], $otherScene->audioTracks()->pluck('audio_tracks.id')->all());
        $this->assertSame(414, StorageUsage::bytes($this->gm->fresh(), fresh: true));
    }

    public function test_library_sounds_stay_private_to_their_owner(): void
    {
        $coGm = User::factory()->create();
        $this->campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);
        $mine = $this->libraryTrack($coGm, 'Cloches');
        $stranger = User::factory()->create();
        $foreign = $this->libraryTrack($stranger, 'Secret');

        // Le co-MJ ajoute son son : le MJ l'écoute et le lance, mais ne peut ni le modifier ni le supprimer.
        Livewire::actingAs($coGm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->set('libraryPicks', [$mine->id, $foreign->id])
            ->call('addFromLibrary');
        $this->assertSame([$mine->id], $this->campaign->availableTracks()->pluck('id')->all());

        $this->actingAs($this->gm)->get(route('audio.file', $mine))->assertOk();
        $this->actingAs($this->gm)->get(route('audio.file', $foreign))->assertNotFound();
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Cloches')
            ->call('edit', $mine->id)->assertForbidden();
        Livewire::actingAs($this->gm)->test(AudioIndex::class, ['campaign' => $this->campaign])
            ->call('delete', $mine->id)->assertNotFound();
        Livewire::actingAs($this->gm)->test(LibrarySounds::class)->call('delete', $mine->id)->assertNotFound();
        $this->assertModelExists($mine);

        // Le co-MJ quitte la campagne : son son n'y est plus.
        $this->campaign->members()->detach($coGm);
        $this->assertSame([], $this->campaign->availableTracks()->pluck('id')->all());
        $this->actingAs($this->gm)->get(route('audio.file', $mine))->assertNotFound();
    }

    public function test_duplication_links_own_library_sounds_and_export_includes_them(): void
    {
        $track = $this->libraryTrack($this->gm, 'Orage');
        $track->linkedCampaigns()->attach($this->campaign);
        $this->scene->audioTracks()->attach($track, ['position' => 0]);

        $copy = app(DuplicateCampaign::class)->handle($this->campaign, $this->gm);
        $this->assertSame(1, AudioTrack::count());
        $this->assertSame([$track->id], $copy->availableTracks()->pluck('id')->all());
        $this->assertSame([$track->id], $copy->scenes()->sole()->audioTracks()->pluck('audio_tracks.id')->all());

        $path = (new CampaignExport($this->campaign->fresh()))->write();
        $imported = (new CampaignImport($this->gm))->handle($path);
        $restored = $imported->audioTracks()->sole();
        $this->assertSame('Orage', $restored->title);
        $this->assertSame([$restored->id], $imported->scenes()->sole()->audioTracks()->pluck('audio_tracks.id')->all());
    }
}
