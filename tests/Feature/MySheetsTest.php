<?php

namespace Tests\Feature;

use App\Actions\Account\DeleteAccount;
use App\Enums\CampaignRole;
use App\Livewire\MySheets\Index;
use App\Livewire\MySheets\Show;
use App\Models\Campaign;
use App\Models\PersonalCharacter;
use App\Models\User;
use App\Support\Plans\StorageUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Mes feuilles de personnage : les personnages joués hors de SagaWyn, rangés dans le compte
 * et visibles de leur seul propriétaire.
 */
class MySheetsTest extends TestCase
{
    use RefreshDatabase;

    private User $me;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        User::factory()->create(); // le premier compte est l'administrateur
        $this->me = User::factory()->create();
    }

    private function character(array $attributes = []): PersonalCharacter
    {
        return $this->me->personalCharacters()->create(['name' => 'Aldric', 'game' => 'Warhammer', ...$attributes]);
    }

    public function test_the_menu_leads_to_my_sheets_and_a_new_character_opens_its_sheet(): void
    {
        $this->actingAs($this->me)->get(route('campaigns.index'))->assertSee(route('my-sheets.index'));

        Livewire::actingAs($this->me)->test(Index::class)
            ->set('name', '  Aldric  ')
            ->set('newGame', 'Warhammer 2e')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('my-sheets.show', PersonalCharacter::firstOrFail()));

        $this->assertDatabaseHas('personal_characters', ['user_id' => $this->me->id, 'name' => 'Aldric', 'game' => 'Warhammer 2e']);
    }

    public function test_i_only_see_my_own_characters_and_can_filter_by_game(): void
    {
        $this->character();
        $this->character(['name' => 'Agathe', 'game' => 'Cthulhu']);
        User::factory()->create()->personalCharacters()->create(['name' => 'Personnage d’un autre']);

        Livewire::actingAs($this->me)->test(Index::class)
            ->assertSee('Aldric')->assertSee('Agathe')->assertDontSee('Personnage d’un autre')
            ->set('game', 'cthulhu')
            ->assertSee('Agathe')->assertDontSee('Aldric');
    }

    public function test_nobody_else_opens_my_sheet_even_a_game_master_of_my_campaigns(): void
    {
        $character = $this->character();
        $gm = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $campaign->members()->attach($this->me, ['role' => CampaignRole::Player->value]);

        foreach ([User::factory()->create(), $gm] as $other) {
            $this->actingAs($other)->get(route('my-sheets.show', $character))->assertNotFound();
            $this->actingAs($other)->get(route('my-sheets.sheet', $character))->assertNotFound();
            $this->actingAs($other)->get(route('my-sheets.portrait', $character))->assertNotFound();
        }

        $this->actingAs($this->me)->get(route('my-sheets.show', $character))->assertOk()->assertSee('Aldric');
    }

    public function test_free_fields_are_saved_and_counters_move_with_minus_and_plus(): void
    {
        $character = $this->character();

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->set('tableName', 'Chez Paul')
            ->set('notes', 'Doit 3 couronnes au forgeron.')
            ->call('addRow', 'text')
            ->set('rows.0.label', 'Carrière')
            ->set('rows.0.value', 'Ratier')
            ->call('addRow', 'counter')
            ->set('rows.1.label', 'Blessures')
            ->set('rows.1.value', '11')
            ->set('rows.1.max', '11')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editing', false)
            ->call('adjust', 1, -4)
            ->assertSee('7 / 11')
            ->call('adjust', 1, -20)
            ->assertSee('0 / 11');

        $character->refresh();
        $this->assertSame('Chez Paul', $character->table_name);
        $this->assertEquals([
            ['type' => 'text', 'label' => 'Carrière', 'value' => 'Ratier'],
            ['type' => 'counter', 'label' => 'Blessures', 'value' => 0, 'max' => 11],
        ], $character->fields);
    }

    public function test_a_counter_must_be_a_whole_number_and_text_rows_cannot_be_adjusted(): void
    {
        $character = $this->character(['fields' => [['type' => 'text', 'label' => 'Carrière', 'value' => 'Ratier']]]);

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('addRow', 'counter')
            ->set('rows.1.label', 'PV')
            ->set('rows.1.value', 'beaucoup')
            ->call('save')
            ->assertHasErrors('rows.1.value')
            ->call('cancel')
            ->call('adjust', 0, 1)
            ->assertNotFound();
    }

    public function test_the_pdf_sheet_and_portrait_are_stored_privately_and_count_in_my_storage(): void
    {
        $character = $this->character();

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->set('sheet', UploadedFile::fake()->create('aldric.pdf', 200, 'application/pdf'))
            ->assertHasNoErrors()
            ->set('portrait', UploadedFile::fake()->image('aldric.jpg'))
            ->assertHasNoErrors();

        $character->refresh();
        Storage::disk('local')->assertExists([$character->sheet_path, $character->portrait_path]);
        $this->assertSame('aldric.pdf', $character->sheet_name);
        $this->assertSame($character->sheet_size + $character->portrait_size, StorageUsage::bytes($this->me, fresh: true));

        $this->actingAs($this->me)->get(route('my-sheets.sheet', $character))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($this->me)->get(route('my-sheets.portrait', $character))->assertOk();
    }

    public function test_a_file_that_is_not_a_pdf_is_refused(): void
    {
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $this->character()])
            ->set('sheet', UploadedFile::fake()->create('virus.exe', 10))
            ->assertHasErrors('sheet');
    }

    public function test_deleting_a_character_or_my_account_removes_its_files(): void
    {
        $character = $this->character();
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->set('sheet', UploadedFile::fake()->create('aldric.pdf', 10, 'application/pdf'));
        $path = $character->refresh()->sheet_path;

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('delete')
            ->assertRedirect(route('my-sheets.index'));

        $this->assertModelMissing($character);
        Storage::disk('local')->assertMissing($path);

        $other = $this->character(['name' => 'Agathe', 'sheet_path' => 'personal-characters/agathe.pdf']);
        Storage::disk('local')->put('personal-characters/agathe.pdf', 'pdf');
        app(DeleteAccount::class)->handle($this->me);

        $this->assertModelMissing($other);
        Storage::disk('local')->assertMissing('personal-characters/agathe.pdf');
    }

    public function test_my_data_export_includes_my_sheets(): void
    {
        $this->character(['notes' => 'Doit 3 couronnes.']);

        $this->actingAs($this->me)->get(route('account.data'))
            ->assertOk()
            ->assertSee('"personal_characters"', false)
            ->assertSee('Doit 3 couronnes.');
    }
}
