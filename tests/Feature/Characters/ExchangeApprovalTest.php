<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\Characters\Index;
use App\Livewire\Characters\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\ExchangeRequest;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** §6.11 : par défaut, le MJ valide les échanges entre joueurs ; il peut les autoriser d'office. */
class ExchangeApprovalTest extends TestCase
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
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();

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

    private function sheet(User $user, PlayerCharacter $character)
    {
        return Livewire::actingAs($user)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character]);
    }

    public function test_an_exchange_waits_for_the_game_master_who_accepts_it(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Cartouches', 'quantity' => 6]);
        $ammo = $harvey->grants()->sole();

        $this->assertTrue($this->campaign->fresh()->exchanges_need_approval);
        $this->sheet($alex, $harvey)
            ->call('startExchange', $ammo->id)
            ->assertSee('Le MJ doit valider l’échange.')
            ->set('exchangeQuantity', 4)
            ->call('exchange')
            ->assertHasNoErrors()
            ->assertSee('Proposé à Jack : le MJ doit valider l’échange.')
            ->assertSee('en attente du MJ')
            ->assertSee('Annuler l’échange')
            ->assertDontSee('wire:click="startExchange('.$ammo->id.')"', false);

        // Rien n'a bougé ; le MJ est prévenu et voit la demande.
        $this->assertSame(6, $ammo->fresh()->quantity);
        $this->assertSame(0, $jack->grants()->count());
        $this->assertContains('Harvey propose de transmettre « Cartouches ×4 » à Jack : à valider.', $this->gm->notifications->pluck('data.text'));
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->assertSee('1 échange à valider');

        // Une seule demande à la fois pour un même élément.
        $this->sheet($alex, $harvey)->call('startExchange', $ammo->id)->call('exchange')->assertHasErrors('exchange');

        // Ni le joueur ni un autre ne peuvent répondre à la place du MJ.
        $this->sheet($alex, $harvey)->call('answerExchange', $ammo->id, true)->assertForbidden();

        $this->sheet($this->gm, $harvey)->assertSee('Accepter l’échange')->call('answerExchange', $ammo->id, true)->assertHasNoErrors();

        $this->assertSame(2, $ammo->fresh()->quantity);
        $this->assertSame(4, $jack->grants()->sole()->quantity);
        $this->assertSame(0, ExchangeRequest::count());
        $this->assertContains('Le MJ a accepté l’échange : « Cartouches ×4 » à Jack.', $alex->fresh()->notifications->pluck('data.text'));
        $this->assertContains('Harvey vous a donné « Cartouches ×4 ».', $sam->fresh()->notifications->pluck('data.text'));
        $this->sheet($sam, $jack)->assertSee('Cartouches ×4 de Harvey à Jack');
    }

    public function test_the_game_master_refuses_and_the_player_can_cancel(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'information', 'title' => 'Le code du coffre', 'body' => '4-8-15']);
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Lampe torche']);
        [$code, $lamp] = [$harvey->grants()->where('kind', 'information')->sole(), $harvey->grants()->where('kind', 'possession')->sole()];

        $this->sheet($alex, $harvey)->call('startExchange', $code->id)->set('exchangeTo', (string) $jack->id)->call('exchange')->assertHasNoErrors();
        $this->sheet($this->gm, $harvey)->call('answerExchange', $code->id, false);

        $this->assertSame(0, $jack->grants()->count());
        $this->assertSame(0, ExchangeRequest::count());
        $this->assertContains('Le MJ a refusé l’échange : « Le code du coffre » à Jack.', $alex->fresh()->notifications->pluck('data.text'));

        $this->sheet($alex, $harvey)->call('startExchange', $lamp->id)->call('exchange')->call('cancelExchange', $lamp->id);
        $this->assertSame(0, ExchangeRequest::count());
        $this->assertSame($harvey->id, $lamp->fresh()->player_character_id);
    }

    public function test_the_game_master_can_allow_exchanges_without_approval(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam, $jack] = $this->table['Jack'];
        $this->give($harvey, ['kind' => 'possession', 'title' => 'Lampe torche']);

        Livewire::actingAs($sam)->test(Index::class, ['campaign' => $this->campaign])->assertForbidden();
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee('Valider les échanges entre joueurs')
            ->call('toggleExchangeApproval');
        $this->assertFalse($this->campaign->fresh()->exchanges_need_approval);

        $this->sheet($alex, $harvey)->call('startExchange', $harvey->grants()->sole()->id)->call('exchange')->assertSee('Donné à Jack.');
        $this->assertSame(1, $jack->grants()->count());
        $this->assertSame(0, ExchangeRequest::count());
    }
}
