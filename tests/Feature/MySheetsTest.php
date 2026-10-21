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
use Illuminate\Support\Facades\DB;
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

    public function test_free_fields_are_saved_in_blocks_and_counters_move_with_minus_and_plus(): void
    {
        $character = $this->character();

        $component = Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->assertCount('blocks', 1)
            ->set('tableName', 'Chez Paul')
            ->set('blocks.0.title', 'Profil')
            ->call('addRow', 0, 'text')
            ->set('blocks.0.rows.0.label', 'Carrière')
            ->set('blocks.0.rows.0.value', 'Ratier')
            ->set('blocks.0.rows.0.note', 'Ancienne carrière : mendiant')
            ->call('addBlock')
            ->set('blocks.1.title', 'Santé')
            ->call('addRow', 1, 'counter')
            ->set('blocks.1.rows.0.label', 'Blessures')
            ->set('blocks.1.rows.0.value', '11')
            ->set('blocks.1.rows.0.max', '11')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editing', false)
            ->assertSee('Ancienne carrière : mendiant');

        $health = $character->refresh()->blockList()[1]['id'];

        $component->call('adjust', $health, 0, -4)
            ->assertSee('7 / 11')
            ->call('adjust', $health, 0, -20)
            ->assertSee('0 / 11');

        $character->refresh();
        $this->assertSame('Chez Paul', $character->table_name);
        $this->assertSame(['Profil', 'Santé'], array_column($character->blockList(), 'title'));
        $this->assertEquals(['type' => 'counter', 'label' => 'Blessures', 'value' => 0, 'max' => 11, 'note' => '', 'checkbox' => false, 'checked' => false], $character->blockList()[1]['rows'][0]);
    }

    public function test_an_entry_with_a_checkbox_is_ticked_during_play(): void
    {
        $character = $this->character(['blocks' => [['id' => 'skills', 'title' => 'Compétences', 'column' => 'left', 'rows' => [
            ['type' => 'text', 'label' => 'Bibliothèque', 'value' => '60 %', 'checkbox' => true],
            ['type' => 'text', 'label' => 'Discrétion', 'value' => '20 %'],
        ]]]]);

        $component = Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->assertSeeHtml('aria-label="Cocher « Bibliothèque »"')
            ->call('toggleCheck', 'skills', 0);

        $this->assertTrue($character->refresh()->blockList()[0]['rows'][0]['checked']);

        $component->call('toggleCheck', 'skills', 0);
        $this->assertFalse($character->refresh()->blockList()[0]['rows'][0]['checked']);

        // Sans case, rien à cocher.
        $component->call('toggleCheck', 'skills', 1)->assertNotFound();
    }

    public function test_blocks_are_moved_between_columns_and_rows_between_blocks(): void
    {
        $character = $this->character(['blocks' => [
            ['id' => 'stats', 'title' => 'Caractéristiques', 'column' => 'left', 'rows' => [['type' => 'text', 'label' => 'Force', 'value' => '40']]],
            ['id' => 'gear', 'title' => 'Équipement', 'column' => 'left', 'rows' => []],
            ['id' => 'spells', 'title' => 'Sorts', 'column' => 'left', 'rows' => []],
        ]]);

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('toggleArranging')
            ->assertSee('Glissez un bloc')
            ->call('saveLayout', ['left' => ['spells', 'stats'], 'right' => ['gear', 'inconnu']]);

        $blocks = $character->refresh()->blockList();
        $this->assertSame(['spells', 'stats', 'gear'], array_column($blocks, 'id'));
        $this->assertSame(['left', 'left', 'right'], array_column($blocks, 'column'));

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('moveRowToBlock', 1, 0, 2)
            ->call('save')
            ->assertHasNoErrors();

        $blocks = $character->refresh()->blockList();
        $this->assertSame([], $blocks[1]['rows']);
        $this->assertSame('Force', $blocks[2]['rows'][0]['label']);
        $this->assertSame('right', $blocks[2]['column']);
    }

    public function test_a_counter_must_be_a_whole_number_and_text_rows_cannot_be_adjusted(): void
    {
        $character = $this->character(['blocks' => [['id' => 'profil', 'title' => '', 'column' => 'left', 'rows' => [['type' => 'text', 'label' => 'Carrière', 'value' => 'Ratier']]]]]);

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('addRow', 0, 'counter')
            ->set('blocks.0.rows.1.label', 'PV')
            ->set('blocks.0.rows.1.value', 'beaucoup')
            ->call('save')
            ->assertHasErrors('blocks.0.rows.1.value')
            ->call('cancel')
            ->call('adjust', 'profil', 0, 1)
            ->assertNotFound();
    }

    public function test_existing_fields_move_into_a_first_block(): void
    {
        $migration = require database_path('migrations/2026_11_08_100000_add_blocks_to_personal_characters.php');
        $migration->down();

        $id = DB::table('personal_characters')->insertGetId([
            'user_id' => $this->me->id, 'name' => 'Aldric', 'created_at' => now(), 'updated_at' => now(),
            'fields' => json_encode([['type' => 'counter', 'label' => 'Blessures', 'value' => 7, 'max' => 11]]),
        ]);

        $migration->up();

        $blocks = PersonalCharacter::findOrFail($id)->blockList();
        $this->assertCount(1, $blocks);
        $this->assertSame('Blessures', $blocks[0]['rows'][0]['label']);
        $this->assertSame(7, $blocks[0]['rows'][0]['value']);
    }

    public function test_several_note_blocks_are_placed_on_three_columns(): void
    {
        $character = $this->character();

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('removeBlock', 0)
            ->call('addBlock', 'note')
            ->set('blocks.0.text', 'Doit 3 couronnes au forgeron.')
            ->call('addBlock', 'note')
            ->set('blocks.1.title', 'Rumeurs')
            ->set('blocks.1.text', 'Un skaven rôde sous le marché.')
            ->call('addRow', 1, 'text')
            ->assertStatus(422);

        $component = Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('removeBlock', 0)
            ->call('addBlock', 'note')
            ->set('blocks.0.text', 'Doit 3 couronnes au forgeron.')
            ->call('addBlock', 'note')
            ->set('blocks.1.title', 'Rumeurs')
            ->set('blocks.1.text', 'Un skaven rôde sous le marché.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Doit 3 couronnes au forgeron.')
            ->assertSee('Un skaven rôde sous le marché.');

        [$debts, $rumours] = $character->refresh()->blockList();
        $this->assertSame(['note', 'Notes'], [$debts['kind'], $debts['title']]);
        $this->assertSame(1, $character->usedColumns());

        $component->call('saveLayout', ['left' => [$debts['id']], 'middle' => [], 'right' => [$rumours['id']]]);

        $character->refresh();
        $this->assertSame(['left', 'right'], array_column($character->blockList(), 'column'));
        $this->assertSame(3, $character->usedColumns());
    }

    public function test_a_sheet_full_of_notes_still_takes_more_and_the_limit_is_explained(): void
    {
        $note = fn (int $i) => ['id' => 'n'.$i, 'kind' => 'note', 'title' => 'Note '.$i, 'column' => 'left', 'text' => 'Texte', 'rows' => []];
        $character = $this->character(['blocks' => array_map($note, range(1, 30))]);

        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('addBlock', 'note')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasNoErrors();
        $this->assertCount(31, $character->refresh()->blockList());

        $character->update(['blocks' => array_map($note, range(1, PersonalCharacter::MAX_BLOCKS))]);
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('addBlock', 'note')
            ->assertOk()
            ->assertHasErrors('blocks')
            ->assertSee('Une feuille garde au plus 200 blocs.');
    }

    public function test_named_tabs_each_hold_their_own_blocks(): void
    {
        $character = $this->character(['blocks' => [['id' => 'stats', 'title' => 'Profil du héros', 'column' => 'left', 'rows' => []]]]);

        // Une feuille d'avant les onglets garde tous ses blocs dans un premier onglet, sans barre d'onglets.
        $this->assertSame([['id' => PersonalCharacter::FIRST_TAB, 'name' => '']], $character->tabList());
        $this->assertSame(PersonalCharacter::FIRST_TAB, $character->blockList()[0]['tab']);

        $component = Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->assertDontSee('Onglets de la feuille')
            ->call('edit')
            ->set('tabs.0.name', 'Personnage')
            ->call('addTab')
            ->set('tabs.1.name', 'Sorts')
            ->call('addBlock', 'note')
            ->set('blocks.1.title', 'Grimoire')
            ->set('blocks.1.text', 'Boule de feu, Bouclier.')
            ->call('addTab')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Onglets de la feuille')
            ->assertSee('Onglet vide.')
            ->assertDontSee('Profil du héros');

        $character->refresh();
        [$personnage, $sorts, $third] = $character->tabList();
        $this->assertSame(['Personnage', 'Sorts', ''], array_column($character->tabList(), 'name'));
        $this->assertSame([PersonalCharacter::FIRST_TAB, $sorts['id']], array_column($character->blockList(), 'tab'));
        $this->assertSame('Onglet 3', PersonalCharacter::tabLabel($third, 2));

        $component->call('showTab', $sorts['id'])
            ->assertSee('Boule de feu, Bouclier.')
            ->call('showTab', $personnage['id'])
            ->assertSee('Profil du héros')
            ->assertDontSee('Boule de feu, Bouclier.')
            ->call('showTab', 'ailleurs')
            ->assertStatus(404);

        // Organiser un onglet ne touche pas aux blocs des autres.
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('showTab', $sorts['id'])
            ->call('saveLayout', ['left' => ['stats'], 'middle' => [], 'right' => [$character->blockList()[1]['id']]]);
        $this->assertEquals(['stats' => 'left', $character->blockList()[1]['id'] => 'right'], array_column($character->refresh()->blockList(), 'column', 'id'));

        // Ranger les onglets, puis en retirer un : ses blocs passent dans le premier onglet.
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $character])
            ->call('edit')
            ->call('moveTab', 1, -1)
            ->call('removeTab', 2)
            ->call('removeTab', 1)
            ->call('save')
            ->assertHasNoErrors();

        $character->refresh();
        $this->assertSame(['Sorts'], array_column($character->tabList(), 'name'));
        $this->assertSame([$sorts['id'], $sorts['id']], array_column($character->blockList(), 'tab'));
    }

    public function test_the_former_single_note_becomes_a_note_block(): void
    {
        $migration = require database_path('migrations/2026_11_10_100000_move_personal_notes_into_blocks.php');
        $character = $this->character(['blocks' => [['id' => 'stats', 'title' => 'Caractéristiques', 'column' => 'left', 'rows' => []]]]);
        $migration->down();
        DB::table('personal_characters')->where('id', $character->id)->update(['notes' => 'Doit 3 couronnes.']);

        $migration->up();

        $blocks = $character->refresh()->blockList();
        $this->assertSame(['fields', 'note'], array_column($blocks, 'kind'));
        $this->assertSame('Doit 3 couronnes.', $blocks[1]['text']);
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
        $this->character(['blocks' => [['id' => 'dettes', 'kind' => 'note', 'title' => 'Dettes', 'column' => 'left', 'text' => 'Doit 3 couronnes.']]]);

        $this->actingAs($this->me)->get(route('account.data'))
            ->assertOk()
            ->assertSee('"personal_characters"', false)
            ->assertSee('Doit 3 couronnes.');
    }
}
