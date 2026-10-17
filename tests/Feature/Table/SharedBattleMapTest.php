<?php

namespace Tests\Feature\Table;

use App\Enums\CampaignRole;
use App\Livewire\Maps\Show;
use App\Livewire\Table\Initiative;
use App\Livewire\Table\Remote;
use App\Livewire\Table\Screen;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\MapToken;
use App\Models\TableMap;
use App\Models\User;
use App\Support\TableDisplay;
use App\Support\TableInitiative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Carte de combat partagée : chacun déplace son jeton sur l'écran, le MJ confie les autres,
 * montre l'ordre d'initiative et partage un lien invité en lecture seule.
 */
class SharedBattleMapTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private User $sam;

    private Campaign $campaign;

    private TableMap $map;

    private MapToken $harvey;

    private MapToken $wolf;

    private MapToken $ghoul;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->sam = User::factory()->create(['name' => 'Sam']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['table_shared' => true]);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->campaign->members()->attach($this->sam, ['role' => CampaignRole::Player->value]);

        $sheet = fn (string $name) => Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => $name]);
        $harvey = $sheet('Harvey');
        $this->campaign->playerCharacters()->create(['entity_id' => $harvey->id, 'user_id' => $this->alex->id]);

        $path = UploadedFile::fake()->image('map.png', 400, 300)->store('documents', 'local');
        $document = new Document(['title' => 'Crypte', 'disk' => 'local', 'path' => $path, 'original_name' => 'map.png', 'mime_type' => 'image/png', 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        $this->map = new TableMap(['name' => 'Crypte', 'grid_size' => 20]);
        $this->map->forceFill(['width' => 400, 'height' => 300]);
        $this->map->campaign()->associate($this->campaign);
        $this->map->document()->associate($document);
        $this->map->save();
        $this->map->refresh();

        $this->harvey = $this->token('Harvey', false, $harvey);
        $this->wolf = $this->token('Loup', false);
        $this->ghoul = $this->token('Goule', true);

        TableDisplay::show($this->campaign, 'map', $this->map->id);
    }

    private function token(string $label, bool $hidden, ?Entity $entity = null): MapToken
    {
        $token = new MapToken(['label' => $label, 'x' => 100, 'y' => 100, 'size' => 1, 'hidden' => $hidden, 'show_label' => true]);
        $token->entity()->associate($entity);
        $this->map->tokens()->save($token);

        return $token;
    }

    public function test_a_player_moves_their_character_and_the_tokens_the_game_master_hands_them(): void
    {
        $screen = Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign]);

        $screen->call('moveToken', $this->harvey->id, 150, 999)->assertOk();
        $this->assertSame([150.0, 300.0], [$this->harvey->fresh()->x, $this->harvey->fresh()->y], 'Posé sur la carte, jamais au-delà.');

        Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign])
            ->call('moveToken', $this->wolf->id, 10, 10)->assertForbidden();
        Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign])
            ->call('moveToken', $this->ghoul->id, 10, 10)->assertForbidden();
        Livewire::actingAs($this->sam)->test(Screen::class, ['campaign' => $this->campaign])
            ->call('moveToken', $this->harvey->id, 10, 10)->assertForbidden();

        // Le MJ confie le loup (un familier) à Sam.
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'map' => $this->map])
            ->assertSee('MJ et Alex (son personnage)')
            ->call('setController', $this->wolf->id, (string) $this->sam->id);
        Livewire::actingAs($this->sam)->test(Screen::class, ['campaign' => $this->campaign])
            ->call('moveToken', $this->wolf->id, 40, 50)->assertOk();
        $this->assertSame([40.0, 50.0], [$this->wolf->fresh()->x, $this->wolf->fresh()->y]);

        // Un joueur étranger à la campagne ne peut pas être choisi.
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'map' => $this->map])
            ->call('setController', $this->wolf->id, (string) User::factory()->create()->id)->assertStatus(422);
        Livewire::actingAs($this->alex)->test(Show::class, ['campaign' => $this->campaign, 'map' => $this->map])->assertForbidden();

        // Le MJ cesse de partager : plus rien ne bouge côté joueur.
        TableDisplay::share($this->campaign, false);
        $this->assertSame([], MapToken::movableIds($this->alex, $this->campaign->fresh(), $this->map->tokens()->get()));
        $this->assertCount(2, MapToken::movableIds($this->gm, $this->campaign->fresh(), $this->map->tokens()->get()), 'Le MJ déplace tous les jetons visibles.');
    }

    public function test_the_game_master_shows_the_initiative_order_and_steps_through_turns(): void
    {
        $initiative = Livewire::actingAs($this->gm)->test(Initiative::class, ['campaign' => $this->campaign])
            ->call('addTokens')
            ->set('label', 'Coffre piégé')
            ->call('add', false)
            ->call('add')
            ->assertHasErrors('label');

        $entries = collect(TableInitiative::state($this->campaign->fresh())['entries']);
        $this->assertSame(['Harvey', 'Loup', 'Goule', 'Coffre piégé'], $entries->pluck('label')->all());
        $this->assertSame([true, false, false, false], $entries->pluck('visible')->all(), 'Seul le PJ arrive visible.');

        [$harvey, $wolf, $ghoul] = $entries->pluck('id')->all();
        $initiative->call('setValue', $harvey, '12')->call('setValue', $wolf, '15,5')->call('setValue', $ghoul, '20')
            ->call('toggleVisible', $wolf)->call('toggleVisible', $entries[3]['id'])
            ->call('sort');
        $this->assertSame(['Goule', 'Loup', 'Harvey', 'Coffre piégé'], collect(TableInitiative::state($this->campaign->fresh())['entries'])->pluck('label')->all());

        // Pas encore montrée : rien côté joueur.
        Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign])->assertDontSee('Round 1');

        $this->actingAs($this->gm);
        $initiative->call('toggleShown')->call('step')->call('step');
        $state = TableInitiative::state($this->campaign->fresh());
        $this->assertSame([$harvey, 1], [$state['turn'], $state['round']], 'La goule, masquée, est sautée.');

        Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertSee('Round 1')->assertSee('Loup')->assertSee('15,5')->assertSee('Aussi présents')->assertSee('Coffre piégé')
            ->assertDontSee('Goule');

        $this->actingAs($this->gm);
        $initiative->call('step');
        $this->assertSame([$wolf, 2], [TableInitiative::state($this->campaign->fresh())['turn'], TableInitiative::state($this->campaign->fresh())['round']]);
        $initiative->call('step', -1);
        $this->assertSame([$harvey, 1], [TableInitiative::state($this->campaign->fresh())['turn'], TableInitiative::state($this->campaign->fresh())['round']]);

        $initiative->call('resetAll');
        $this->assertSame([], TableInitiative::state($this->campaign->fresh())['entries']);
        Livewire::actingAs($this->alex)->test(Initiative::class, ['campaign' => $this->campaign])->assertForbidden();
    }

    public function test_a_guest_link_shows_the_shared_screen_read_only_until_revoked(): void
    {
        $this->get(route('table.screen', $this->campaign))->assertRedirect();
        $remote = Livewire::actingAs($this->gm)->test(Remote::class, ['campaign' => $this->campaign])->call('createGuestLink');
        $token = $this->campaign->fresh()->table_guest_token;
        $this->assertSame(40, strlen($token));

        auth()->logout();
        $this->get(route('table.guest', $token))->assertOk()->assertSee('Harvey')->assertDontSee('Goule');
        $this->get(route('table.guest.file', $token))->assertOk();
        $this->get(route('table.guest', str_repeat('x', 40)))->assertNotFound();
        Livewire::test(Screen::class, ['token' => $token])->call('moveToken', $this->harvey->id, 1, 1)->assertForbidden();

        // Le MJ ne partage plus : écran neutre, plus aucun fichier.
        TableDisplay::share($this->campaign, false);
        $this->get(route('table.guest', $token))->assertOk()->assertSee("Le MJ ne partage plus l'écran de table.")->assertDontSee('Harvey');
        $this->get(route('table.guest.file', $token))->assertNotFound();

        TableDisplay::share($this->campaign, true);
        $this->actingAs($this->gm);
        $remote->call('revokeGuestLink');
        $this->assertNull($this->campaign->fresh()->table_guest_token);
        $this->get(route('table.guest', $token))->assertNotFound();
        $this->get(route('table.guest.file', $token))->assertNotFound();
    }
}
