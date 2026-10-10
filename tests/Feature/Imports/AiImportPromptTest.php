<?php

namespace Tests\Feature\Imports;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Imports\Create;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

/**
 * « Préparer l'import avec une IA » : le prompt décrit des formats que l'import accepte vraiment.
 */
class AiImportPromptTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->gameSystem->update(['name' => 'Brume & Serment']);
        $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Vigueur', 'group' => 'Caractéristiques', 'type' => FieldType::Number, 'zone' => Zone::Public, 'position' => 1,
        ]);
    }

    public function test_the_game_master_copies_a_prompt_that_knows_the_campaign(): void
    {
        $this->actingAs($this->gm)->get(route('imports.create', $this->campaign))->assertSee(route('imports.ai', $this->campaign));

        $this->actingAs($this->gm)->get(route('imports.ai', $this->campaign))->assertOk()
            ->assertSee(['Copier le prompt', 'Jeu : Brume &amp; Serment', '« Vigueur » : nombre, zone publique, groupe Caractéristiques', 'Personnage', 'GUIDE-IMPORT.md', 'Usage personnel'], false);

        $this->actingAs($this->gm)->get(route('imports.ai.prompt', $this->campaign))->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="sagawyn-prompt-import.md"')
            ->assertSee('Écris le contenu des fiches, les noms de champs et le guide en **français**', false);

        // En anglais, l'IA écrit en anglais ; les en-têtes restent ceux de l'import.
        $this->gm->forceFill(['preferences' => ['locale' => 'en']])->save();
        $this->actingAs($this->gm)->get(route('imports.ai.prompt', $this->campaign))->assertSee(['**anglais**', 'Nom;Type;Résumé;Description;Notes MJ'], false);
    }

    public function test_the_prompt_is_also_a_claude_skill(): void
    {
        $response = $this->actingAs($this->gm)->get(route('imports.ai.skill', $this->campaign))->assertOk()->assertDownload('sagawyn-import-skill.zip');

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $skill = $zip->getFromName('sagawyn-import/SKILL.md');
        $zip->close();

        $this->assertStringStartsWith("---\nname: sagawyn-import\ndescription: ", $skill);
        $this->assertStringContainsString('Jeu : Brume & Serment', $skill);
    }

    public function test_players_do_not_get_the_prompt(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        foreach (['imports.ai', 'imports.ai.prompt', 'imports.ai.skill'] as $route) {
            $this->actingAs($player)->get(route($route, $this->campaign))->assertForbidden();
        }
    }

    public function test_files_written_as_the_prompt_asks_import_without_errors(): void
    {
        $import = function (string $mode, string $name, array $lines) {
            $page = Livewire::actingAs($this->gm)
                ->test(Create::class, ['campaign' => $this->campaign, 'mode' => $mode])
                ->set('file', UploadedFile::fake()->createWithContent($name, "\u{FEFF}".implode("\n", $lines)))
                ->assertHasNoErrors()
                ->assertSee('Importer '.(count($lines) - 1).' lignes');
            $this->assertNotContains('new', $page->get('mapping'), 'Chaque colonne correspond à un champ déjà importé.');
            $page->call('import')->assertHasNoErrors();
        };

        $import('fields', '1-champs.csv', [
            'Nom;Groupe;Type;Zone;Choix;Type de fiche;Modifiable par le joueur',
            'Points de vie;Caractéristiques;compteur;publique;;Personnage;oui',
            'Dé de vie;Caractéristiques;liste;publique;d6|d8;;non',
            'Mentor;Liens;référence;publique;;;non',
            'Motivation cachée;Secrets;texte long;MJ;;;non',
            'Site officiel;Liens;lien;publique;;;non',
            'Né le;Identité;date;publique;;;non',
            'Discret;Compétences;oui/non;publique;;;non',
        ]);

        $import('entities', '2-pnj.csv', [
            'Nom;Type;Résumé;Description;Notes MJ;Vigueur;Points de vie;Dé de vie;Mentor;Motivation cachée;Site officiel;Né le;Discret',
            'Maître Orvel;Personnage;Vieux sage;Il enseigne au village (Livre p. 12).;Ancien cultiste;2;8 / 8;d6;;Rédemption;;01/02/1890;non',
            'Ilka;Personnage;Apprentie;"Élève de [[Maître Orvel]]; curieuse.";;3;11 / 11;d8;Maître Orvel;"Venger son frère, ""coûte que coûte""";https://exemple.fr;;oui',
        ]);

        $import('rules', '3-regles.csv', [
            'Titre;Catégorie;Résumé;Procédure;Notes MJ;Source;Origine;Statut;Zone;Tags',
            'Brume;Glossaire;Voile magique qui recouvre les vallées.;;;Livre p. 4;référence;Disponible;publique;',
            'Serment;Magie;Un serment lie deux personnages.;"Chacun déclare son serment.\nLe MJ fixe le prix.";;Livre p. 30;référence;Disponible;publique;magie, serment',
        ]);

        $import('scenes', '4-scenes.csv', [
            'Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles',
            'La vallée perdue;Les héros cherchent Ilka.;Acte 1;Le départ;Orvel confie sa mission (p. 40).;Prévue;Maître Orvel | Ilka;;Serment',
            'La vallée perdue;;Acte 1;La brume;La route disparaît.;Prévue;Ilka;;Brume',
        ]);

        $ilka = Entity::where('name', 'Ilka')->sole();
        $this->assertSame('Élève de [[Maître Orvel]]; curieuse.', $ilka->description);
        $this->assertEquals(['value' => 11, 'max' => 11], $ilka->field_values[FieldDefinition::where('name', 'Points de vie')->value('id')]);
        $this->assertSame(EntityType::standard('character')->id, $ilka->entity_type_id);
        $this->assertSame(RuleOrigin::Reference, Rule::where('title', 'Serment')->sole()->origin);
        $this->assertSame(SceneStatus::Planned, Scene::where('name', 'Le départ')->sole()->status);
        $this->assertSame(2, Scene::where('name', 'Le départ')->sole()->entities()->count());
    }
}
