<?php

namespace Tests\Feature\Pwa;

use App\Enums\CampaignRole;
use App\Http\Middleware\AvailableOffline;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Notifications\CampaignEvent;
use App\Notifications\Channels\PushChannel;
use App\Support\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\VAPID;
use NotificationChannels\WebPush\WebPushChannel;
use RuntimeException;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    private PlayerCharacter $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Enfants de la Peur']);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->character = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $this->player->id]);
    }

    private function subscription(string $endpoint = 'https://push.example.test/abc'): array
    {
        return ['endpoint' => $endpoint, 'keys' => ['p256dh' => 'cle-publique', 'auth' => 'jeton'], 'contentEncoding' => 'aes128gcm'];
    }

    private function withVapidKeys(): void
    {
        $keys = VAPID::createVapidKeys();
        config(['webpush.vapid.public_key' => $keys['publicKey'], 'webpush.vapid.private_key' => $keys['privateKey']]);
    }

    public function test_the_app_is_installable(): void
    {
        $manifest = $this->get('/manifest.webmanifest')->assertOk()->assertHeader('Content-Type', 'application/manifest+json')->json();

        $this->assertSame('standalone', $manifest['display']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));

        $this->actingAs($this->player)->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false)
            ->assertDontSee('sagawyn-guest');

        auth()->logout();
        $this->get(route('login'))->assertOk()->assertSee('<meta name="sagawyn-guest" content="1">', false);
    }

    public function test_only_the_character_sheet_is_kept_for_offline_reading(): void
    {
        $this->actingAs($this->player)->get(route('characters.show', [$this->campaign, $this->character]))
            ->assertOk()
            ->assertHeader(AvailableOffline::HEADER, '1');

        $this->get(route('campaigns.index'))->assertOk()->assertHeaderMissing(AvailableOffline::HEADER);
        $this->get(route('messages.index', $this->campaign))->assertOk()->assertHeaderMissing(AvailableOffline::HEADER);

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('characters.show', [$this->campaign, $this->character]))
            ->assertForbidden()
            ->assertHeaderMissing(AvailableOffline::HEADER);
    }

    public function test_a_device_subscribes_and_unsubscribes_from_push(): void
    {
        $this->post(route('push.store'), $this->subscription())->assertRedirect(route('login'));

        $this->actingAs($this->player)->postJson(route('push.store'), $this->subscription())->assertNoContent();
        $this->actingAs($this->player)->postJson(route('push.store'), $this->subscription())->assertNoContent();
        $this->assertSame(1, $this->player->pushSubscriptions()->count());

        $this->actingAs($this->player)->postJson(route('push.store'), $this->subscription('http://push.example.test/abc'))
            ->assertJsonValidationErrors('endpoint');

        // Un autre compte sur le même navigateur reprend l'abonnement.
        $this->actingAs($this->gm)->postJson(route('push.store'), $this->subscription())->assertNoContent();
        $this->assertSame(0, $this->player->pushSubscriptions()->count());
        $this->assertSame(1, $this->gm->pushSubscriptions()->count());

        $this->actingAs($this->player)->deleteJson(route('push.destroy'), ['endpoint' => 'https://push.example.test/abc'])->assertNoContent();
        $this->assertSame(1, $this->gm->pushSubscriptions()->count());

        $this->actingAs($this->gm)->deleteJson(route('push.destroy'), ['endpoint' => 'https://push.example.test/abc'])->assertNoContent();
        $this->assertSame(0, $this->gm->pushSubscriptions()->count());
    }

    public function test_push_is_sent_only_with_vapid_keys_and_a_subscribed_device(): void
    {
        $event = new CampaignEvent($this->campaign, 'message', 'Le MJ vous écrit : Rendez-vous au port.', route('messages.index', $this->campaign));

        $this->assertSame(['database'], $event->via($this->player));

        $this->withVapidKeys();
        $this->assertSame(['database'], $event->via($this->player));

        $this->player->updatePushSubscription('https://push.example.test/abc', 'cle', 'jeton');
        $this->assertSame(['database', PushChannel::class], $event->via($this->player->fresh()));

        $event->id = 'f3b5c1de-0000-4000-8000-000000000001';
        $payload = $event->toWebPush($this->player, $event)->toArray();
        $this->assertSame('Les Enfants de la Peur', $payload['title']);
        $this->assertSame('Le MJ vous écrit : Rendez-vous au port.', $payload['body']);
        $this->assertSame('/notifications/'.$event->id, $payload['data']['url']);
    }

    public function test_an_unreachable_push_service_never_breaks_the_action(): void
    {
        $this->withVapidKeys();
        $this->player->updatePushSubscription('https://push.example.test/abc', 'cle', 'jeton');
        $this->mock(WebPushChannel::class)->shouldReceive('send')->once()->andThrow(new RuntimeException('service push injoignable'));

        Notify::player($this->character, 'message', 'Le MJ vous écrit : bonjour', route('messages.index', $this->campaign));

        $this->assertSame(1, $this->player->notifications()->count());
    }
}
