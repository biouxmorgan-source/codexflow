<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\Characters\Show;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExchangeTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, array{0: User, 1: PlayerCharacter}> */
    private array $table = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        // Échanges autorisés d'office : la validation par le MJ est testée dans ExchangeApprovalTest.
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['exchanges_need_approval' => false]);

        foreach (['Harvey' => 'Alex', 'Jack' => 'Sam'] as $characterName => $playerName) {
            $player = User::factory()->create(['name' => $playerName]);
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $characterName, 'entity_type_id' => EntityType::standard('character')->id]);
            $this->table[$characterName] = [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
        }
    }

    private function give(PlayerCharacter $character, array $data): void
    {
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$character->id], $data);
    }

    private function sheet(User $player, PlayerCharacter $character)
    {
        return Livewire::actingAs($player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character]);
    }

    public function test_a_player_gives_part_of_a_stack_then_the_rest(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Cartouches', 'quantity' => 6]);
        $ammo = $harvey->grants()->sole();

        $this->sheet($alex, $harvey)
            ->call('startExchange', $ammo->id)
            ->assertSet('exchangeTo', (string) $jack->id)
            ->set('exchangeQuantity', 4)
            ->call('exchange')
            ->assertHasNoErrors()
            ->assertSee('Donné à Jack.');

        $this->assertSame(2, $ammo->fresh()->quantity);
        $this->assertSame(4, $jack->grants()->sole()->quantity);

        $this->sheet($alex, $harvey)->call('startExchange', $ammo->id)->set('exchangeQuantity', 3)->call('exchange')->assertHasErrors('exchangeQuantity');
        $this->sheet($alex, $harvey)->call('startExchange', $ammo->id)->call('exchange')->assertHasNoErrors();

        $this->assertSame(0, $harvey->grants()->count());
        $this->assertSame(6, $jack->grants()->sole()->quantity);

        // Le joueur de Jack est prévenu, le MJ aussi ; l'échange est au journal des deux personnages.
        $this->assertContains('Harvey vous a donné « Cartouches ×4 ».', $sam->notifications->pluck('data.text'));
        $this->assertContains('Harvey a donné « Cartouches ×4 » à Jack.', $this->gm->notifications->pluck('data.text'));
        $this->assertSame(0, $alex->notifications()->where('data->kind', 'grant')->where('data->text', 'like', 'Harvey%')->count());
        $this->sheet($sam, $jack)->assertSee('Objet transmis')->assertSee('Cartouches ×4 de Harvey à Jack');
        $this->sheet($alex, $harvey)->assertSee('Cartouches ×2 de Harvey à Jack');
        $this->assertSame('a donné', ActivityLog::latest('id')->first()->verb());
    }

    public function test_a_single_object_changes_hands_and_merges_with_the_same_object(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Lampe torche']);
        $this->give($jack, ['kind' => 'possession', 'title' => 'lampe torche']);

        $this->sheet($alex, $harvey)->call('startExchange', $harvey->grants()->sole()->id)->call('exchange')->assertHasNoErrors();

        $this->assertSame(0, $harvey->grants()->count());
        $this->assertSame(2, $jack->grants()->sole()->quantity);
    }

    public function test_knowledge_is_shared_and_kept_by_the_giver(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Professeur Armitage', 'gm_notes' => 'Membre du culte']);
        $this->give($harvey, ['kind' => 'entity', 'entity_id' => $npc->id]);
        $this->give($harvey, ['kind' => 'information', 'title' => 'Le code du coffre', 'body' => '4-8-15']);

        foreach ($harvey->grants as $grant) {
            $this->sheet($alex, $harvey)->call('startExchange', $grant->id)->set('exchangeTo', (string) $jack->id)->call('exchange')->assertHasNoErrors()->assertSee('Transmis à Jack.');
        }

        $this->assertSame(2, $harvey->grants()->count());
        $this->assertTrue($jack->fresh()->knows($npc));
        $this->assertSame('4-8-15', $jack->grants()->where('kind', 'information')->sole()->body);
        $this->assertContains('Harvey vous a montré la fiche « Professeur Armitage ».', $sam->notifications->pluck('data.text'));
        $this->actingAs($sam)->get(route('characters.entity', [$this->campaign, $jack, $npc]))->assertOk()->assertDontSee('Membre du culte');

        // Déjà connu : rien n'est dupliqué.
        $this->sheet($alex, $harvey)->call('startExchange', $harvey->grants()->where('kind', 'entity')->sole()->id)->set('exchangeTo', (string) $jack->id)->call('exchange')->assertHasErrors('exchange');
        $this->assertSame(2, $jack->grants()->count());
    }

    public function test_only_the_player_of_an_unlocked_sheet_gives_and_only_to_a_companion(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Lampe torche']);
        $lamp = $harvey->grants()->sole();

        // Le joueur de Jack ne peut pas prendre l'objet de Harvey.
        $this->sheet($sam, $jack)->call('startExchange', $lamp->id)->assertNotFound();

        // Un personnage d'une autre campagne n'est pas un destinataire.
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $stranger = $other->playerCharacters()->create(['entity_id' => Entity::factory()->for($this->gm, 'owner')->for($other)->create()->id, 'user_id' => $sam->id]);
        $this->sheet($alex, $harvey)->call('startExchange', $lamp->id)->set('exchangeTo', (string) $stranger->id)->call('exchange')->assertHasErrors('exchangeTo');

        $harvey->update(['locked' => true]);
        $this->sheet($alex, $harvey->fresh())->assertDontSee('Donner')->call('startExchange', $lamp->id)->assertForbidden();

        $this->assertSame($harvey->id, $lamp->fresh()->player_character_id);
    }
}
