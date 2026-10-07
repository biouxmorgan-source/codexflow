<?php

namespace Tests\Feature\Messages;

use App\Actions\Messages\SendMessage;
use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Messages\Index;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MessagingTest extends TestCase
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

    /** Parcours de recette 8 : message privé lié à un PNJ. */
    public function test_a_private_message_with_an_npc_opens_its_public_zone_to_that_player_only(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Professeur Armitage', 'summary' => 'Bibliothécaire', 'gm_notes' => 'Connaît le rituel',
        ]);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('open', (string) $harvey->id)
            ->set('body', 'Il vous attend à la bibliothèque.')
            ->set('refKind', 'entity')
            ->set('refEntityId', $npc->id)
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Il vous attend à la bibliothèque.');

        $this->actingAs($alex)->get(route('messages.index', $this->campaign))
            ->assertOk()
            ->assertSee('Il vous attend à la bibliothèque.')
            ->assertSee(route('characters.entity', [$this->campaign, $harvey, $npc]), false);

        $this->actingAs($alex)->get(route('characters.entity', [$this->campaign, $harvey, $npc]))
            ->assertOk()
            ->assertSee('Bibliothécaire')
            ->assertDontSee('Connaît le rituel');

        $this->actingAs($sam)->get(route('messages.index', $this->campaign))
            ->assertOk()
            ->assertDontSee('Il vous attend à la bibliothèque.')
            ->assertDontSee('Professeur Armitage');
    }

    public function test_group_messages_reach_everyone_and_replies_stay_private(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('body', 'Séance samedi à 20 h.')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertNull(Message::sole()->player_character_id);

        foreach ([$alex, $sam] as $player) {
            $this->assertSame(1, Message::unreadCount($player, $this->campaign));
            $this->actingAs($player)->get(route('messages.index', $this->campaign))->assertSee('Séance samedi à 20 h.')->assertSee('à tout le groupe');
            $this->assertSame(0, Message::unreadCount($player, $this->campaign));
        }

        Livewire::actingAs($alex)->test(Index::class, ['campaign' => $this->campaign])
            ->set('body', 'Je serai en retard.')
            ->call('send')
            ->assertHasNoErrors();

        $reply = Message::latest('id')->first();
        $this->assertSame($harvey->id, $reply->player_character_id);
        $this->assertSame(1, Message::unreadCount($this->gm, $this->campaign));
        $this->assertSame(0, Message::unreadCount($sam, $this->campaign));

        $this->actingAs($sam)->get(route('messages.index', $this->campaign))->assertDontSee('Je serai en retard.');

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign, 'conversation' => (string) $harvey->id])
            ->assertSee('Je serai en retard.');
        $this->assertSame(0, Message::unreadCount($this->gm, $this->campaign));
    }

    public function test_the_gm_can_write_to_chosen_characters_only(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('body', 'Vous entendez un bruit.')
            ->set('selected', [(string) $harvey->id])
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Message privé envoyé à 1 personnage.');

        $this->assertSame($harvey->id, Message::sole()->player_character_id);
        $this->assertSame(1, Message::unreadCount($alex, $this->campaign));
        $this->assertSame(0, Message::unreadCount($sam, $this->campaign));
    }

    public function test_a_gm_zone_rule_cannot_be_attached(): void
    {
        $secret = new Rule(['title' => 'Rituel interdit', 'zone' => Zone::GameMaster]);
        $secret->owner()->associate($this->gm);
        $secret->campaign()->associate($this->campaign);
        $secret->save();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('body', 'Lisez ceci.')
            ->set('refKind', 'rule')
            ->set('refRuleId', (string) $secret->id)
            ->call('send')
            ->assertHasErrors('refRuleId');

        $this->assertSame(0, Message::count());
    }

    public function test_a_player_writes_only_to_the_gm_from_their_own_character(): void
    {
        [$alex] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];
        $send = app(SendMessage::class);

        $this->expectException(HttpException::class);
        $send->handle($this->campaign, $alex, [$jack->id], 'Je me fais passer pour Jack.');
    }

    public function test_a_player_cannot_attach_anything(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create();

        $this->expectException(HttpException::class);
        app(SendMessage::class)->handle($this->campaign, $alex, [$harvey->id], 'Regardez.', ['kind' => 'entity', 'id' => $npc->id]);
    }

    public function test_outsiders_cannot_read_the_messages(): void
    {
        $this->actingAs(User::factory()->create())->get(route('messages.index', $this->campaign))->assertForbidden();
    }
}
