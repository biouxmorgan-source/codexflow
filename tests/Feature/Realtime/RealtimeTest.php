<?php

namespace Tests\Feature\Realtime;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Events\CharacterChanged;
use App\Events\UserActivity;
use App\Livewire\HeaderBadges;
use App\Livewire\Messages\Index as Messages;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private User $sam;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->alex = User::factory()->create();
        $this->sam = User::factory()->create();

        foreach ([$this->alex, $this->sam] as $player) {
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        }

        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $this->alex->id]);
    }

    private function useReverb(int $port = 8080): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => $port,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);

        // Les canaux ont été déclarés au démarrage sur le pilote par défaut des tests.
        require base_path('routes/channels.php');
    }

    private function authorizeChannel(User $user, string $channel): int
    {
        return $this->actingAs($user)
            ->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => 'private-'.$channel])
            ->status();
    }

    public function test_private_channels_follow_the_visibility_rules(): void
    {
        $this->useReverb();

        $this->assertSame(200, $this->authorizeChannel($this->alex, 'users.'.$this->alex->id));
        $this->assertSame(403, $this->authorizeChannel($this->sam, 'users.'.$this->alex->id));

        $this->assertSame(200, $this->authorizeChannel($this->alex, 'characters.'.$this->harvey->id));
        $this->assertSame(200, $this->authorizeChannel($this->gm, 'characters.'.$this->harvey->id));
        $this->assertSame(403, $this->authorizeChannel($this->sam, 'characters.'.$this->harvey->id));
    }

    public function test_recipients_and_open_sheets_are_told_to_refresh(): void
    {
        Event::fake([UserActivity::class, CharacterChanged::class]);

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Lampe']);

        Event::assertDispatched(UserActivity::class, fn (UserActivity $event) => $event->userId === $this->alex->id && $event->broadcastWith() === ['kind' => 'grant', 'campaign_id' => $this->campaign->id]);
        Event::assertNotDispatched(UserActivity::class, fn (UserActivity $event) => $event->userId === $this->sam->id);

        $this->harvey->update(['locked' => true]);
        Event::assertDispatched(CharacterChanged::class, fn (CharacterChanged $event) => $event->characterId === $this->harvey->id);
    }

    public function test_the_app_keeps_working_when_reverb_is_not_running(): void
    {
        $this->useReverb(port: 1);

        Livewire::actingAs($this->gm)->test(Messages::class, ['campaign' => $this->campaign])
            ->set('body', 'Séance samedi.')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertSame(1, Message::count());
        $this->assertSame(1, $this->alex->unreadNotifications()->count());
    }

    public function test_the_header_listens_to_the_personal_channel_and_counts_unread(): void
    {
        $badges = Livewire::actingAs($this->alex)->test(HeaderBadges::class, ['campaign' => $this->campaign]);
        $this->assertArrayHasKey('echo-private:users.'.$this->alex->id.',.activity', $badges->instance()->getListeners());

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Lampe']);

        $this->actingAs($this->alex);
        $badges->call('$refresh')->assertSee('1 non lue');
    }
}
