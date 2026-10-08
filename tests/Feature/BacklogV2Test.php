<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Actions\Duplication\DuplicateEntity;
use App\Actions\Duplication\DuplicateScenario;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Entities\Form as EntityForm;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Évolutions retenues après la recette v0.25.0 (backlog de la console d'administration).
 */
class BacklogV2Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private Entity $morel;

    private Entity $manoir;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->morel = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel']);
        $this->manoir = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Manoir Morgause']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    public function test_long_text_fields_use_the_rich_editor_and_link_only_known_sheets_for_the_player(): void
    {
        $past = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Passé', 'type' => FieldType::LongText, 'zone' => Zone::Public, 'position' => 1, 'player_editable' => true]);
        $text = "**Ancien marin**, doit tout à [[Morel|{$this->morel->id}]] et craint [[Manoir Morgause|{$this->manoir->id}]].";

        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $this->harvey->entity])
            ->assertSeeHtml("x-data=\"richEditor('fields.{$past->id}')\"");

        $this->harvey->entity->setFieldValues([$past->id => $text]);
        $this->harvey->entity->save();
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $this->morel->id]);

        // Le joueur : mise en forme, lien vers la fiche connue, le manoir reste du texte.
        $this->actingAs($this->alex)->get(route('characters.show', [$this->campaign, $this->harvey]))
            ->assertSee('<strong>Ancien marin</strong>', false)
            ->assertSee(route('characters.entity', [$this->campaign, $this->harvey, $this->morel]))
            ->assertSee('Manoir Morgause')
            ->assertDontSee(route('entities.show', [$this->campaign, $this->manoir]));

        // Le MJ : tous les liens.
        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, $this->harvey->entity]))
            ->assertSee(route('entities.show', [$this->campaign, $this->manoir]));
    }

    public function test_rule_gm_notes_use_the_rich_editor_and_links(): void
    {
        $rule = new Rule(['title' => 'Peur', 'gm_notes' => "Voir [[Morel|{$this->morel->id}]]."]);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();

        $this->actingAs($this->gm)->get(route('rules.edit', [$this->campaign, $rule]))->assertSee("richEditor('gmNotes')", false);
        $this->actingAs($this->gm)->get(route('rules.show', [$this->campaign, $rule]))->assertSee(route('entities.show', [$this->campaign, $this->morel]));
    }

    public function test_the_session_quick_note_suggests_sheets(): void
    {
        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);

        $this->actingAs($this->gm)->get(route('sessions.live', $this->campaign))
            ->assertOk()
            ->assertSee('x-data="linkInput"', false)
            ->assertSee('id="noteBody-suggestions"', false);
        $this->assertNotNull($session);
    }

    public function test_copies_are_numbered_instead_of_sharing_a_name(): void
    {
        $duplicate = app(DuplicateEntity::class);

        $first = $duplicate->handle($this->morel);
        $second = $duplicate->handle($this->morel);
        $third = $duplicate->handle($second);

        $this->assertSame(['Morel (copie)', 'Morel (copie 2)', 'Morel (copie 3)'], [$first->name, $second->name, $third->name]);

        $scenario = $this->campaign->scenarios()->create(['name' => 'Le Manoir', 'position' => 1]);
        $this->assertSame('Le Manoir (copie)', app(DuplicateScenario::class)->handle($scenario)->name);
        $this->assertSame('Le Manoir (copie 2)', app(DuplicateScenario::class)->handle($scenario)->name);
    }

    public function test_an_import_never_reuses_a_name_the_account_already_has(): void
    {
        $path = (new CampaignExport($this->campaign->fresh()))->write();

        $first = new CampaignImport($this->gm);
        $copy = $first->handle($path);

        $this->assertSame('Les Ombres (2)', $copy->name);
        $this->assertSame($this->campaign->gameSystem->name.' (2)', $copy->gameSystem->name);
        $this->assertContains('Vous aviez déjà une campagne « Les Ombres » : celle de l’archive s’appelle « Les Ombres (2) ».', $first->notices);

        $other = new CampaignImport($this->alex);
        $this->assertSame('Les Ombres', $other->handle($path)->name);
        $this->assertSame([], $other->notices);
    }
}
