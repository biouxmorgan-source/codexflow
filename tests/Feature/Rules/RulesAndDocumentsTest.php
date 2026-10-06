<?php

namespace Tests\Feature\Rules;

use App\Enums\CampaignRole;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Livewire\Documents\Show as DocumentShow;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Rules\Form as RuleForm;
use App\Livewire\Rules\Index as RuleIndex;
use App\Livewire\Rules\Show as RuleShow;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Sessions\Live;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RulesAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private World $world;

    private Campaign $campaign;

    /** Même jeu et même monde que $campaign. */
    private Campaign $sibling;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Document::DISK);

        $this->gm = User::factory()->create();
        $this->world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->world->id]);
        $this->sibling = Campaign::factory()->for($this->gm, 'owner')->create([
            'world_id' => $this->world->id,
            'game_system_id' => $this->campaign->game_system_id,
        ]);
    }

    public function test_a_game_rule_is_shared_by_the_game_campaigns_and_a_campaign_rule_is_not(): void
    {
        Livewire::actingAs($this->gm)
            ->test(RuleForm::class, ['campaign' => $this->campaign])
            ->set('title', 'Jet de condition')
            ->set('category', 'Voyage')
            ->set('procedure', 'Force + Esprit chaque matin.')
            ->set('gmNotes', 'Penser au bonus de feu de camp')
            ->set('tags', 'voyage, matin')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($this->gm)
            ->test(RuleForm::class, ['campaign' => $this->campaign])
            ->set('title', 'Bagarre de taverne')
            ->set('scope', 'campaign')
            ->set('origin', 'house')
            ->set('status', 'to_test')
            ->call('save')
            ->assertHasNoErrors();

        $shared = Rule::where('title', 'Jet de condition')->sole();
        $this->assertSame($this->campaign->game_system_id, $shared->game_system_id);
        $this->assertNull($shared->campaign_id);
        $this->assertSame(['matin', 'voyage'], $shared->tags->pluck('name')->all());
        $this->assertArrayNotHasKey('gm_notes', $shared->toArray());

        Livewire::actingAs($this->gm)->test(RuleIndex::class, ['campaign' => $this->campaign])
            ->assertSee(['Jet de condition', 'Bagarre de taverne']);

        Livewire::actingAs($this->gm)->test(RuleIndex::class, ['campaign' => $this->sibling])
            ->assertSee('Jet de condition')
            ->assertDontSee('Bagarre de taverne');

        $local = Rule::where('title', 'Bagarre de taverne')->sole();
        $this->actingAs($this->gm)->get(route('rules.show', [$this->sibling, $local]))->assertNotFound();
    }

    public function test_rules_filter_by_status_and_tag(): void
    {
        $this->rule('Voyage', ['status' => RuleStatus::Adopted], 'voyage');
        $this->rule('Bagarre', ['status' => RuleStatus::ToTest], 'combat');

        Livewire::actingAs($this->gm)->test(RuleIndex::class, ['campaign' => $this->campaign])
            ->set('status', 'to_test')->assertSee('Bagarre')->assertDontSee('Voyage')
            ->set('status', '')->set('tag', 'voyage')->assertSee('Voyage')->assertDontSee('Bagarre');
    }

    public function test_a_rule_can_only_belong_to_one_place(): void
    {
        $this->expectException(QueryException::class);

        $rule = new Rule(['title' => 'Partout']);
        $rule->owner()->associate($this->gm);
        $rule->game_system_id = $this->campaign->game_system_id;
        $rule->campaign_id = $this->campaign->id;
        $rule->save();
    }

    /** Parcours de recette 6 (partie MJ) : une règle passe dans « À jouer » et peut être marquée jouée. */
    public function test_a_rule_goes_to_play_once_and_can_be_marked_played(): void
    {
        $rule = $this->rule('Bagarre', ['status' => RuleStatus::ToTest]);

        Livewire::actingAs($this->gm)->test(RuleShow::class, ['campaign' => $this->campaign, 'rule' => $rule])
            ->call('addToPlay')
            ->call('addToPlay')
            ->assertSee('Dans « À jouer »');

        $item = $this->campaign->toPlayItems()->sole();
        $this->assertTrue($item->rule->is($rule));

        $live = Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('start');
        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->assertSeeHtml('href="'.route('rules.show', [$this->campaign, $rule]).'"');

        $live->call('markPlayed', $item->id);
        $this->assertNotNull($item->fresh()->done_at);

        Livewire::actingAs($this->gm)->test(RuleShow::class, ['campaign' => $this->campaign, 'rule' => $rule])
            ->assertSee('Ajouter à');
    }

    public function test_gm_uploads_documents_into_the_world_and_finds_them_in_every_campaign_of_that_world(): void
    {
        Livewire::actingAs($this->gm)
            ->test(DocumentIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [
                UploadedFile::fake()->create('carte-region.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('portrait-mira.png'),
            ])
            ->set('scope', 'world')
            ->set('tags', 'cartes')
            ->call('saveUploads')
            ->assertHasNoErrors();

        $map = Document::where('title', 'carte-region')->sole();
        $this->assertSame($this->world->id, $map->world_id);
        $this->assertTrue($map->isPdf());
        Storage::disk(Document::DISK)->assertExists($map->path);

        Livewire::actingAs($this->gm)->test(DocumentIndex::class, ['campaign' => $this->sibling])
            ->assertSee(['carte-region', 'portrait-mira'])
            ->set('kind', 'image')
            ->assertSee('portrait-mira')
            ->assertDontSee('carte-region');

        $this->actingAs($this->gm)->get(route('documents.file', $map))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_only_pdf_and_images_are_accepted(): void
    {
        Livewire::actingAs($this->gm)
            ->test(DocumentIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream')])
            ->call('saveUploads')
            ->assertHasErrors('uploads.0');

        $this->assertSame(0, Document::count());
    }

    public function test_deleting_a_document_removes_its_file_and_links(): void
    {
        $document = $this->document('Lettre');
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Mira']);
        $entity->documents()->attach($document);

        Livewire::actingAs($this->gm)->test(DocumentShow::class, ['campaign' => $this->campaign, 'document' => $document])
            ->set('title', 'Lettre de Mira')
            ->set('zone', 'public')
            ->call('save')
            ->assertHasNoErrors()
            ->call('delete');

        $this->assertSame(0, Document::count());
        $this->assertSame(0, $entity->documents()->count());
        Storage::disk(Document::DISK)->assertMissing($document->path);
    }

    public function test_entity_links_a_document_of_the_campaign(): void
    {
        $document = $this->document('Lettre');
        $foreign = $this->document('Autre', Campaign::factory()->for($this->gm, 'owner')->create());
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Mira']);

        Livewire::actingAs($this->gm)->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $entity])
            ->set('pickedDocumentId', $foreign->id)
            ->call('linkDocument')
            ->assertHasErrors('pickedDocumentId')
            ->set('pickedDocumentId', $document->id)
            ->call('linkDocument')
            ->assertSeeHtml('href="'.route('documents.show', [$this->campaign, $document]).'"');

        $this->assertSame([$document->id], $entity->documents()->pluck('documents.id')->all());

        // La fiche du monde n'affiche pas, dans une autre campagne, un document qui n'y est pas disponible.
        Livewire::actingAs($this->gm)->test(EntityShow::class, ['campaign' => $this->sibling, 'entity' => $entity])
            ->assertDontSeeHtml('href="'.route('documents.show', [$this->campaign, $document]).'"');
    }

    /** Parcours de recette 3 (partie MJ) : scène avec deux PNJ, une règle et un PDF, puis session. */
    public function test_rules_and_documents_of_the_scene_show_up_in_session(): void
    {
        $aldric = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric']);
        $mira = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Mira']);
        $rule = $this->rule('Bagarre de taverne', ['summary' => 'On perd de la condition, pas des PV', 'gm_notes' => 'Secret du MJ']);
        $foreignRule = $this->rule('Regle d\'un autre jeu', [], null, Campaign::factory()->for($this->gm, 'owner')->create());
        $map = $this->document('Plan de la cave');
        $viaRule = $this->document('Table des coups');
        $rule->documents()->attach($viaRule);
        $scenario = $this->campaign->scenarios()->create(['name' => 'Une nuit']);

        Livewire::actingAs($this->gm)
            ->test(SceneForm::class, ['campaign' => $this->campaign])
            ->set('scenarioId', (string) $scenario->id)
            ->set('name', 'Bagarre')
            ->set('status', SceneStatus::Available->value)
            ->set('linked', [
                ['id' => $aldric->id, 'name' => 'Aldric', 'type' => 'PNJ', 'note' => ''],
                ['id' => $mira->id, 'name' => 'Mira', 'type' => 'PNJ', 'note' => ''],
            ])
            ->set('pickedRuleId', $foreignRule->id)->call('addRule')
            ->set('pickedRuleId', $rule->id)->call('addRule')
            ->set('pickedDocumentId', $map->id)->call('addDocument')
            ->call('save')
            ->assertHasNoErrors();

        $scene = $scenario->scenes()->sole();
        $this->assertSame([$rule->id], $scene->rules()->pluck('rules.id')->all());
        $this->assertSame([$map->id], $scene->documents()->pluck('documents.id')->all());

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('start');

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->assertSeeInOrder(['Fiches utiles', 'Aldric', 'Mira', 'Règles', 'Bagarre de taverne', 'On perd de la condition', 'Secret du MJ', 'Documents', 'Plan de la cave', 'Table des coups'])
            ->assertSeeHtml('href="'.route('documents.file', $map).'"');
    }

    public function test_rules_and_documents_are_reserved_to_the_game_master(): void
    {
        $rule = $this->rule('Bagarre');
        $document = $this->document('Lettre');
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        foreach ([$player, User::factory()->create()] as $user) {
            $this->actingAs($user)->get(route('rules.index', $this->campaign))->assertForbidden();
            $this->actingAs($user)->get(route('rules.show', [$this->campaign, $rule]))->assertForbidden();
            $this->actingAs($user)->get(route('documents.index', $this->campaign))->assertForbidden();
            $this->actingAs($user)->get(route('documents.show', [$this->campaign, $document]))->assertForbidden();
            $this->actingAs($user)->get(route('documents.file', $document))->assertForbidden();
        }

        // Un co-MJ ne peut pas ranger un document dans le jeu d'un autre compte.
        $this->campaign->members()->updateExistingPivot($player, ['role' => CampaignRole::GameMaster->value]);
        Livewire::actingAs($player)
            ->test(DocumentIndex::class, ['campaign' => $this->campaign])
            ->set('uploads', [UploadedFile::fake()->create('carte.pdf', 10, 'application/pdf')])
            ->set('scope', 'game')
            ->call('saveUploads')
            ->assertForbidden();
        $this->assertSame(1, Document::count());
    }

    /** @param array<string, mixed> $attributes */
    private function rule(string $title, array $attributes = [], ?string $tags = null, ?Campaign $campaign = null): Rule
    {
        $rule = new Rule(['title' => $title, ...$attributes]);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($campaign ?? $this->campaign);
        $rule->save();

        if ($tags !== null) {
            $rule->tags()->sync(Tag::idsFromInput($this->gm, $tags));
        }

        return $rule;
    }

    private function document(string $title, ?Campaign $campaign = null): Document
    {
        $path = UploadedFile::fake()->create($title.'.pdf', 10, 'application/pdf')->store('documents', Document::DISK);

        $document = new Document([
            'title' => $title,
            'disk' => Document::DISK,
            'path' => $path,
            'original_name' => $title.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10240,
        ]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($campaign ?? $this->campaign);
        $document->save();

        return $document;
    }
}
