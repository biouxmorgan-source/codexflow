<?php

namespace Tests\Feature\Duplication;

use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Campaigns\Index;
use App\Livewire\Campaigns\Show;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class CampaignDuplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private World $world;

    private Campaign $campaign;

    private Entity $worldNpc;

    private Entity $localNpc;

    private Entity $hideout;

    private PlayerCharacter $character;

    private Rule $rule;

    private Rule $sharedRule;

    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres', 'world_id' => $this->world->id]);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);

        $this->worldNpc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Le roi']);
        $this->localNpc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Aldric',
            'image_path' => UploadedFile::fake()->image('aldric.png')->store('entities', 'local'),
        ]);
        $this->hideout = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'La planque', 'entity_type_id' => EntityType::standard('place')->id]);

        // Personnage joueur, avec une fiche révélée.
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey']);
        $this->character = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->player->id]);
        CharacterGrant::create(['player_character_id' => $this->character->id, 'kind' => 'entity', 'entity_id' => $this->localNpc->id, 'granted_by' => $this->gm->id]);

        $this->relate($this->localNpc, $this->hideout, 'habite à');
        $this->relate($this->localNpc, $this->worldNpc, 'sert');
        $this->relate($sheet, $this->localNpc, 'connaît');

        $this->worldNpc->stateIn($this->campaign)->fill(['status' => 'Prisonnier', 'gm_notes' => 'Dans la tour'])->save();

        $this->rule = new Rule(['title' => 'Filature', 'gm_notes' => 'Jet difficile']);
        $this->rule->owner()->associate($this->gm);
        $this->rule->campaign()->associate($this->campaign);
        $this->rule->save();
        $this->sharedRule = new Rule(['title' => 'Combat']);
        $this->sharedRule->owner()->associate($this->gm);
        $this->sharedRule->gameSystem()->associate($this->campaign->game_system_id);
        $this->sharedRule->save();

        $this->document = new Document([
            'title' => 'Carte de la ville', 'zone' => Zone::GameMaster, 'disk' => 'local',
            'path' => UploadedFile::fake()->create('carte.pdf', 20, 'application/pdf')->store('documents', 'local'),
            'original_name' => 'carte.pdf', 'mime_type' => 'application/pdf', 'size' => 20480,
        ]);
        $this->document->owner()->associate($this->gm);
        $this->document->campaign()->associate($this->campaign);
        $this->document->save();
        $this->hideout->documents()->attach($this->document);
        $this->rule->documents()->attach($this->document);

        $scenario = $this->campaign->scenarios()->create(['name' => 'Acte I', 'position' => 0]);
        $scene = $scenario->scenes()->create(['name' => 'Le marché', 'status' => SceneStatus::Played, 'position' => 0]);
        $scene->entities()->attach([$this->localNpc->id => ['note' => 'au stand', 'position' => 0], $this->worldNpc->id => ['note' => null, 'position' => 1], $sheet->id => ['note' => null, 'position' => 2]]);
        $scene->rules()->attach([$this->rule->id => ['position' => 0], $this->sharedRule->id => ['position' => 1]]);
        $scene->documents()->attach($this->document, ['position' => 0]);

        $this->campaign->pins()->attach([$this->localNpc->id => ['position' => 0], $this->worldNpc->id => ['position' => 1], $sheet->id => ['position' => 2]]);

        foreach (['Retour du roi' => null, 'Déjà placé' => now()] as $body => $doneAt) {
            $item = new ToPlayItem(['body' => $body, 'position' => 0, 'done_at' => $doneAt]);
            $item->campaign()->associate($this->campaign);
            $item->rule()->associate($this->rule);
            $item->save();
        }
        $intention = new ToPlayItem(['body' => 'Voler la clé', 'position' => 1]);
        $intention->campaign()->associate($this->campaign);
        $intention->character()->associate($this->character);
        $intention->save();

        // Historique de la table d'origine.
        $sessionId = DB::table('play_sessions')->insertGetId(['campaign_id' => $this->campaign->id, 'number' => 1, 'current_scene_id' => $scene->id, 'started_at' => now(), 'ended_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('session_notes')->insert(['play_session_id' => $sessionId, 'user_id' => $this->gm->id, 'scene_id' => $scene->id, 'body' => 'Ils ont fui', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('messages')->insert(['campaign_id' => $this->campaign->id, 'sender_id' => $this->gm->id, 'body' => 'Bienvenue', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('campaign_invitations')->insert(['campaign_id' => $this->campaign->id, 'invited_by' => $this->gm->id, 'token' => Str::random(40), 'role' => 'player', 'expires_at' => now()->addWeek(), 'created_at' => now(), 'updated_at' => now()]);
        $this->campaign->forceFill(['table_shared' => true, 'table_display' => ['kind' => 'entity', 'id' => $this->localNpc->id]])->save();
    }

    private function relate(Entity $from, Entity $to, string $label): void
    {
        $relation = new EntityRelation(['label' => $label, 'zone' => Zone::Public]);
        $relation->owner()->associate($this->gm);
        $relation->from()->associate($from);
        $relation->to()->associate($to);
        $relation->campaign()->associate($this->campaign);
        $relation->save();
    }

    private function duplicate(): Campaign
    {
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign])
            ->assertSee(__('Dupliquer la campagne'))
            ->call('duplicate')
            ->assertRedirect(route('campaigns.show', Campaign::latest('id')->first()));

        return Campaign::where('name', 'Les Ombres (copie)')->sole();
    }

    public function test_the_prepared_content_is_copied_and_remapped(): void
    {
        $copy = $this->duplicate();

        $this->assertSame([$this->gm->id, $this->world->id, $this->campaign->game_system_id], [$copy->user_id, $copy->world_id, $copy->game_system_id]);

        // Fiches de campagne copiées (pas celle du personnage joueur), fichiers compris ; fiches du monde partagées.
        $entities = $copy->localEntities()->get()->keyBy('name');
        $this->assertEqualsCanonicalizing(['Aldric', 'La planque'], $entities->keys()->all());
        $aldric = $entities['Aldric'];
        $hideout = $entities['La planque'];
        $this->assertNotSame($this->localNpc->image_path, $aldric->image_path);
        Storage::disk('local')->assertExists([$this->localNpc->image_path, $aldric->image_path]);
        $this->assertSame(3, Entity::where('world_id', null)->where('campaign_id', $this->campaign->id)->count());
        $this->assertSame(1, Entity::where('world_id', $this->world->id)->count());

        // Relations entre les copies, et vers la fiche du monde ; celle du personnage joueur est abandonnée.
        $relations = EntityRelation::where('campaign_id', $copy->id)->get();
        $this->assertCount(2, $relations);
        $this->assertTrue($relations->contains(fn ($r) => $r->from_entity_id === $aldric->id && $r->to_entity_id === $hideout->id && $r->label === 'habite à'));
        $this->assertTrue($relations->contains(fn ($r) => $r->from_entity_id === $aldric->id && $r->to_entity_id === $this->worldNpc->id));

        // État de campagne de la fiche du monde.
        $state = $this->worldNpc->campaignStates()->where('campaign_id', $copy->id)->sole();
        $this->assertSame(['Prisonnier', 'Dans la tour'], [$state->status, $state->gm_notes]);

        // Documents (nouveau fichier) et règles de campagne, avec leurs liens.
        $document = $copy->documents()->sole();
        $this->assertSame('Carte de la ville', $document->title);
        $this->assertNotSame($this->document->path, $document->path);
        Storage::disk('local')->assertExists([$this->document->path, $document->path]);
        $rule = $copy->rules()->sole();
        $this->assertSame(['Filature', 'Jet difficile'], [$rule->title, $rule->gm_notes]);
        $this->assertSame([$document->id], $hideout->documents()->pluck('documents.id')->all());
        $this->assertSame([$document->id], $rule->documents()->pluck('documents.id')->all());
        $this->assertSame([$this->document->id], $this->hideout->documents()->pluck('documents.id')->all());

        // Scénarios et scènes : liens remappés, statut remis à « Prévue ».
        $scene = $copy->scenes()->sole();
        $this->assertSame('Le marché', $scene->name);
        $this->assertSame(SceneStatus::Planned, $scene->status);
        $this->assertSame([$aldric->id, $this->worldNpc->id], $scene->entities->modelKeys());
        $this->assertSame('au stand', $scene->entities->first()->pivot->note);
        $this->assertSame([$rule->id, $this->sharedRule->id], $scene->rules->modelKeys());
        $this->assertSame([$document->id], $scene->documents->modelKeys());
        $this->assertSame(SceneStatus::Played, $this->campaign->scenes()->sole()->status);

        // Épingles et « À jouer » du MJ en attente.
        $this->assertSame([$aldric->id, $this->worldNpc->id], $copy->pins->modelKeys());
        $item = $copy->toPlayItems()->sole();
        $this->assertSame(['Retour du roi', $rule->id, null], [$item->body, $item->rule_id, $item->done_at]);
    }

    public function test_the_table_history_is_not_copied(): void
    {
        $logsBefore = ActivityLog::count();

        $copy = $this->duplicate();

        $this->assertSame([$this->gm->id], $copy->members()->pluck('users.id')->all());
        $this->assertSame(CampaignRole::GameMaster, $copy->roleOf($this->gm));
        $this->assertSame(0, $copy->invitations()->count());
        $this->assertSame(0, $copy->playerCharacters()->count());
        $this->assertSame(1, CharacterGrant::count());
        $this->assertSame(0, $copy->playSessions()->count());
        $this->assertSame(1, DB::table('session_notes')->count());
        $this->assertSame(0, $copy->messages()->count());
        $this->assertSame(0, ActivityLog::where('campaign_id', $copy->id)->count());
        $this->assertSame($logsBefore, ActivityLog::count());
        $this->assertNull($copy->table_display);
        $this->assertFalse((bool) $copy->table_shared);

        // L'original est intact.
        $this->assertSame(2, $this->campaign->members()->count());
        $this->assertSame(1, $this->campaign->playSessions()->count());
        $this->assertSame(3, $this->campaign->toPlayItems()->count());
    }

    public function test_duplicating_from_my_campaigns_shows_a_status(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Index::class)
            ->assertSee(__('Dupliquer'))
            ->call('duplicate', $this->campaign->id)
            ->assertSee('Campagne dupliquée : « Les Ombres (copie) ».', false)
            ->assertSee('Les Ombres (copie)');

        $this->assertSame(2, Campaign::count());
    }

    public function test_only_the_owner_can_duplicate_a_campaign(): void
    {
        $coGm = User::factory()->create();
        $this->campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);

        Livewire::actingAs($this->player)
            ->test(Index::class)
            ->assertDontSee(__('Dupliquer'))
            ->call('duplicate', $this->campaign->id)
            ->assertForbidden();

        Livewire::actingAs($coGm)
            ->test(Show::class, ['campaign' => $this->campaign])
            ->assertDontSee(__('Dupliquer la campagne'))
            ->call('duplicate')
            ->assertForbidden();

        $this->assertSame(1, Campaign::count());
    }

    public function test_copied_files_are_removed_when_the_duplication_fails(): void
    {
        // Fait échouer la transaction après la copie des fichiers.
        Scenario::creating(fn () => throw new RuntimeException('panne'));

        try {
            app(DuplicateCampaign::class)->handle($this->campaign, $this->gm);
            $this->fail('La duplication aurait dû échouer.');
        } catch (RuntimeException) {
        }

        $this->assertSame(1, Campaign::count());
        $this->assertEqualsCanonicalizing(
            [$this->localNpc->image_path, $this->document->path],
            array_values(array_filter([...Storage::disk('local')->allFiles()], fn ($path) => ! str_starts_with($path, 'livewire-tmp'))),
        );
    }
}
