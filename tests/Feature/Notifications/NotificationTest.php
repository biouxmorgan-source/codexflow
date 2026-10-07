<?php

namespace Tests\Feature\Notifications;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\Characters\Show;
use App\Livewire\Messages\Index as Messages;
use App\Livewire\Notifications\Index;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationTest extends TestCase
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
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Enfants de la Peur']);

        foreach (['Harvey' => 'Alex', 'Jack' => 'Sam'] as $characterName => $playerName) {
            $player = User::factory()->create(['name' => $playerName]);
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $characterName, 'entity_type_id' => EntityType::standard('character')->id]);
            $this->table[$characterName] = [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
        }
    }

    public function test_a_revealed_sheet_notifies_only_the_player_of_that_character(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Professeur Armitage', 'gm_notes' => 'Membre du culte']);

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'entity', 'entity_id' => $npc->id]);

        $this->assertSame(0, $this->gm->notifications()->count());
        $this->assertSame(0, $sam->notifications()->count());
        $notification = $alex->unreadNotifications()->sole();
        $this->assertSame('Le MJ vous a révélé la fiche « Professeur Armitage ».', $notification->data['text']);
        $this->assertStringNotContainsString('culte', json_encode($notification->data));

        $this->actingAs($alex)->get(route('notifications.index'))->assertOk()->assertSee('Professeur Armitage')->assertSee('Les Enfants de la Peur');
        $this->actingAs($sam)->get(route('notifications.index'))->assertOk()->assertDontSee('Professeur Armitage');

        $this->actingAs($alex)->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('characters.entity', [$this->campaign, $harvey, $npc]));
        $this->assertSame(0, $alex->unreadNotifications()->count());

        // Une notification ne s'ouvre que par son destinataire.
        $this->actingAs($sam)->get(route('notifications.open', $notification->id))->assertNotFound();
    }

    public function test_taking_an_item_back_is_notified(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'possession', 'title' => 'Revolver .38']);
        GiveToCharacters::revoke($harvey->grants()->sole());

        $this->assertSame(
            ['Le MJ a repris « Revolver .38 ».', 'Le MJ vous a donné « Revolver .38 ».'],
            $alex->notifications()->get()->sortByDesc(fn ($n) => $n->data['kind'] === 'revoke')->map(fn ($n) => $n->data['text'])->values()->all(),
        );
    }

    public function test_messages_notify_their_recipients_and_are_read_from_the_messages_page(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];

        Livewire::actingAs($this->gm)->test(Messages::class, ['campaign' => $this->campaign])
            ->set('body', 'Séance samedi.')
            ->call('send');

        $this->assertSame('Message du MJ au groupe : Séance samedi.', $alex->unreadNotifications()->sole()->data['text']);
        $this->assertSame(1, $sam->unreadNotifications()->count());
        $this->assertSame(0, $this->gm->notifications()->count());

        Livewire::actingAs($alex)->test(Messages::class, ['campaign' => $this->campaign])
            ->set('body', 'Je serai en retard.')
            ->call('send');

        $this->assertSame(0, $alex->unreadNotifications()->count());
        $this->assertSame('Harvey : Je serai en retard.', $this->gm->unreadNotifications()->sole()->data['text']);
        $this->assertSame(1, $sam->unreadNotifications()->count());

        // Ouvrir la conversation de Harvey marque la notification lue.
        Livewire::actingAs($this->gm)->test(Messages::class, ['campaign' => $this->campaign, 'conversation' => (string) $harvey->id]);
        $this->assertSame(0, $this->gm->unreadNotifications()->count());
    }

    public function test_a_player_intention_notifies_the_gm(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];

        Livewire::actingAs($alex)->test(Show::class, ['campaign' => $this->campaign, 'character' => $harvey])
            ->set('intentionBody', 'Fouiller la crypte')
            ->call('addIntention')
            ->assertHasNoErrors();

        $notification = $this->gm->unreadNotifications()->sole();
        $this->assertSame('Harvey : Fouiller la crypte', $notification->data['text']);
        $this->assertSame(route('sessions.live', $this->campaign), $notification->data['url']);
    }

    public function test_all_notifications_can_be_marked_read(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'information', 'title' => 'Le notaire ment']);
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'possession', 'title' => 'Lampe tempête']);

        Livewire::actingAs($alex)->test(Index::class)
            ->set('unreadOnly', true)
            ->assertSee('Le notaire ment')
            ->call('markAllRead')
            ->assertSee('Aucune notification non lue.');

        $this->assertSame(0, $alex->unreadNotifications()->count());
    }
}
