<?php

namespace Tests\Feature\Dice;

use App\Livewire\Dice\Pad;
use App\Livewire\MySheets\Show;
use App\Models\DiceRoll;
use App\Models\PersonalCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Dés de « Mes feuilles » : hors campagne, le lancer est tiré par le serveur, montré sur la feuille
 * à son seul propriétaire, et rien n'est enregistré.
 */
class PersonalSheetDiceTest extends TestCase
{
    use RefreshDatabase;

    private User $me;

    private PersonalCharacter $aldric;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(); // le premier compte est l'administrateur
        $this->me = User::factory()->create();
        $this->aldric = $this->me->personalCharacters()->create(['name' => 'Aldric', 'blocks' => [[
            'id' => 'stats', 'kind' => 'fields', 'title' => 'Profil', 'rows' => [
                ['type' => 'text', 'label' => 'Capacité de combat', 'value' => '42'],
                ['type' => 'text', 'label' => 'Carrière', 'value' => 'Ratier'],
                ['type' => 'counter', 'label' => 'Blessures', 'value' => 11, 'max' => 11],
            ],
        ]]]);
    }

    public function test_numbers_on_my_sheet_carry_a_die_and_the_sheet_has_its_own_dice(): void
    {
        Livewire::actingAs($this->me)->test(Show::class, ['personalCharacter' => $this->aldric])
            ->assertSee('Lancer les dés avec Capacité de combat')->assertSee('Lancer sous 42 (seuil)')
            ->assertSee('Lancer les dés avec Blessures')->assertDontSee('Lancer les dés avec Carrière')
            ->assertSeeLivewire(Pad::class);
    }

    public function test_a_roll_from_my_sheet_is_shown_on_it_and_not_recorded(): void
    {
        $pad = Livewire::actingAs($this->me)->test(Pad::class, ['character' => $this->aldric])
            ->call('openFor')->assertSet('open', true)
            ->call('roll', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold' => 42,
                'bonus_from' => [['fixed' => 10, 'name' => 'Arme'], ['field' => 1, 'take' => 'value']]], 'Attaque')
            ->assertSee('Attaque');

        $roll = $pad->get('rolls')[0];
        // Le bonus nommé compte, une valeur de fiche de campagne n'a pas de sens ici.
        $this->assertSame(52, $roll['result']['threshold']);
        $this->assertIsBool($roll['result']['success']);
        $this->assertSame(0, DiceRoll::count());

        foreach (range(1, 12) as $i) {
            $pad->call('roll', ['count' => 2, 'sides' => 6]);
        }
        $this->assertCount(Pad::KEPT, $pad->get('rolls'));
        $pad->call('clear')->assertSet('rolls', []);
    }

    public function test_a_threshold_is_needed_and_nobody_else_rolls_on_my_sheet(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Pad::class, ['character' => $this->aldric])->assertNotFound();

        Livewire::actingAs($this->me)->test(Pad::class, ['character' => $this->aldric])
            ->call('roll', ['count' => 1, 'sides' => 100, 'reading' => 'below'])->assertHasErrors('threshold')->assertSet('rolls', []);
    }
}
