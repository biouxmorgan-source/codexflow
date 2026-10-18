<?php

namespace Tests\Feature\Security;

use App\Actions\Demo\LoadDemoCampaign;
use App\Enums\CampaignRole;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\MapToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * Taper une adresse ne suffit jamais : chaque page et chaque fichier du site est essayé,
 * avec de vrais numéros, par un visiteur, par un compte étranger à la campagne, par un
 * spectateur et par un joueur. Seul ce que leur rôle autorise s'ouvre.
 *
 * Toute nouvelle route est essayée d'office : en ajouter une sans contrôle fait échouer ce test.
 */
class RouteAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Pages ouvertes à tous, sans compte, ou qui ne concernent que le compte connecté. */
    private const OPEN = [
        'home', 'help', 'manifest', 'robots', 'sitemap', 'privacy', 'bugs.create', 'up',
        'invitations.show', 'gifts.show', 'table.guest', 'table.guest.file', 'table.guest.image', 'table.guest.token',
        'billing.success', 'campaigns.index', 'preferences', 'rewards', 'changelog', 'recommended', 'account.data',
        'notifications.index', 'notifications.open', 'entity-types.index', 'tags.index', 'library.sounds', 'search.all', 'bugs.index',
        'imports.ai.archive', 'imports.ai.archive.prompt', 'imports.ai.archive.skill', 'account.email.confirm',
    ];

    /** Ce qu'un joueur ouvre dans sa campagne (son personnage, la table partagée, les messages…). */
    private const PLAYER = [
        'campaigns.show', 'messages.index', 'search.index', 'timeline.index', 'graph.index', 'feedback.answer',
        'characters.show', 'characters.sheet', 'characters.portrait', 'characters.entity', 'characters.entity-image',
        'characters.entity-attachment', 'characters.document',
        'table.screen', 'table.file', 'table.image', 'table.token',
    ];

    /** Ce qu'un spectateur voit : la campagne et l'écran de table. */
    private const SPECTATOR = ['campaigns.show', 'table.screen', 'table.file', 'table.image', 'table.audio', 'table.token'];

    private Campaign $campaign;

    /** @var array<string, mixed> */
    private array $parameters;

    protected function setUp(): void
    {
        parent::setUp();

        $gm = User::factory()->create();
        $this->campaign = app(LoadDemoCampaign::class)->handle($gm, 'fr');
        $this->campaign->forceFill(['table_shared' => true])->save();

        $map = $this->campaign->maps()->firstOrFail();
        $character = $this->campaign->playerCharacters()->firstOrFail();
        $entity = $this->campaign->availableEntities()->whereKeyNot($character->entity_id)->orderBy('entities.id')->firstOrFail();
        $token = MapToken::query()->where('table_map_id', $map->id)->firstOrFail();

        $this->parameters = [
            'campaign' => $this->campaign,
            'entity' => $entity,
            'scene' => $this->campaign->scenes()->firstOrFail(),
            'rule' => $this->campaign->availableRules()->firstOrFail(),
            'document' => $this->campaign->availableDocuments()->firstOrFail(),
            'map' => $map,
            'token' => $token,
            'playSession' => $this->campaign->playSessions()->firstOrFail(),
            'feedbackRequest' => $this->campaign->feedbackRequests()->firstOrFail(),
            'character' => $character,
            'attachment' => Attachment::query()->firstOrFail(),
            'track' => $this->campaign->audioTracks()->firstOrFail(),
            'gameSystem' => $this->campaign->gameSystem,
            'world' => $this->campaign->world,
            'kind' => 'fiches',
            'interval' => 'monthly',
            'user' => $gm,
            'code' => 'CODE-INCONNU',
            'notification' => '00000000-0000-0000-0000-000000000000',
        ];
    }

    public function test_a_visitor_without_an_account_is_sent_to_the_login_page(): void
    {
        foreach ($this->protectedRoutes() as $route) {
            $response = $this->call($route->methods()[0], $this->url($route));

            $this->assertTrue(
                $response->isRedirect(route('login')),
                "Sans compte, {$route->getName()} doit renvoyer vers la connexion (reçu {$response->status()}).",
            );
        }
    }

    public function test_an_account_outside_the_campaign_opens_nothing_by_typing_the_address(): void
    {
        // Même réponse qu'une adresse inexistante : on n'apprend pas que la campagne ou le fichier existe.
        $this->assertNothingOpens(User::factory()->create(), $this->campaignRoutes(), 'un compte étranger', [404]);
    }

    public function test_a_spectator_only_opens_the_campaign_and_the_table_screen(): void
    {
        $spectator = User::factory()->create();
        $this->campaign->members()->attach($spectator, ['role' => CampaignRole::Spectator->value]);

        $this->assertNothingOpens($spectator, $this->campaignRoutes(except: self::SPECTATOR), 'un spectateur');
    }

    public function test_a_player_opens_nothing_reserved_to_the_game_master(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $this->assertNothingOpens($player, $this->campaignRoutes(except: self::PLAYER), 'un joueur');
    }

    public function test_a_player_does_not_open_another_players_character(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $routes = $this->campaignRoutes()->filter(fn (Route $route) => str_starts_with((string) $route->getName(), 'characters.') && $route->getName() !== 'characters.index');

        $this->assertNotEmpty($routes);
        $this->assertNothingOpens($player, $routes, 'le joueur d’un autre personnage');
    }

    /**
     * Un MJ ne se sert pas de sa propre campagne comme passe-partout : avec son numéro de
     * campagne et le numéro d'une fiche, d'une scène, d'un fichier… d'une autre, rien ne s'ouvre.
     */
    public function test_a_game_master_cannot_reach_another_campaigns_content_through_their_own(): void
    {
        $otherGm = User::factory()->create();
        $own = app(LoadDemoCampaign::class)->handle($otherGm, 'fr');
        $this->parameters['campaign'] = $own;

        $routes = $this->campaignRoutes()->reject(fn (Route $route) => $route->parameterNames() === ['campaign'] || in_array($route->getName(), ['imports.example', 'exports.download'], true));

        $this->assertNothingOpens($otherGm, $routes, 'le MJ d’une autre campagne');
    }

    /** Les fichiers déposés ne sont jamais servis tels quels : ni /storage/…, ni adresse signée. */
    public function test_uploaded_files_are_never_served_without_the_checks(): void
    {
        $this->assertFalse(Router::has('storage.local'));

        $this->actingAs(User::factory()->create())
            ->get('/storage/'.Attachment::query()->firstOrFail()->path)
            ->assertNotFound();
    }

    /** @param  iterable<Route>  $routes */
    private function assertNothingOpens(User $user, iterable $routes, string $who, array $statuses = [403, 404]): void
    {
        $this->actingAs($user);

        foreach ($routes as $route) {
            $response = $this->call($route->methods()[0], $this->url($route));
            $this->assertContains(
                $response->status(),
                $statuses,
                "{$route->getName()} ({$route->uri()}) répond {$response->status()} pour {$who}.",
            );
        }
    }

    /** @return Collection<int, Route> toutes les routes réservées aux comptes connectés */
    private function protectedRoutes()
    {
        return collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('auth', $route->gatherMiddleware(), true))
            ->reject(fn (Route $route) => str_starts_with($route->uri(), 'livewire') || $route->getName() === null)
            ->reject(fn (Route $route) => in_array('HEAD', $route->methods(), true) && count($route->methods()) === 1)
            ->values();
    }

    /**
     * Routes qui touchent une campagne, son contenu, ses fichiers ou sa bibliothèque.
     *
     * @param  list<string>  $except
     * @return Collection<int, Route>
     */
    private function campaignRoutes(array $except = [])
    {
        return $this->protectedRoutes()
            ->reject(fn (Route $route) => in_array($route->getName(), [...self::OPEN, ...$except], true))
            // Fortify (connexion, double authentification…) : propre au compte connecté.
            ->reject(fn (Route $route) => str_starts_with($route->getActionName(), 'Laravel\\Fortify\\'))
            ->reject(fn (Route $route) => str_starts_with((string) $route->getName(), 'admin.') || in_array($route->getName(), ['billing.portal', 'billing.checkout', 'invitations.accept', 'gifts.redeem', 'push.store', 'push.destroy'], true))
            ->values();
    }

    private function url(Route $route): string
    {
        $parameters = [];
        foreach ($route->parameterNames() as $name) {
            $this->assertArrayHasKey($name, $this->parameters, "Paramètre inconnu {$name} pour {$route->getName()} : l'ajouter au test.");
            $parameters[$name] = $this->parameters[$name];
        }

        return route($route->getName(), $parameters);
    }
}
