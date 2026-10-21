<?php

namespace Tests\Feature\Imports;

use App\Actions\Account\DeleteAccount;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Imports\Create;
use App\Models\Campaign;
use App\Models\ContentDeclaration;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use App\Support\Archive\CampaignExport;
use App\Support\ContentDeclarations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

/** Cahier des charges « Gestion des droits et responsabilités lors des imports » (17/10/2026). */
class ContentDeclarationTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
    }

    private function csv(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('pnj.csv', "Nom;Résumé\nAldric;Forgeron");
    }

    public function test_an_import_without_the_declaration_is_refused_by_the_server(): void
    {
        Livewire::actingAs($this->gm)->test(Create::class, ['campaign' => $this->campaign])
            ->assertSet('rightsDeclared', false)
            ->set('file', $this->csv())
            ->assertSee('Je certifie disposer des droits')
            ->call('import')
            ->assertHasErrors('rightsDeclared');

        $this->assertFalse(Entity::where('name', 'Aldric')->exists());
        $this->assertSame(0, ContentDeclaration::count());
    }

    public function test_an_accepted_import_leaves_a_dated_versioned_trace_and_asks_again_next_time(): void
    {
        $this->travelTo(now()->setMicroseconds(0));

        Livewire::actingAs($this->gm)->test(Create::class, ['campaign' => $this->campaign])
            ->set('file', $this->csv())
            ->set('rightsDeclared', true)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('rightsDeclared', false)
            ->set('file', $this->csv())
            ->call('import')
            ->assertHasErrors('rightsDeclared');

        $declaration = ContentDeclaration::sole();
        $this->assertTrue(Entity::where('name', 'Aldric')->exists());
        $this->assertSame($this->gm->id, $declaration->user_id);
        $this->assertSame($this->gm->email, $declaration->user_email);
        $this->assertSame($this->campaign->id, $declaration->campaign_id);
        $this->assertSame('import', $declaration->kind);
        $this->assertSame('csv-entities', $declaration->import_type);
        $this->assertSame(ContentDeclarations::current(), $declaration->declaration_version);
        $this->assertTrue($declaration->declaration_accepted);
        $this->assertTrue($declaration->accepted_at->equalTo(now()));
        $this->assertSame('success', $declaration->import_status);
        $this->assertTrue(str($declaration->id)->isUuid());
    }

    public function test_the_campaign_archive_and_template_imports_require_it_too(): void
    {
        $path = (new CampaignExport($this->campaign))->write();
        $archive = fn () => UploadedFile::fake()->createWithContent('campagne.zip', file_get_contents($path));

        Livewire::actingAs($this->gm)->test(CampaignIndex::class)
            ->set('importing', true)
            ->set('archive', $archive())
            ->call('importArchive')
            ->assertHasErrors('rightsDeclared');
        $this->assertSame(1, Campaign::count());

        Livewire::actingAs($this->gm)->test(CampaignIndex::class)
            ->set('importing', true)
            ->set('archive', $archive())
            ->set('rightsDeclared', true)
            ->call('importArchive')
            ->assertHasNoErrors();

        $copy = Campaign::whereKeyNot($this->campaign->id)->sole();
        $this->assertSame(['archive', 'success', $copy->id], [
            ContentDeclaration::sole()->import_type, ContentDeclaration::sole()->import_status, ContentDeclaration::sole()->campaign_id,
        ]);

        // Une archive refusée laisse une trace en échec.
        $junk = tempnam(sys_get_temp_dir(), 'junk').'.zip';
        $zip = new ZipArchive;
        $zip->open($junk, ZipArchive::CREATE);
        $zip->addFromString('lisezmoi.txt', 'rien');
        $zip->close();

        Livewire::actingAs($this->gm)->test(CampaignIndex::class)
            ->set('importing', true)
            ->set('archive', UploadedFile::fake()->createWithContent('autre.zip', file_get_contents($junk)))
            ->set('rightsDeclared', true)
            ->call('importArchive')
            ->assertHasErrors('archive');
        $this->assertSame(1, ContentDeclaration::where('import_type', 'archive')->where('import_status', 'failed')->count());

        $template = UploadedFile::fake()->createWithContent('modele.json', json_encode(CampaignExport::template($this->campaign, false)));
        Livewire::actingAs($this->gm)->test(FieldsManage::class, ['campaign' => $this->campaign])
            ->set('template', $template)
            ->call('importTemplate')
            ->assertHasErrors('rightsDeclared')
            ->set('rightsDeclared', true)
            ->call('importTemplate')
            ->assertHasNoErrors();
        $this->assertSame(1, ContentDeclaration::where('import_type', 'template')->where('import_status', 'success')->count());
    }

    public function test_the_trace_is_in_my_data_and_survives_account_deletion_for_five_years(): void
    {
        $this->gm->forceFill(['is_admin' => false])->save();
        $declaration = ContentDeclaration::accept($this->gm, 'csv-entities', $this->campaign);
        $declaration->finish(true);

        $this->actingAs($this->gm)->get(route('account.data'))->assertOk()
            ->assertJsonPath('content_declarations.0.import_id', $declaration->id)
            ->assertJsonPath('content_declarations.0.declaration_version', ContentDeclarations::current());

        app(DeleteAccount::class)->handle($this->gm);

        $kept = $declaration->fresh();
        $this->assertNull($kept->user_id);
        $this->assertNotNull($kept->account_deleted_at);
        $this->assertSame($this->gm->email, $kept->user_email);

        $this->travel(4)->years();
        $this->artisan('model:prune', ['--model' => ContentDeclaration::class]);
        $this->assertNotNull($declaration->fresh());

        $this->travel(2)->years();
        $this->artisan('model:prune', ['--model' => ContentDeclaration::class]);
        $this->assertNull($declaration->fresh());
    }

    public function test_every_declaration_text_is_translated_and_a_separate_publication_text_exists(): void
    {
        $this->assertNotSame(ContentDeclarations::text('import'), ContentDeclarations::text('publication'));

        foreach (array_keys(config('codexflow.locales')) as $locale) {
            if ($locale === 'fr') {
                continue;
            }
            $translations = json_decode(file_get_contents(lang_path($locale.'.json')), true);

            foreach (ContentDeclarations::VERSIONS as $versions) {
                foreach ($versions as $paragraphs) {
                    foreach ($paragraphs as $paragraph) {
                        $this->assertArrayHasKey($paragraph, $translations, "{$locale} : {$paragraph}");
                    }
                }
            }
        }
    }

    public function test_the_import_screens_link_to_the_terms(): void
    {
        $terms = route('privacy').'#contenus-utilisateurs';

        $this->get(route('privacy'))->assertOk()
            ->assertSee('id="contenus-utilisateurs"', false)
            ->assertSee(['Conditions d’utilisation : contenus utilisateurs et propriété intellectuelle', 'ne vérifie pas systématiquement']);

        $this->actingAs($this->gm);
        Livewire::test(Create::class, ['campaign' => $this->campaign])->set('file', $this->csv())->assertSee($terms, false);
        Livewire::test(CampaignIndex::class)->set('importing', true)->assertSee($terms, false);
        Livewire::test(FieldsManage::class, ['campaign' => $this->campaign])->assertSee($terms, false);
    }
}
