<?php

namespace Tests\Feature\Book;

use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Livewire\Account\AiKey;
use App\Livewire\Book\Show as BookShow;
use App\Livewire\Scenes\Form;
use App\Livewire\Scenes\Show as SceneShow;
use App\Models\Campaign;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\User;
use App\Support\AdventureBook;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class AdventureBookTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Scenario $scenario;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'La Lanterne de Brumeval']);
        $this->scenario = $this->campaign->scenarios()->create(['name' => 'Le port']);
    }

    private function scene(string $name, array $attributes = []): Scene
    {
        return $this->scenario->scenes()->create(['name' => $name, 'position' => $this->scenario->scenes()->count() + 1, ...$attributes]);
    }

    /** Parcours : le MJ raconte ce qui s'est passé, ou coche « Jouée comme prévu », sans réécrire la préparation. */
    public function test_the_game_master_records_what_happened_or_played_as_planned(): void
    {
        $scene = $this->scene('Arrivée au port', ['description' => 'Le gardien accueille les personnages.']);

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'scene' => $scene])
            ->set('whatHappened', 'Les personnages ont **ignoré** le gardien.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Les personnages ont **ignoré** le gardien.', $scene->fresh()->what_happened);
        $this->assertFalse($scene->fresh()->played_as_planned);

        $other = $this->scene('Le phare éteint', ['description' => 'Le phare s’éteint.']);

        Livewire::actingAs($this->gm)
            ->test(SceneShow::class, ['campaign' => $this->campaign, 'scene' => $other])
            ->assertSee('Ce qui est prévu')
            ->assertSee('Ce qui s’est passé')
            ->call('togglePlayedAsPlanned')
            ->assertSee('La table a suivi ce qui était prévu');

        $other->refresh();
        $this->assertTrue($other->played_as_planned);
        $this->assertSame(SceneStatus::Played, $other->status);
    }

    public function test_the_book_tells_played_scenes_in_order_with_session_summaries(): void
    {
        $this->scene('Arrivée au port', ['description' => 'Prévu : accueil.', 'what_happened' => 'Les héros arrivent sous la pluie.']);
        $this->scene('Le phare éteint', ['description' => "### Ce qui est prévu\n\nLe phare s’éteint.", 'played_as_planned' => true, 'status' => 'played']);
        $this->scene('La crique', ['description' => "### Ce qui est prévu\n\nUne embuscade.\n\n### Ce qui s'est passé (séance 2)\n\nLes héros ont négocié avec les contrebandiers."]);
        $this->scene('Le repaire de la sorcière', ['description' => 'Pas encore joué.']);
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now(), 'ended_at' => now(), 'summary' => 'Une nuit de tempête.']);

        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->assertSeeInOrder(['La Lanterne de Brumeval', 'Le port', 'Arrivée au port', 'Les héros arrivent sous la pluie.', 'Le phare éteint', 'Le phare s’éteint.', 'La crique', 'Les héros ont négocié avec les contrebandiers.', 'Les séances', 'Session 1', 'Une nuit de tempête.'])
            ->assertDontSee('Prévu : accueil.')
            ->assertDontSee('Une embuscade.')
            ->assertDontSee('Le repaire de la sorcière');

        // Une scène « Jouée » sans récit reprend ce qui était prévu, avec un rappel au MJ, absent de l'impression.
        $this->scene('Le marché', ['description' => 'On y vend des lanternes.', 'status' => 'played']);

        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->assertSee('On y vend des lanternes.')
            ->assertSee('Scène jouée sans récit');

        $this->actingAs($this->gm)->get(route('book.print', $this->campaign))
            ->assertOk()
            ->assertSee('On y vend des lanternes.')
            ->assertDontSee('Scène jouée sans récit')
            ->assertSee('Enregistrer au format PDF');

        $markdown = (new AdventureBook($this->campaign))->markdown();
        $this->assertStringContainsString('#### La crique', $markdown);
        $this->assertStringNotContainsString('Ce qui est prévu', $markdown);
    }

    /** Une scène préparée par une IA dans une autre langue que celle du MJ garde ses deux sections. */
    public function test_the_sections_are_recognised_in_every_language(): void
    {
        $english = $this->scene('The cove', ['description' => "### What's planned\n\nAn ambush.\n\n### What happened (session 2)\n\nThe heroes bargained."]);
        $german = $this->scene('Die Bucht', ['description' => "## Was geplant ist\n\nEin Hinterhalt.", 'status' => 'played']);

        $this->assertSame('**What happened (session 2)**'."\n\nThe heroes bargained.", AdventureBook::happened($english));
        $this->assertSame('An ambush.', AdventureBook::planned($english));
        $this->assertSame('Ein Hinterhalt.', AdventureBook::planned($german));
    }

    public function test_the_book_is_reserved_to_the_game_master(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $this->scene('Arrivée au port', ['what_happened' => 'Secret de MJ.']);

        foreach ([[$player, 403], [User::factory()->create(), 404]] as [$user, $status]) {
            $this->actingAs($user)->get(route('book.show', $this->campaign))->assertStatus($status);
            $this->actingAs($user)->get(route('book.print', $this->campaign))->assertStatus($status);
        }

        $this->actingAs($this->gm)->get(route('book.show', $this->campaign))->assertOk()->assertSee('Secret de MJ.');
        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))->assertSee(route('book.show', $this->campaign));
    }

    public function test_the_game_master_keeps_a_written_version_and_prints_it(): void
    {
        $this->scene('Arrivée au port', ['what_happened' => 'Les héros arrivent.']);

        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->call('edit')
            ->set('narrative', "# Chronique\n\nIl pleuvait sur Brumeval.")
            ->call('saveNarrative')
            ->assertHasNoErrors()
            ->assertSee('Il pleuvait sur Brumeval.');

        $this->assertArrayNotHasKey('adventure_book', $this->campaign->fresh()->toArray());

        $this->actingAs($this->gm)->get(route('book.print', [$this->campaign, 'version' => 'redigee']))
            ->assertOk()->assertSee('Il pleuvait sur Brumeval.')->assertDontSee('Les héros arrivent.');
        $this->actingAs($this->gm)->get(route('book.print', $this->campaign))
            ->assertOk()->assertSee('Les héros arrivent.')->assertDontSee('Il pleuvait sur Brumeval.');
    }

    public function test_the_game_master_has_the_book_written_by_their_own_ai(): void
    {
        $this->scene('Arrivée au port', ['what_happened' => 'Les héros arrivent sous la pluie.']);

        // Sans clé : seulement le texte à copier, rien n'est envoyé.
        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->assertSee('Copier le texte')
            ->assertDontSee('Rédiger avec')
            ->call('writeWithAi')
            ->assertHasErrors('direct');

        Livewire::actingAs($this->gm)->test(AiKey::class)
            ->set('provider', 'openai')->set('model', 'gpt-5')->set('apiKey', 'sk-test-0123456789abcdefWXYZ')
            ->call('save')->assertHasNoErrors();

        Http::fake(['api.openai.com/*' => Http::response([
            'choices' => [['finish_reason' => 'stop', 'message' => ['content' => "# Le port\n\nSous une pluie battante, nos héros posèrent le pied à Brumeval."]]],
        ])]);

        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->assertSee('Rédiger avec ChatGPT (OpenAI)')
            ->call('writeWithAi')
            ->assertHasNoErrors()
            ->assertSet('version', 'redigee')
            ->assertSee('nos héros posèrent le pied');

        Http::assertSent(fn (Request $request) => str_contains($request['messages'][0]['content'], 'Les héros arrivent sous la pluie.'));
        $this->assertStringContainsString('nos héros posèrent le pied', $this->campaign->fresh()->adventure_book);
    }

    public function test_the_ai_part_follows_the_campaign_switch(): void
    {
        $this->campaign->forceFill(['disabled_features' => ['ai']])->save();

        Livewire::actingAs($this->gm)
            ->test(BookShow::class, ['campaign' => $this->campaign])
            ->assertOk()
            ->assertDontSee('Faire rédiger par une IA');
    }

    public function test_a_complete_backup_keeps_what_happened_but_a_template_does_not(): void
    {
        $this->scene('Arrivée au port', ['description' => 'Prévu.', 'what_happened' => 'Les héros arrivent.', 'played_as_planned' => true, 'status' => 'played']);
        $this->campaign->forceFill(['adventure_book' => 'Il pleuvait.'])->save();

        $read = function (string $path): array {
            $zip = new ZipArchive;
            $zip->open($path);

            return json_decode($zip->getFromName('campagne.json'), true);
        };
        $backup = (new CampaignExport($this->campaign, true))->write();

        $template = $read((new CampaignExport($this->campaign))->write())['scenarios'][0]['scenes'][0];
        $this->assertArrayNotHasKey('what_happened', $template);

        $complete = $read($backup);
        $this->assertSame('Les héros arrivent.', $complete['scenarios'][0]['scenes'][0]['what_happened']);
        $this->assertSame('Il pleuvait.', $complete['table']['adventure_book']);

        $restored = (new CampaignImport($this->gm))->handle($backup);
        $scene = Scene::whereHas('scenario', fn ($q) => $q->where('campaign_id', $restored->id))->sole();
        $this->assertSame('Les héros arrivent.', $scene->what_happened);
        $this->assertTrue($scene->played_as_planned);
        $this->assertSame('Il pleuvait.', $restored->fresh()->adventure_book);
    }
}
