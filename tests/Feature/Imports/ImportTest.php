<?php

namespace Tests\Feature\Imports;

use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Imports\Create;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
    }

    public function test_entities_are_imported_from_a_french_excel_csv_with_new_fields(): void
    {
        $existing = Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create([
            'name' => 'Shen Chu',
            'summary' => 'Ancien résumé',
            'description' => 'À garder',
        ]);

        // Export Excel français : point-virgule, Windows-1252, virgule décimale.
        $csv = mb_convert_encoding(implode("\r\n", [
            'Nom;Type;Résumé;Force;Discrétion;Né le',
            'Shen Chu;Personnage;Herboriste;2,5;oui;01/02/1990',
            'Loup des cendres;créature;"Prédateur; rapide";5;non;',
            ';Personnage;Sans nom;1;oui;',
            'Golem;Machine;;9;non;',
            'Mira;;Colporteuse;beaucoup;non;',
        ]), 'Windows-1252', 'UTF-8');

        $component = Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('file', UploadedFile::fake()->createWithContent('pnj.csv', $csv))
            ->assertHasNoErrors()
            ->assertSet('mapping', [0 => 'name', 1 => 'type', 2 => 'summary', 3 => 'new', 4 => 'new', 5 => 'new'])
            ->set('newGroup', 'Caractéristiques')
            ->assertSee(['Force · Nombre', 'Discrétion · Oui/non', 'Né le · Date'])
            ->assertSee(['Ligne 4', 'nom manquant', 'type de fiche « Machine » inconnu', 'Ligne 6 (Mira)', '« beaucoup » n\'est pas un nombre'])
            ->assertSee('Importer 2 lignes');

        $this->assertSame(0, FieldDefinition::count(), 'Rien n\'est créé avant validation.');

        $component->call('import')->assertHasNoErrors();
        $component->assertSee('1 créé, 1 mis à jour, 3 nouveaux champs');

        $strength = FieldDefinition::where('name', 'Force')->sole();
        $this->assertSame(FieldType::Number, $strength->type);
        $this->assertSame('Caractéristiques', $strength->group);
        $this->assertNull($strength->entity_type_id, 'Avec une colonne Type, les champs valent pour tous les types.');

        $shen = $existing->fresh();
        $this->assertSame('Herboriste', $shen->summary);
        $this->assertSame('À garder', $shen->description);
        $this->assertSame(2.5, $shen->fieldValue($strength));
        $this->assertSame('1990-02-01', $shen->fieldValue(FieldDefinition::where('name', 'Né le')->sole()));

        $wolf = Entity::where('name', 'Loup des cendres')->sole();
        $this->assertSame(EntityType::standard('creature')->id, $wolf->entity_type_id);
        $this->assertSame('Prédateur; rapide', $wolf->summary);
        $this->assertSame($this->campaign->world_id, $wolf->world_id);
        $this->assertFalse($wolf->fieldValue(FieldDefinition::where('name', 'Discrétion')->sole()));
    }

    public function test_json_import_maps_columns_to_existing_fields(): void
    {
        $die = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Dé de vie', 'type' => FieldType::Select, 'options' => ['d6', 'd8'], 'zone' => Zone::Public,
        ]);

        $json = json_encode([
            ['nom' => 'Aldric', 'dé de vie' => 'D8', 'secret' => 'Indicateur'],
            ['nom' => 'Mira', 'dé de vie' => 'd20'],
        ]);

        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('scope', 'campaign')
            ->set('file', UploadedFile::fake()->createWithContent('pnj.json', $json))
            ->assertSet('mapping', [0 => 'name', 1 => 'field:'.$die->id, 2 => 'gm_notes'])
            ->assertSee('ne fait pas partie des choix')
            ->call('import');

        $aldric = Entity::where('name', 'Aldric')->sole();
        $this->assertSame('d8', $aldric->fieldValue($die));
        $this->assertSame('Indicateur', $aldric->gm_notes);
        $this->assertSame($this->campaign->id, $aldric->campaign_id);
        $this->assertFalse(Entity::where('name', 'Mira')->exists());
    }

    public function test_values_for_fields_of_another_entity_type_are_flagged_and_skipped(): void
    {
        $strength = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Force', 'type' => FieldType::Number, 'zone' => Zone::Public,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);

        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('file', UploadedFile::fake()->createWithContent('b.csv', "Nom;Type;Force\nLoup;Créature;5\nAldric;Personnage;3"))
            ->assertSee('Force ne concerne que les fiches Personnage : valeur ignorée')
            ->call('import');

        $this->assertNull(Entity::where('name', 'Loup')->sole()->fieldValue($strength));
        $this->assertSame(3, Entity::where('name', 'Aldric')->sole()->fieldValue($strength));
    }

    public function test_one_column_fills_same_named_fields_of_each_entity_type(): void
    {
        $fields = $this->campaign->gameSystem->fieldDefinitions();
        $characterStrength = $fields->create([
            'name' => 'Force', 'type' => FieldType::Number, 'zone' => Zone::Public,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
        $creatureStrength = $fields->create([
            'name' => 'Force', 'type' => FieldType::Text, 'zone' => Zone::Public,
            'entity_type_id' => EntityType::standard('creature')->id,
        ]);

        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('file', UploadedFile::fake()->createWithContent('b.csv', "Nom;Type;Force\nLoup;Créature;5 (2D6)\nAldric;Personnage;3"))
            ->assertDontSee('valeur ignorée')
            ->assertDontSee('pas un nombre')
            ->call('import');

        $wolf = Entity::where('name', 'Loup')->sole();
        $this->assertSame('5 (2D6)', $wolf->fieldValue($creatureStrength));
        $this->assertNull($wolf->fieldValue($characterStrength));
        $this->assertSame(3, Entity::where('name', 'Aldric')->sole()->fieldValue($characterStrength));
    }

    public function test_a_list_of_fields_is_imported_for_the_game(): void
    {
        $csv = implode("\n", [
            'Nom,Groupe,Type,Zone,Choix,Type de fiche',
            'Force,Caractéristiques,nombre,publique,,Personnage',
            'Dé de vie,Caractéristiques,liste,,d4|d6|d8,',
            'Pouvoir caché,Capacités,texte long,MJ,,',
            'Chance,,pourcentage,,,',
            'Rang,,liste,,,',
        ]);

        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('mode', 'fields')
            ->set('file', UploadedFile::fake()->createWithContent('champs.csv', $csv))
            ->assertSee(['type « pourcentage » inconnu', 'une liste a besoin de choix', 'Importer 3 lignes'])
            ->call('import')
            ->assertSee('3 créés');

        $this->assertSame(['Force', 'Dé de vie', 'Pouvoir caché'], FieldDefinition::ordered()->pluck('name')->all());
        $this->assertSame(EntityType::standard('character')->id, FieldDefinition::where('name', 'Force')->value('entity_type_id'));
        $this->assertSame(['d4', 'd6', 'd8'], FieldDefinition::where('name', 'Dé de vie')->sole()->options);
        $this->assertSame(Zone::GameMaster, FieldDefinition::where('name', 'Pouvoir caché')->sole()->zone);
    }

    public function test_rules_are_imported_for_the_game_and_existing_ones_updated(): void
    {
        $existing = new Rule(['title' => 'Kangling', 'summary' => 'Ancienne définition', 'procedure' => 'À garder']);
        $existing->owner()->associate($this->gm);
        $existing->game_system_id = $this->campaign->game_system_id;
        $existing->save();

        $csv = implode("\r\n", [
            'Titre;Catégorie;Résumé;Procédure;Notes MJ;Origine;Statut;Zone;Tags',
            'kangling;Glossaire;Cor en fémur humain;;;;;;Glossaire',
            'Magie poussée;Magie;Sorciers à 0 SAN;"Lancez 1D100.',
            'Comparez au Mythe.";Option;maison;à tester;MJ;Magie, Option',
            ';Glossaire;Sans titre;;;;;;',
            'Dakini;Glossaire;Être féminin;;;officielle;;secret;',
            'Ghat;Glossaire;Marches;;;inventée;périmée;;',
        ]);

        $component = Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign, 'mode' => 'rules'])
            ->set('file', UploadedFile::fake()->createWithContent('regles.csv', $csv))
            ->assertSee(['Mise à jour', 'titre manquant', 'origine « inventée » inconnue', 'statut « périmée » inconnu', 'Importer 3 lignes']);

        $this->assertSame(1, Rule::count(), 'Rien n\'est créé avant validation.');

        $component->call('import')->assertHasNoErrors()->assertSee('2 créés, 1 mis à jour');

        $kangling = $existing->fresh();
        $this->assertSame('Cor en fémur humain', $kangling->summary);
        $this->assertSame('À garder', $kangling->procedure, 'Une case vide ne remplace rien.');
        $this->assertSame('Glossaire', $kangling->category);
        $this->assertSame(['Glossaire'], $kangling->tags->pluck('name')->all());

        $magic = Rule::where('title', 'Magie poussée')->sole();
        $this->assertSame("Lancez 1D100.\nComparez au Mythe.", $magic->procedure);
        $this->assertSame(RuleOrigin::House, $magic->origin);
        $this->assertSame(RuleStatus::ToTest, $magic->status);
        $this->assertSame(Zone::GameMaster, $magic->zone);
        $this->assertSame($this->campaign->game_system_id, $magic->game_system_id);
        $this->assertNull($magic->campaign_id);
        $this->assertSame(['Magie', 'Option'], $magic->tags->pluck('name')->all());

        $this->assertSame(Zone::GameMaster, Rule::where('title', 'Dakini')->sole()->zone);
    }

    public function test_scenarios_and_scenes_are_imported_with_their_links(): void
    {
        $wang = Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create(['name' => 'Wang Enlai']);
        $peking = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Pékin']);
        $elsewhere = Campaign::factory()->for($this->gm, 'owner')->create();
        Entity::factory()->for($this->gm, 'owner')->for($elsewhere)->create(['name' => 'Inconnu ailleurs']);

        $handout = new Document(['title' => 'Indice - Portes 1', 'disk' => 'local', 'path' => 'x.jpg', 'original_name' => 'Indice - Portes 1.jpg', 'mime_type' => 'image/jpeg', 'size' => 10]);
        $handout->owner()->associate($this->gm);
        $handout->campaign()->associate($this->campaign);
        $handout->save();

        $rule = new Rule(['title' => 'Poursuite']);
        $rule->owner()->associate($this->gm);
        $rule->game_system_id = $this->campaign->game_system_id;
        $rule->save();

        $scenario = $this->campaign->scenarios()->create(['name' => '1. La cité', 'position' => 1]);
        $existing = $scenario->scenes()->create(['name' => 'La conférence', 'description' => 'À garder', 'chapter' => 'Ancien', 'position' => 1]);

        $csv = implode("\r\n", [
            'Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles',
            '1. La cité;Pékin, 1923;Pékin;la conférence;;jouée;wang enlai | Pekin;;',
            '1. La cité;;Pékin;La tempête;"Une tempête.',
            'Le bureau est fouillé.";;Wang Enlai|Inconnu ailleurs;Indice - Portes 1;Poursuite',
            '2. Vers les portes;Le désert;;Départ;;;;;',
            ';;;Sans scénario;;;;;',
            '2. Vers les portes;;;Départ;;;;;',
            '2. Vers les portes;;;Fin;;terminée;;;',
        ]);

        $component = Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign, 'mode' => 'scenes'])
            ->set('file', UploadedFile::fake()->createWithContent('scenes.csv', $csv))
            ->assertSee(['Mise à jour', 'fiche « Inconnu ailleurs » introuvable', 'scénario manquant', 'déjà présente ligne 4', 'statut « terminée » inconnu', 'Importer 3 lignes']);

        $this->assertSame(1, Scene::count(), 'Rien n\'est créé avant validation.');

        $component->call('import')->assertHasNoErrors()->assertSee('2 créés, 1 mis à jour, 1 nouveau scénario');

        $this->assertSame('Pékin, 1923', $scenario->fresh()->summary);
        $existing->refresh();
        $this->assertSame('À garder', $existing->description, 'Une case vide ne remplace rien.');
        $this->assertSame('Pékin', $existing->chapter);
        $this->assertSame(SceneStatus::Played, $existing->status);
        $this->assertSame([$wang->id, $peking->id], $existing->entities->pluck('id')->all());

        $storm = Scene::where('name', 'La tempête')->sole();
        $this->assertSame("Une tempête.\nLe bureau est fouillé.", $storm->description);
        $this->assertSame(SceneStatus::Planned, $storm->status);
        $this->assertSame(2, $storm->position);
        $this->assertSame([$wang->id], $storm->entities->pluck('id')->all());
        $this->assertSame([$handout->id], $storm->documents->pluck('id')->all());
        $this->assertSame([$rule->id], $storm->rules->pluck('id')->all());

        $second = Scenario::where('name', '2. Vers les portes')->sole();
        $this->assertSame($this->campaign->id, $second->campaign_id);
        $this->assertSame(2, $second->position);
        $this->assertSame(['Départ'], $second->scenes->pluck('name')->all());

        $this->get(route('imports.example', [$this->campaign, 'scenes']))->assertOk()->assertDownload('codexflow-exemple-scenes.csv');
        $this->get(route('scenarios.index', $this->campaign))->assertSee(route('imports.create', [$this->campaign, 'mode' => 'scenes']), false);
    }

    public function test_rules_can_be_imported_for_the_campaign_only(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('mode', 'rules')
            ->set('ruleScope', 'campaign')
            ->set('file', UploadedFile::fake()->createWithContent('regles.json', json_encode([['titre' => 'Voyage', 'procédure' => 'Un test par jour']])))
            ->call('import')
            ->assertSee('1 créé');

        $rule = Rule::sole();
        $this->assertSame($this->campaign->id, $rule->campaign_id);
        $this->assertNull($rule->game_system_id);
        $this->assertSame(RuleOrigin::Reference, $rule->origin);
        $this->assertSame(Zone::Public, $rule->zone);

        $this->get(route('imports.example', [$this->campaign, 'regles']))->assertOk()->assertDownload('codexflow-exemple-regles.csv');
    }

    public function test_unreadable_files_are_reported(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $this->campaign])
            ->set('file', UploadedFile::fake()->createWithContent('vide.json', '{"a": 1'))
            ->assertSee('Le fichier JSON est illisible')
            ->set('file', UploadedFile::fake()->createWithContent('page.html', '<p>x</p>'))
            ->assertHasErrors('file');
    }

    public function test_import_and_examples_are_reserved_to_the_game_owner(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('imports.create', $this->campaign))->assertForbidden();
        $this->actingAs($stranger)->get(route('imports.example', [$this->campaign, 'fiches']))->assertForbidden();

        $this->actingAs($this->gm)
            ->get(route('imports.example', [$this->campaign, 'champs']))
            ->assertOk()
            ->assertDownload('codexflow-exemple-champs.csv');
    }
}
