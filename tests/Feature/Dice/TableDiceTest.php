<?php

namespace Tests\Feature\Dice;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Dice\Tray;
use App\Livewire\Table\Screen;
use App\Models\Campaign;
use App\Models\DiceRoll;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\CampaignFeatures;
use App\Support\Dice\TableDice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Dés à la table : lancers libres à la vue de tous ou discrets, demandes du MJ à toute la table
 * ou à certains personnages, seuil caché, lancers enregistrés. Visibilité vérifiée côté serveur.
 */
class TableDiceTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private User $sam;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    private PlayerCharacter $june;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->sam = User::factory()->create(['name' => 'Sam']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['table_shared' => true]);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->campaign->members()->attach($this->sam, ['role' => CampaignRole::Player->value]);

        $this->harvey = $this->character('Harvey', $this->alex);
        $this->june = $this->character('June', $this->sam);
    }

    private function character(string $name, User $player): PlayerCharacter
    {
        $sheet = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => $name]);

        return $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player->id]);
    }

    private function tray(User $user)
    {
        return Livewire::actingAs($user)->test(Tray::class, ['campaign' => $this->campaign]);
    }

    public function test_a_roll_at_the_table_is_seen_by_everyone_and_on_the_screen(): void
    {
        $this->tray($this->alex)->call('toggle')->call('roll', ['count' => 2, 'sides' => 6, 'reading' => 'sum'], 'table', 'Fouille')
            ->assertSet('lastRollId', fn ($id) => $id !== null)
            ->assertSee('Fouille');

        $roll = DiceRoll::sole();
        $this->assertSame($this->harvey->id, $roll->player_character_id);
        $this->assertCount(2, $roll->result['dice']);

        $this->tray($this->sam)->call('select', 'history')->assertSee('Harvey')->assertSee('Fouille');
        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])->assertSee('Harvey')->assertSee('2d6, additionnés');

        $this->travel(TableDice::SCREEN_SECONDS + 1)->seconds();
        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])->assertDontSee('Fouille');
    }

    public function test_a_discreet_roll_is_seen_only_by_the_gm_and_the_roller(): void
    {
        $this->tray($this->alex)->call('roll', ['count' => 1, 'sides' => 20], 'gm', 'Discrétion');

        $this->tray($this->alex)->call('select', 'history')->assertSee('Discrétion');
        $this->tray($this->gm)->call('select', 'history')->assertSee('Discrétion');
        $this->tray($this->sam)->call('select', 'history')->assertDontSee('Discrétion');
        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])->assertDontSee('Discrétion');

        // Le MJ lance caché : lui seul le voit.
        $this->tray($this->gm)->call('roll', ['count' => 1, 'sides' => 100], 'gm', 'Rencontre');
        $this->tray($this->alex)->call('select', 'history')->assertDontSee('Rencontre');
        $this->assertCount(0, TableDice::visibleRolls($this->campaign, $this->sam));
    }

    public function test_the_gm_asks_one_character_and_only_that_player_is_prompted(): void
    {
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 100, 'reading' => 'below'], 'Perception', [$this->june->id], 'gm');

        $this->tray($this->alex)->assertDontSee('Le MJ demande un lancer');
        $sam = $this->tray($this->sam)->assertSee('Le MJ demande un lancer')->assertSee('Perception')->assertSee('Votre seuil');

        // Sans seuil, le joueur saisit le sien.
        $sam->call('answer')->assertHasErrors('answerThreshold');
        $sam->set('answerThreshold', '45')->set('answerBonus', '+10')->call('answer')->assertHasNoErrors()->assertSee('Fermer');

        $roll = DiceRoll::sole();
        $this->assertSame($this->june->id, $roll->player_character_id);
        $this->assertSame('gm', $roll->visibility);
        $this->assertSame(55, $roll->result['threshold']);
        $this->assertNotNull($roll->request->closed_at, 'Tout le monde a lancé : la demande se range.');

        $this->tray($this->sam)->call('dismissAnswer')->assertDontSee('Le MJ demande un lancer');
        $this->tray($this->alex)->call('select', 'history')->assertDontSee('Perception');
        $this->tray($this->gm)->call('select', 'ask')->assertSee('Perception')->assertSee('June');
    }

    public function test_a_request_to_the_whole_table_waits_for_each_character_once(): void
    {
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 20, 'reading' => 'above', 'threshold' => 12], 'Volonté', null, 'table');
        $request = $this->campaign->diceRequests()->sole();
        $this->assertEqualsCanonicalizing([$this->harvey->id, $this->june->id], $request->characterIds());

        $this->tray($this->alex)->assertSee('Volonté')->call('answer');
        $this->assertNull($request->fresh()->closed_at);
        $this->assertTrue(TableDice::pendingFor($this->alex, $this->campaign)->isEmpty());

        $this->expectException(ValidationException::class);
        TableDice::answer($request->fresh(), $this->alex, $this->harvey);
    }

    public function test_a_player_cannot_answer_for_someone_else_or_after_closing(): void
    {
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 20], 'Force', [$this->june->id], 'table');
        $request = $this->campaign->diceRequests()->sole();

        $this->tray($this->alex)->set('answerRequestId', $request->id)->set('answerCharacterId', $this->june->id)->call('answer')->assertNotFound();

        $this->tray($this->gm)->call('closeRequest', $request->id);
        $this->tray($this->sam)->assertDontSee('Le MJ demande un lancer');
        $this->assertSame(0, DiceRoll::count());
    }

    public function test_a_hidden_threshold_stays_with_the_gm(): void
    {
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 20, 'reading' => 'above', 'hide_threshold' => true], 'Piège', null, 'table')
            ->assertHasErrors('threshold');

        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 20, 'reading' => 'above', 'threshold' => 17, 'hide_threshold' => true], 'Piège', [$this->harvey->id], 'table');
        $this->tray($this->alex)->assertSee('Piège')->assertDontSee('17 ou plus')->assertSee('? ou plus')->call('answer')
            ->assertDontSee('Réussite de')->assertDontSee('Échec de');

        $roll = TableDice::present(DiceRoll::sole(), false);
        $this->assertNull($roll['result']['success']);
        $this->assertNull($roll['result']['threshold']);
        $this->assertNotNull(TableDice::present(DiceRoll::sole(), true)['result']['success']);
    }

    public function test_the_gm_saves_rolls_for_the_table(): void
    {
        $this->tray($this->gm)->call('savePreset', ['count' => 6, 'sides' => 6, 'reading' => 'count', 'threshold' => 5], 'Combat', 'table')->assertHasNoErrors();
        $this->assertSame('Combat', TableDice::presets($this->campaign->fresh())[0]['label']);

        $this->tray($this->alex)->call('toggle')->assertSee('Combat');
        $this->tray($this->alex)->call('savePreset', ['count' => 1, 'sides' => 6], 'Triche', 'table')->assertForbidden();

        $id = TableDice::presets($this->campaign->fresh())[0]['id'];
        $this->tray($this->alex)->call('removePreset', $id, 'table')->assertForbidden();
        $this->tray($this->gm)->call('removePreset', $id, 'table');
        $this->assertSame([], TableDice::presets($this->campaign->fresh()));
    }

    public function test_each_player_saves_reloads_and_updates_their_own_rolls(): void
    {
        $id = $this->tray($this->alex)->call('savePreset', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold' => 45], 'Tir', 'mine')->assertHasNoErrors()->effects['returns'][0];

        $this->assertSame('Tir', TableDice::presets($this->campaign, $this->alex)[0]['label']);
        $this->assertSame([], TableDice::presets($this->campaign), 'Un lancer personnel ne va pas à la table.');
        $this->assertSame([], TableDice::presets($this->campaign, $this->sam));
        $this->tray($this->alex)->call('toggle')->assertSee('Mes lancers')->assertSee('Tir');
        $this->tray($this->sam)->call('toggle')->assertDontSee('Tir');

        // Le personnage progresse : le même lancer, avec un seuil plus haut.
        $this->tray($this->alex)->call('savePreset', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold' => 50], 'Tir', 'mine', $id);
        $presets = TableDice::presets($this->campaign, $this->alex);
        $this->assertCount(1, $presets);
        $this->assertSame(50, $presets[0]['formula']['threshold']);

        // Un autre joueur ne touche pas aux lancers d'Alex, même avec son identifiant.
        $this->tray($this->sam)->call('removePreset', $id, 'mine');
        $this->assertCount(1, TableDice::presets($this->campaign, $this->alex));

        $this->tray($this->alex)->call('savePreset', ['count' => 1, 'sides' => 6], ' ', 'mine')->assertHasErrors('presetLabel');
        $this->tray($this->alex)->call('removePreset', $id, 'mine');
        $this->assertSame([], TableDice::presets($this->campaign, $this->alex));
    }

    public function test_dice_can_be_switched_off_and_spectators_do_not_roll(): void
    {
        CampaignFeatures::toggle($this->campaign, 'dice');

        $this->tray($this->alex)->assertDontSee('Lancer')->call('roll', ['count' => 1, 'sides' => 6])->assertForbidden();
        $this->actingAs($this->alex)->followingRedirects()->get(route('campaigns.show', $this->campaign))->assertOk()->assertDontSeeLivewire(Tray::class);

        CampaignFeatures::toggle($this->campaign, 'dice');
        $this->actingAs($this->alex)->followingRedirects()->get(route('campaigns.show', $this->campaign))->assertSeeLivewire(Tray::class);

        $watcher = User::factory()->create();
        $this->campaign->members()->attach($watcher, ['role' => CampaignRole::Spectator->value]);
        $this->tray($watcher)->assertForbidden();
    }

    public function test_a_player_cannot_roll_as_another_character(): void
    {
        $this->tray($this->alex)->set('characterId', $this->june->id)->call('roll', ['count' => 1, 'sides' => 6])->assertForbidden();
        $this->assertSame(0, DiceRoll::count());
    }

    public function test_saved_rolls_from_an_archive_are_cleaned(): void
    {
        $presets = TableDice::cleanPresets([
            ['id' => 'a', 'label' => 'Attaque', 'formula' => ['count' => 900, 'sides' => 20, 'reading' => 'keep', 'keep' => 2]],
            ['label' => '', 'formula' => []],
            'n’importe quoi',
            ['id' => 'a', 'label' => 'Doublon', 'formula' => []],
        ]);

        $this->assertCount(1, $presets);
        $this->assertSame(30, $presets[0]['formula']['count']);
        $this->assertSame([], TableDice::cleanPresets('pas une liste'));
    }

    /** @return array{0: FieldDefinition, 1: FieldDefinition, 2: FieldDefinition} Force, Esquive, secret MJ */
    private function sheet(): array
    {
        $game = $this->campaign->gameSystem;
        $strength = $game->fieldDefinitions()->create(['name' => 'Force', 'group' => 'Caractéristiques', 'type' => FieldType::Number, 'zone' => Zone::Public, 'position' => 1]);
        $dodge = $game->fieldDefinitions()->create(['name' => 'Esquive', 'group' => 'Compétences', 'type' => FieldType::Number, 'zone' => Zone::Public, 'position' => 2]);
        $secret = $game->fieldDefinitions()->create(['name' => 'Destin', 'type' => FieldType::Number, 'zone' => Zone::GameMaster, 'position' => 3]);

        $this->harvey->entity->setFieldValues([$strength->id => 47, $dodge->id => 35, $secret->id => 9]);
        $this->harvey->entity->save();
        $this->june->entity->setFieldValues([$strength->id => 62, $dodge->id => 50]);
        $this->june->entity->save();

        return [$strength, $dodge, $secret];
    }

    public function test_the_bonus_and_threshold_come_from_the_rolling_characters_sheet(): void
    {
        [$strength, $dodge, $secret] = $this->sheet();
        $formula = ['count' => 1, 'sides' => 100, 'reading' => 'below', 'modifier' => 2,
            'threshold_from' => ['field' => $dodge->id, 'take' => 'value'],
            'bonus_from' => [['field' => $strength->id, 'take' => 'tens'], ['field' => $secret->id, 'take' => 'value']]];

        $this->tray($this->alex)->assertDontSee('Esquive')->call('toggle')->assertSee('Esquive')->assertDontSee('Destin')
            ->call('roll', $formula, 'table', 'Esquive');

        $roll = DiceRoll::latest('id')->first();
        // Esquive 35, bonus +2 et dizaine de Force 4 ; un champ de la zone MJ n'est jamais lu.
        $this->assertSame(35, $roll->formula()->threshold);
        $this->assertSame(6, $roll->formula()->modifier);
        $this->assertSame(41, $roll->result['threshold']);
        $this->assertStringContainsString('35 (Esquive)', $roll->formula()->describe());
        $this->assertStringContainsString('bonus +6 (+2, dizaine de Force +4) au seuil', $roll->formula()->describe());
    }

    public function test_a_request_reads_each_characters_own_sheet(): void
    {
        [$strength, $dodge] = $this->sheet();
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold_from' => ['field' => $dodge->id, 'take' => 'half']], 'Esquive', null);

        $this->tray($this->alex)->assertSee('17 (moitié de Esquive)')->call('answer')->assertHasNoErrors();
        $this->tray($this->sam)->assertSee('25 (moitié de Esquive)')->call('answer')->assertHasNoErrors();

        $this->assertEqualsCanonicalizing([17, 25], DiceRoll::all()->map(fn (DiceRoll $roll) => $roll->formula()->threshold)->all());
    }

    public function test_without_a_value_on_the_sheet_the_threshold_is_typed(): void
    {
        [, $dodge] = $this->sheet();
        $this->june->entity->setFieldValues([$dodge->id => null]);
        $this->june->entity->save();
        $this->tray($this->gm)->call('ask', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold_from' => ['field' => $dodge->id, 'take' => 'value']], 'Esquive', [$this->june->id]);

        $this->tray($this->sam)->call('answer')->assertHasErrors('answerThreshold')
            ->set('answerThreshold', '40')->call('answer')->assertHasNoErrors();
        $this->assertSame(40, DiceRoll::latest('id')->first()->formula()->threshold);
    }

    public function test_a_gm_roll_without_a_character_cannot_read_a_sheet_threshold(): void
    {
        [, $dodge] = $this->sheet();

        $this->tray($this->gm)->call('roll', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold' => 99, 'threshold_from' => ['field' => $dodge->id, 'take' => 'value', 'got' => 99]])
            ->assertHasErrors('threshold');
        $this->assertSame(0, DiceRoll::count());
    }

    public function test_several_named_bonuses_add_up_with_sheet_values(): void
    {
        [$strength] = $this->sheet();
        $formula = ['count' => 2, 'sides' => 6, 'reading' => 'sum', 'modifier' => 1, 'bonus_from' => [
            ['fixed' => 2, 'name' => 'Arme'], ['fixed' => -1, 'name' => 'Blessure'], ['field' => $strength->id, 'take' => 'tens'],
        ]];

        $this->tray($this->alex)->call('roll', $formula, 'table', 'Coup');
        $this->tray($this->gm)->call('roll', $formula, 'table', 'Coup');

        [$alex, $gm] = DiceRoll::orderBy('id')->get()->all();
        // Alex : 1 + 2 − 1 + dizaine de Force 4 ; le MJ, sans fiche, garde les bonus nommés.
        $this->assertSame(6, $alex->formula()->modifier);
        $this->assertSame('2d6, additionnés, +6 (+1, Arme +2, Blessure -1, dizaine de Force +4)', $alex->formula()->describe());
        $this->assertSame(2, $gm->formula()->modifier);
        $this->assertSame('2d6, additionnés, +1 Arme +2 Blessure -1 + dizaine de Force', $gm->formula()->describe());
        $this->assertSame(array_sum(array_column($alex->result['dice'], 'value')) + 6, $alex->result['total']);
    }

    public function test_a_die_beside_each_number_on_the_sheet_opens_the_tray_for_that_character(): void
    {
        [$strength] = $this->sheet();
        $sheet = fn (User $user, PlayerCharacter $character) => Livewire::actingAs($user)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $character]);

        // Le joueur sur son personnage : la valeur reste liée au champ ; jamais un champ de la zone MJ.
        $sheet($this->alex, $this->harvey)->assertSee('Lancer les dés avec Force')->assertSee('Lancer sous 35 (seuil)')
            ->assertSee('\u0022own\u0022:true', false)->assertDontSee('Lancer les dés avec Destin');
        // Le MJ sur la fiche d'un joueur : la valeur est reprise telle quelle.
        $sheet($this->gm, $this->harvey)->assertSee('Lancer les dés avec Esquive')->assertSee('\u0022own\u0022:false', false);
        // Un autre joueur ne lance pas depuis la fiche de Harvey.
        $sheet($this->sam, $this->june)->assertSee('Lancer les dés avec Force');

        $this->tray($this->alex)->call('openFor', $this->harvey->id)->assertSet('open', true)->assertSet('characterId', $this->harvey->id)
            ->call('openFor', $this->june->id)->assertSet('characterId', $this->harvey->id)
            ->call('roll', ['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold_from' => ['field' => $strength->id, 'take' => 'value']], 'table', 'Force');
        $this->assertSame(47, DiceRoll::latest('id')->first()->formula()->threshold);

        CampaignFeatures::toggle($this->campaign, 'dice');
        $sheet($this->alex, $this->harvey)->assertDontSee('Lancer les dés avec Force');
    }
}
