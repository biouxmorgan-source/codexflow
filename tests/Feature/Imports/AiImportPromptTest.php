<?php

namespace Tests\Feature\Imports;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Campaigns\Index as CampaignIndex;
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
            ->assertSee(['Copier le prompt', 'Langue du contenu importé', 'Game: Brume &amp; Serment', '&quot;Vigueur&quot;: type `nombre`, zone `publique`, group Caractéristiques', 'Personnage', 'IMPORT-GUIDE.md', 'Personal use'], false);

        // Le prompt est en anglais ; le contenu est écrit dans la langue de l'interface par défaut.
        $this->actingAs($this->gm)->get(route('imports.ai.prompt', $this->campaign))->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="sagawyn-prompt-import.md"')
            ->assertSee(['Write all the content', 'in **French**', '### Ce qui est prévu', '"Importer"'], false);

        // Une autre langue choisie : le contenu change de langue, les en-têtes restent ceux de l'import.
        $this->actingAs($this->gm)->get(route('imports.ai', [$this->campaign, 'lang' => 'de']))->assertOk()
            ->assertSee(route('imports.ai.prompt', [$this->campaign, 'lang' => 'de']), false);
        $this->actingAs($this->gm)->get(route('imports.ai.prompt', [$this->campaign, 'lang' => 'de']))
            ->assertSee(['in **German**', '### Was geplant ist', 'Nom;Type;Résumé;Description;Notes MJ'], false)
            ->assertDontSee('in **French**', false);

        // Une langue inconnue revient à celle de l'interface, dont les noms d'écrans sont repris.
        $this->gm->forceFill(['preferences' => ['locale' => 'en']])->save();
        $this->actingAs($this->gm)->get(route('imports.ai.prompt', [$this->campaign, 'lang' => 'xx']))
            ->assertSee(['in **English**', '### What’s planned', '"Import"'], false);
    }

    public function test_a_generic_prompt_prepares_a_whole_campaign_archive(): void
    {
        Livewire::actingAs($this->gm)->test(CampaignIndex::class)->set('importing', true)->assertSee(route('imports.ai.archive'));

        $this->actingAs($this->gm)->get(route('imports.ai.archive'))->assertOk()
            ->assertSee(['Copier le prompt', 'Importer une campagne', 'codexflow-campaign', 'Assemble the campaign archive', 'Personal use'], false)
            ->assertDontSee(['Les Ombres', 'Brume &amp; Serment', '« Vigueur » : nombre'], false);

        $prompt = $this->actingAs($this->gm)->get(route('imports.ai.archive.prompt'))->assertOk()->getContent();
        // Le format de l'archive est repris en annexe, ses titres décalés sous celui de l'annexe.
        $this->assertStringContainsString("\n### campagne.json\n", $prompt);
        $this->assertStringNotContainsString('# SagaWyn campaign archive', $prompt);

        $response = $this->actingAs($this->gm)->get(route('imports.ai.archive.skill', ['lang' => 'es']))->assertOk()->assertDownload('sagawyn-import-skill.zip');
        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $this->assertStringContainsString('SagaWyn - <Title>.zip', $zip->getFromName('sagawyn-import/SKILL.md'));
        $this->assertStringContainsString('in **Spanish**', $zip->getFromName('sagawyn-import/SKILL.md'));
        $zip->close();
    }

    public function test_the_prompt_is_also_a_claude_skill(): void
    {
        $response = $this->actingAs($this->gm)->get(route('imports.ai.skill', $this->campaign))->assertOk()->assertDownload('sagawyn-import-skill.zip');

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $skill = $zip->getFromName('sagawyn-import/SKILL.md');
        $checker = $zip->getFromName('sagawyn-import/scripts/check.py');
        $archive = $zip->getFromName('sagawyn-import/references/archive-format.md');
        $zip->close();

        $this->assertStringStartsWith("---\nname: sagawyn-import\ndescription: ", $skill);
        $this->assertStringContainsString('Game: Brume & Serment', $skill);
        $this->assertStringContainsString('scripts/check.py', $skill);
        $this->assertStringContainsString('codexflow-campaign', $archive);
        $this->assertStringContainsString('def main(', $checker);

        // Le prompt à copier ne parle pas des fichiers du skill, qu'il n'a pas.
        $this->assertStringNotContainsString('scripts/check.py', $this->actingAs($this->gm)->get(route('imports.ai.prompt', $this->campaign))->getContent());
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
            $page->set('rightsDeclared', true)->call('import')->assertHasNoErrors();
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
