<?php

namespace Tests\Feature\Exports;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Imports\Create;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ExportTest extends TestCase
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

    public function test_exported_sheets_can_be_reimported_as_a_template(): void
    {
        $fields = $this->campaign->gameSystem->fieldDefinitions();
        $characterStrength = $fields->create(['name' => 'Force', 'type' => FieldType::Number, 'zone' => Zone::Public, 'entity_type_id' => EntityType::standard('character')->id]);
        $creatureStrength = $fields->create(['name' => 'Force', 'type' => FieldType::Text, 'zone' => Zone::Public, 'entity_type_id' => EntityType::standard('creature')->id]);
        $stealth = $fields->create(['name' => 'Discrétion', 'type' => FieldType::Boolean, 'zone' => Zone::Public]);
        $secret = $fields->create(['name' => 'Secret', 'type' => FieldType::LongText, 'zone' => Zone::GameMaster]);

        Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create([
            'entity_type_id' => EntityType::standard('character')->id,
            'name' => 'Aldric', 'summary' => 'Tavernier', 'description' => null, 'gm_notes' => 'Espion',
            'field_values' => [(string) $characterStrength->id => 2.5, (string) $stealth->id => true, (string) $secret->id => "Doit de l'argent\nà la guilde"],
        ]);
        Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'entity_type_id' => EntityType::standard('creature')->id,
            'name' => 'Loup', 'summary' => null, 'description' => null, 'gm_notes' => null, 'field_values' => [(string) $creatureStrength->id => '5 (2D6)'],
        ]);

        $csv = $this->actingAs($this->gm)->get(route('exports.download', [$this->campaign, 'fiches']))
            ->assertOk()->assertDownload()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBFNom;Type;Résumé;Description;\"Notes MJ\";Force;Discrétion;Secret\n", $csv);
        $this->assertStringContainsString("Aldric;Personnage;Tavernier;;Espion;2,5;Oui;\"Doit de l'argent\nà la guilde\"", $csv);
        $this->assertStringContainsString('Loup;Créature;;;;"5 (2D6)";;', $csv);

        // Filtré par type : seules les créatures, avec leurs champs.
        $creatures = $this->get(route('exports.download', [$this->campaign, 'fiches', 'type' => EntityType::standard('creature')->id]))->streamedContent();
        $this->assertStringNotContainsString('Aldric', $creatures);

        // Le fichier se réimporte tel quel dans une autre campagne du même jeu.
        $other = Campaign::factory()->for($this->gm, 'owner')->create(['game_system_id' => $this->campaign->game_system_id]);

        $import = Livewire::actingAs($this->gm)
            ->test(Create::class, ['campaign' => $other])
            ->set('file', UploadedFile::fake()->createWithContent('export.csv', $csv))
            ->assertDontSee('valeur ignorée');
        $this->assertNotContains('new', $import->get('mapping'), 'Chaque colonne retrouve son champ.');
        $import->call('import')->assertHasNoErrors();

        $copy = Entity::where('campaign_id', $other->id)->where('name', 'Aldric')->sole();
        $this->assertSame(2.5, $copy->fieldValue($characterStrength));
        $this->assertTrue($copy->fieldValue($stealth));
        $this->assertSame("Doit de l'argent\nà la guilde", $copy->fieldValue($secret));
        $this->assertSame('Espion', $copy->gm_notes);
        $this->assertSame('5 (2D6)', Entity::where('campaign_id', $other->id)->where('name', 'Loup')->sole()->fieldValue($creatureStrength));
    }

    public function test_fields_rules_and_scenes_are_exported_in_the_import_format(): void
    {
        $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Dé de vie', 'group' => 'Caractéristiques', 'type' => FieldType::Select, 'options' => ['d6', 'd8'],
            'zone' => Zone::GameMaster, 'entity_type_id' => EntityType::standard('character')->id,
        ]);

        $rule = new Rule(['title' => 'Poursuite', 'category' => 'Combat', 'summary' => 'Qui rattrape qui']);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();
        $rule->tags()->attach(Tag::idsFromInput($this->gm, 'action, extérieur'));

        $wang = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Wang']);
        $scenario = $this->campaign->scenarios()->create(['name' => '1. La cité', 'summary' => 'Prologue', 'position' => 1]);
        $this->campaign->scenarios()->create(['name' => 'Vide', 'position' => 2]);
        $first = $scenario->scenes()->create(['name' => 'Conférence', 'chapter' => 'Pékin', 'position' => 1, 'status' => SceneStatus::Played]);
        $first->entities()->attach($wang, ['position' => 0]);
        $first->rules()->attach($rule, ['position' => 0]);
        $scenario->scenes()->create(['name' => 'Départ', 'position' => 2]);

        $this->actingAs($this->gm);

        $this->assertSame(
            "\xEF\xBB\xBFNom;Groupe;Type;Zone;Choix;\"Type de fiche\"\n\"Dé de vie\";Caractéristiques;liste;MJ;d6|d8;Personnage\n",
            $this->get(route('exports.download', [$this->campaign, 'champs']))->streamedContent(),
        );

        $this->assertStringContainsString(
            'Poursuite;Combat;"Qui rattrape qui";;;;Référence;Disponible;publique;"action, extérieur"',
            $this->get(route('exports.download', [$this->campaign, 'regles']))->streamedContent(),
        );

        $scenes = $this->get(route('exports.download', [$this->campaign, 'scenes']))->streamedContent();
        $this->assertStringContainsString("\"1. La cité\";Prologue;Pékin;Conférence;;Jouée;Wang;;Poursuite\n\"1. La cité\";;;Départ;;Prévue;;;\n", $scenes);
        $this->assertStringNotContainsString('Vide', $scenes, 'Un scénario sans scène ne donne aucune ligne.');

        // Réimporté dans la même campagne, chaque scène est mise à jour sans doublon ni avertissement.
        Livewire::test(Create::class, ['campaign' => $this->campaign, 'mode' => 'scenes'])
            ->set('file', UploadedFile::fake()->createWithContent('scenes.csv', $scenes))
            ->assertDontSee('introuvable')
            ->call('import')
            ->assertHasNoErrors();
        $this->assertSame(2, Scene::count());
    }

    public function test_only_the_game_master_can_export(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $this->actingAs($player)->get(route('exports.download', [$this->campaign, 'fiches']))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('exports.download', [$this->campaign, 'regles']))->assertForbidden();
        $this->actingAs($this->gm)->get(route('exports.download', [$this->campaign, 'autre']))->assertNotFound();

        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))->assertSee(route('exports.download', [$this->campaign, 'fiches']));
    }
}
