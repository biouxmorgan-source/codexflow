<?php

namespace Tests\Feature\Archive;

use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\MapToken;
use App\Models\Rule;
use App\Models\Secret;
use App\Models\TableMap;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\World;
use App\Support\Archive\ArchiveException;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class CampaignArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $other;

    private Campaign $campaign;

    private Entity $worldNpc;

    private Entity $localNpc;

    private FieldDefinition $field;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->other = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create(['name' => 'Valdaria']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres', 'world_id' => $world->id]);

        $this->field = new FieldDefinition(['name' => 'Courage', 'type' => 'number', 'zone' => Zone::Public, 'position' => 1]);
        $this->field->gameSystem()->associate($this->campaign->game_system_id);
        $this->field->save();

        $this->worldNpc = Entity::factory()->for($this->gm, 'owner')->for($world)->create([
            'name' => 'Le roi',
            'image_path' => UploadedFile::fake()->image('roi.png')->store('entities', 'local'),
            'field_values' => [(string) $this->field->id => 7],
        ]);
        $this->localNpc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Aldric',
            'gm_notes' => 'Il sert [[Le roi|'.$this->worldNpc->id.']] en secret.',
            'entity_type_id' => EntityType::standard('place')->id,
        ]);
        $this->localNpc->tags()->attach($this->gm->tags()->create(['name' => 'Intrigue']));
        $this->worldNpc->stateIn($this->campaign)->fill(['status' => 'Prisonnier', 'overrides' => [(string) $this->field->id => 2]])->save();

        $relation = new EntityRelation(['label' => 'sert', 'reverse_label' => 'emploie', 'zone' => Zone::GameMaster]);
        $relation->owner()->associate($this->gm);
        $relation->from()->associate($this->localNpc);
        $relation->to()->associate($this->worldNpc);
        $relation->campaign()->associate($this->campaign);
        $relation->save();

        // Appartient à la table : ne doit jamais sortir.
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey le joueur']);
        $character = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->other->id]);
        CharacterGrant::create(['player_character_id' => $character->id, 'kind' => 'information', 'title' => 'Secret du joueur']);

        $rule = new Rule(['title' => 'Filature', 'gm_notes' => 'Jet difficile']);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();
        $shared = new Rule(['title' => 'Combat']);
        $shared->owner()->associate($this->gm);
        $shared->gameSystem()->associate($this->campaign->game_system_id);
        $shared->save();

        $document = new Document([
            'title' => 'Carte de la ville', 'zone' => Zone::GameMaster, 'disk' => 'local',
            'path' => UploadedFile::fake()->image('carte.png', 400, 300)->store('documents', 'local'),
            'original_name' => 'carte.png', 'mime_type' => 'image/png', 'size' => 2048,
        ]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();
        $this->localNpc->documents()->attach($document);

        $scene = $this->campaign->scenarios()->create(['name' => 'Acte I', 'position' => 1])
            ->scenes()->create(['name' => 'Le marché', 'status' => SceneStatus::Played, 'position' => 1]);
        $scene->entities()->attach([$this->localNpc->id => ['note' => 'au stand', 'position' => 0], $sheet->id => ['note' => null, 'position' => 1]]);
        $scene->documents()->attach($document, ['position' => 0]);
        $scene->rules()->attach($rule, ['position' => 0]);

        $this->campaign->pins()->attach([$this->localNpc->id => ['position' => 0], $sheet->id => ['position' => 1]]);

        $secret = new Secret(['title' => 'Aldric trahit le roi']);
        $secret->campaign()->associate($this->campaign);
        $secret->owner()->associate($this->gm);
        $secret->save();
        $secret->entities()->attach($this->localNpc);
        $secret->scenes()->attach($scene);

        $map = new TableMap(['name' => 'La ville', 'grid_size' => 20, 'grid_enabled' => true]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->forceFill(['width' => 400, 'height' => 300, 'view_zoom' => 3, 'ruler' => ['x1' => 0, 'y1' => 0, 'x2' => 5, 'y2' => 5]])->save();
        $token = new MapToken(['label' => 'Aldric', 'x' => 10, 'y' => 20, 'size' => 1, 'hidden' => true, 'show_label' => true]);
        $token->entity()->associate($this->localNpc);
        $map->tokens()->save($token);

        $event = new TimelineEvent(['kind' => 'world', 'date_label' => 'An 300', 'title' => 'Le couronnement', 'description' => 'Pour [[Le roi|'.$this->worldNpc->id.']].', 'zone' => Zone::GameMaster]);
        $event->campaign()->associate($this->campaign);
        $event->user_id = $this->gm->id;
        $event->position = 1;
        $event->save();
    }

    private function archive(): string
    {
        $path = (new CampaignExport($this->campaign->fresh()))->write();
        $this->assertFileExists($path);

        return $path;
    }

    /** Parcours : le MJ exporte sa campagne, un autre MJ l'importe et retrouve tout son contenu. */
    public function test_a_campaign_travels_to_another_account_with_its_content_and_files(): void
    {
        $copy = (new CampaignImport($this->other))->handle($this->archive());

        $this->assertTrue($copy->isGameMaster($this->other));
        $this->assertSame('Les Ombres', $copy->name);
        $this->assertNotSame($this->campaign->game_system_id, $copy->game_system_id);
        $this->assertNotSame($this->campaign->world_id, $copy->world_id);
        $this->assertSame('Valdaria', $copy->world->name);
        $this->assertSame($this->other->id, $copy->world->user_id);

        // Le monde et la campagne gardent leur partage d'origine.
        $king = $copy->availableEntities()->where('name', 'Le roi')->sole();
        $aldric = $copy->availableEntities()->where('name', 'Aldric')->sole();
        $this->assertSame($copy->world_id, $king->world_id);
        $this->assertSame($copy->id, $aldric->campaign_id);
        $this->assertTrue($king->hasImage());
        $this->assertTrue(Storage::disk('local')->exists($king->image_path));
        $this->assertNotSame($this->worldNpc->image_path, $king->image_path);
        $this->assertSame('place', $aldric->type->key);
        $this->assertSame(['Intrigue'], $aldric->tags->pluck('name')->all());
        $this->assertSame($this->other->id, $aldric->tags->first()->user_id);

        // Le lien [[ ]] pointe sur la nouvelle fiche, pas sur l'ancienne.
        $this->assertSame("Il sert [[Le roi|{$king->id}]] en secret.", $aldric->gm_notes);

        $field = $copy->gameSystem->fieldDefinitions()->sole();
        $this->assertSame('Courage', $field->name);
        $this->assertSame(7, $king->field_values[(string) $field->id]);
        $state = $king->stateIn($copy);
        $this->assertSame('Prisonnier', $state->status);
        $this->assertSame(2, $state->overrides[(string) $field->id]);

        $relation = EntityRelation::where('campaign_id', $copy->id)->sole();
        $this->assertSame([$aldric->id, $king->id, 'sert', 'emploie'], [$relation->from_entity_id, $relation->to_entity_id, $relation->label, $relation->reverse_label]);
        $this->assertSame(Zone::GameMaster, $relation->zone);

        $scene = $copy->scenarios()->sole()->scenes()->sole();
        $this->assertSame(['Le marché', SceneStatus::Played], [$scene->name, $scene->status]);
        $this->assertSame(['Aldric'], $scene->entities->pluck('name')->all());
        $this->assertSame('au stand', $scene->entities->first()->pivot->note);
        $this->assertSame(['Filature'], $scene->rules->pluck('title')->all());

        $document = $copy->documents()->sole();
        $this->assertSame('Carte de la ville', $document->title);
        $this->assertTrue(Storage::disk('local')->exists($document->path));
        $this->assertSame([$aldric->id], $document->entities()->pluck('entities.id')->all());

        $this->assertSame(['Combat'], Rule::where('game_system_id', $copy->game_system_id)->pluck('title')->all());
        $this->assertSame(['Filature'], $copy->rules()->pluck('title')->all());
        $this->assertSame([$aldric->id], $copy->pins()->pluck('entities.id')->all());

        $secret = $copy->secrets()->sole();
        $this->assertSame('Aldric trahit le roi', $secret->title);
        $this->assertSame([$aldric->id], $secret->entities()->pluck('entities.id')->all());
        $this->assertSame([$scene->id], $secret->scenes()->pluck('scenes.id')->all());

        $map = $copy->maps()->sole();
        $this->assertSame([$document->id, 'La ville', true], [$map->document_id, $map->name, $map->grid_enabled]);
        // La vue et la règle du moment appartiennent à la table d'origine.
        $this->assertSame(1.0, $map->view_zoom);
        $this->assertNull($map->ruler);
        $this->assertSame([$aldric->id, 'Aldric'], [$map->tokens()->sole()->entity_id, $map->tokens()->sole()->label]);

        $event = $copy->timelineEvents()->sole();
        $this->assertSame(['Le couronnement', 'An 300'], [$event->title, $event->date_label]);
        $this->assertSame("Pour [[Le roi|{$king->id}]].", $event->description);

        // Rien de la table d'origine n'a voyagé.
        $this->assertSame(0, $copy->playerCharacters()->count());
        $this->assertNull($copy->availableEntities()->where('name', 'Harvey le joueur')->first());
        $this->assertSame(1, $copy->members()->count());
    }

    public function test_the_archive_is_downloaded_by_its_game_master_only(): void
    {
        $this->actingAs($this->gm)->get(route('archives.campaign', $this->campaign))
            ->assertOk()->assertHeader('content-type', 'application/zip');

        $this->actingAs($this->other)->get(route('archives.campaign', $this->campaign))->assertForbidden();
        $this->actingAs($this->other)->get(route('archives.template', $this->campaign))->assertForbidden();
    }

    public function test_the_import_form_creates_the_campaign_and_refuses_a_foreign_file(): void
    {
        $path = $this->archive();

        Livewire::actingAs($this->other)->test(CampaignIndex::class)
            ->set('importing', true)
            ->set('archive', UploadedFile::fake()->createWithContent('campagne.zip', file_get_contents($path)))
            ->call('importArchive')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Les Ombres', Campaign::where('user_id', $this->other->id)->sole()->name);

        // Un .zip qui n'est pas une archive CodexFlow est refusé sans rien créer.
        $junk = tempnam(sys_get_temp_dir(), 'junk').'.zip';
        $zip = new ZipArchive;
        $zip->open($junk, ZipArchive::CREATE);
        $zip->addFromString('lisezmoi.txt', 'rien');
        $zip->close();

        Livewire::actingAs($this->other)->test(CampaignIndex::class)
            ->set('importing', true)
            ->set('archive', UploadedFile::fake()->createWithContent('autre.zip', file_get_contents($junk)))
            ->call('importArchive')
            ->assertHasErrors('archive');

        $this->assertSame(1, Campaign::where('user_id', $this->other->id)->count());
    }

    /** Parcours : un MJ partage la structure de son jeu, un autre l'ajoute au sien. */
    public function test_a_game_template_carries_the_structure_without_any_content(): void
    {
        $template = CampaignExport::template($this->campaign, true);

        $this->assertSame(CampaignExport::TEMPLATE_FORMAT, $template['format']);
        $this->assertSame(['Courage'], array_column($template['field_definitions'], 'name'));
        $this->assertSame(['Combat'], array_column($template['rules'], 'title'));
        $this->assertArrayNotHasKey('entities', $template);
        $this->assertStringNotContainsString('Aldric', json_encode($template));

        $theirs = Campaign::factory()->for($this->other, 'owner')->create();
        $existing = new Rule(['title' => 'Combat']);
        $existing->owner()->associate($this->other);
        $existing->gameSystem()->associate($theirs->game_system_id);
        $existing->save();

        $added = (new CampaignImport($this->other))->template(json_encode($template), $theirs);

        $this->assertSame(1, $added['fields']);
        $this->assertSame(0, $added['rules'], 'Une règle du même nom n’est pas ajoutée deux fois.');
        $this->assertSame(['Courage'], $theirs->gameSystem->fieldDefinitions()->pluck('name')->all());
        $this->assertSame(1, Rule::where('game_system_id', $theirs->game_system_id)->count());

        // Réimporter le même modèle n'ajoute rien.
        $again = (new CampaignImport($this->other))->template(json_encode($template), $theirs->fresh());
        $this->assertSame([0, 0], [$again['fields'], $again['rules']]);
        $this->assertSame(1, $theirs->gameSystem->fieldDefinitions()->count());

        $this->expectException(ArchiveException::class);
        (new CampaignImport($this->other))->template('{"format":"autre"}', $theirs);
    }

    public function test_the_template_page_exports_and_imports(): void
    {
        $this->actingAs($this->gm)->get(route('archives.template', [$this->campaign, 'regles' => 1]))
            ->assertOk()->assertHeader('content-type', 'application/json');

        $theirs = Campaign::factory()->for($this->other, 'owner')->create();

        Livewire::actingAs($this->other)->test(FieldsManage::class, ['campaign' => $theirs])
            ->set('template', UploadedFile::fake()->createWithContent('modele.json', json_encode(CampaignExport::template($this->campaign, false))))
            ->call('importTemplate')
            ->assertHasNoErrors()
            ->assertSee('Courage');
    }

    /** Une archive bricolée ne doit ni écrire de fichier dangereux ni laisser passer de valeur inattendue. */
    public function test_a_tampered_archive_is_checked_field_by_field(): void
    {
        $data = [
            'format' => CampaignExport::FORMAT,
            'version' => 1,
            'game_system' => ['name' => 'Jeu', 'image' => '../../etc/passwd'],
            'world' => null,
            'campaign' => ['name' => str_repeat('x', 400)],
            'entity_types' => [['id' => 1, 'key' => 'character', 'name' => 'Personnage']],
            'entities' => [
                ['id' => 5, 'entity_type_id' => 1, 'name' => 'Vrai', 'description' => '[[Fantôme|999]]', 'image' => 'fichiers/1.png'],
                ['id' => 6, 'entity_type_id' => 1, 'name' => ''],
            ],
            'relations' => [['from' => 5, 'to' => 999, 'label' => 'sert'], ['from' => 5, 'to' => 5, 'label' => 'se sert']],
            'documents' => [['id' => 9, 'title' => 'Faux', 'file' => 'fichiers/2.pdf', 'original_name' => 'x.pdf']],
            'maps' => [['document_id' => 9, 'name' => 'Carte', 'width' => 100, 'height' => 100, 'tokens' => [['label' => 'X', 'entity_id' => 999]]]],
            'timeline' => [['kind' => 'inventé', 'title' => 'Jamais']],
        ];

        $path = tempnam(sys_get_temp_dir(), 'forge').'.zip';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE);
        $zip->addFromString('campagne.json', json_encode($data));
        // Un script déguisé en image, et du HTML déguisé en PDF.
        $zip->addFromString('fichiers/1.png', '<?php echo "pwned";');
        $zip->addFromString('fichiers/2.pdf', '<html><script>alert(1)</script></html>');
        $zip->close();

        $copy = (new CampaignImport($this->other))->handle($path);

        $this->assertSame(255, mb_strlen($copy->name));
        $this->assertNull($copy->gameSystem->image_path, 'Un chemin hors de l’archive n’est jamais lu.');

        $entity = $copy->localEntities()->sole();
        $this->assertSame('Vrai', $entity->name);
        $this->assertNull($entity->image_path, 'Le contenu ne correspond pas à l’extension.');
        $this->assertSame('[[Fantôme]]', $entity->description, 'Un lien vers une fiche absente devient du texte.');

        $this->assertSame(0, EntityRelation::where('campaign_id', $copy->id)->count());
        $this->assertSame(0, $copy->documents()->count());
        $this->assertSame(0, $copy->maps()->count(), 'Sans document, pas de carte.');
        $this->assertSame(0, $copy->timelineEvents()->count());
        // Seule l'image de la campagne d'origine est sur le disque : rien n'a été écrit par l'import.
        $this->assertSame([$this->worldNpc->image_path], Storage::disk('local')->files('entities'));
    }

    /** Sauvegarde complète : la table voyage aussi, telle que le MJ la voit, sans compte ni note « Moi seul ». */
    public function test_a_complete_backup_carries_the_table_without_private_notes_or_emails(): void
    {
        $player = User::factory()->create(['name' => 'Alex', 'email' => 'alex.prive@example.com']);
        $this->campaign->members()->attach($player, ['role' => 'player']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Lucy', 'entity_type_id' => EntityType::standard('character')->id]);
        $harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player->id]);
        CharacterGrant::create(['player_character_id' => $harvey->id, 'kind' => 'entity', 'entity_id' => $this->localNpc->id, 'granted_by' => $this->gm->id]);
        CharacterGrant::create(['player_character_id' => $harvey->id, 'kind' => 'possession', 'title' => 'Lanterne', 'quantity' => 2, 'granted_by' => $this->gm->id]);

        $session = $this->campaign->playSessions()->create(['number' => 1, 'title' => 'Le bal', 'started_at' => now()->subDay(), 'ended_at' => now()->subDay()->addHours(3)]);
        $session->notes()->make(['body' => 'Lucy a volé la clé.'])->forceFill(['user_id' => $this->gm->id])->save();
        foreach (['group' => 'Note de toute la table', 'private' => 'Mon secret à moi'] as $visibility => $body) {
            $harvey->notes()->make(['body' => $body, 'visibility' => $visibility])->forceFill(['user_id' => $player->id])->save();
        }
        $this->campaign->messages()->make(['body' => 'Rendez-vous au port.', 'player_character_id' => $harvey->id])->forceFill(['sender_id' => $this->gm->id])->save();

        // L'archive simple ne contient pas la table.
        $zip = new ZipArchive;
        $zip->open($this->archive());
        $this->assertStringNotContainsString('Lucy', $zip->getFromName('campagne.json'));
        $zip->close();

        $this->actingAs($player)->get(route('archives.campaign', [$this->campaign, 'complete' => 1]))->assertForbidden();
        $this->actingAs($this->gm)->get(route('archives.campaign', [$this->campaign, 'complete' => 1]))->assertOk()
            ->assertDownload('codexflow-les-ombres-sauvegarde-complete.zip');

        $path = (new CampaignExport($this->campaign->fresh(), complete: true))->write();
        $zip->open($path);
        $json = $zip->getFromName('campagne.json');
        $zip->close();
        $this->assertStringContainsString('Rendez-vous au port.', $json);
        $this->assertStringContainsString('"player": "Alex"', $json);
        $this->assertStringNotContainsString('alex.prive@example.com', $json);
        $this->assertStringNotContainsString('Mon secret à moi', $json);

        $copy = (new CampaignImport($this->other))->handle($path);

        $this->assertSame(2, $copy->playerCharacters()->count());
        $character = $copy->playerCharacters()->whereHas('entity', fn ($q) => $q->where('name', 'Lucy'))->sole();
        $this->assertNull($character->user_id);
        $this->assertSame(['Aldric', 'Lanterne'], $character->grants->map(fn (CharacterGrant $grant) => $grant->entity?->name ?? $grant->title)->sort()->values()->all());
        $copySession = $copy->playSessions()->sole();
        $this->assertSame('Le bal', $copySession->title);
        $this->assertNotNull($copySession->ended_at);
        $this->assertSame(['Lucy a volé la clé.'], $copySession->notes()->pluck('body')->all());
        $this->assertSame(['Note de toute la table'], $character->notes()->pluck('body')->all());
        $this->assertSame(0, $copy->messages()->count());
    }
}
