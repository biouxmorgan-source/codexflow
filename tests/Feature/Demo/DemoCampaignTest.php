<?php

namespace Tests\Feature\Demo;

use App\Actions\Demo\LoadDemoCampaign;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\MapToken;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Secret;
use App\Models\TableMap;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use App\Support\Dice\DiceFormula;
use App\Support\Dice\TableDice;
use App\Support\EntityLinks;
use App\Support\Locale;
use App\Support\TableInitiative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DemoCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_the_demonstration_campaign_shows_every_feature(): void
    {
        $gm = User::factory()->create();

        $campaign = app(LoadDemoCampaign::class)->handle($gm, 'fr');
        $text = LoadDemoCampaign::text('fr');

        $this->assertSame($text['campaign']['name'], $campaign->name);
        $this->assertTrue($campaign->is_demo, 'La démonstration est repérée pour les statistiques.');
        $this->assertSame($text['game']['name'], $campaign->gameSystem->name);
        $this->assertSame($text['world']['name'], $campaign->world->name);
        $this->assertTrue($campaign->isGameMaster($gm));

        // Des fiches dans le monde ; propres à la campagne, l'inconnu du phare et la fiche du personnage joueur.
        $world = Entity::where('world_id', $campaign->world_id)->get();
        $this->assertGreaterThanOrEqual(15, $world->count());
        $this->assertSame(2, Entity::where('campaign_id', $campaign->id)->count());

        // Couvertures du jeu et du monde.
        $this->assertTrue(Storage::disk('local')->exists($campaign->gameSystem->image_path));
        $this->assertTrue(Storage::disk('local')->exists($campaign->world->image_path));
        $this->assertGreaterThan(5, $world->whereNotNull('image_path')->count());

        foreach ($world->whereNotNull('image_path') as $entity) {
            $this->assertTrue(Storage::disk(Entity::FILES_DISK)->exists($entity->image_path));
        }

        // Les deux zones sont employées : des notes MJ, et des relations cachées.
        $this->assertGreaterThan(5, $world->whereNotNull('gm_notes')->count());
        $relations = EntityRelation::whereIn('from_entity_id', $world->modelKeys())->get();
        $this->assertGreaterThan(0, $relations->where('zone', Zone::GameMaster)->count());
        $this->assertGreaterThan(0, $relations->where('zone', Zone::Public)->count());
        $this->assertGreaterThan(0, $relations->whereNotNull('reverse_label')->count());

        // Champs libres du jeu, renseignés sur les fiches.
        $fields = $campaign->gameSystem->fieldDefinitions;
        $this->assertGreaterThan(0, $fields->where('zone', Zone::GameMaster)->count());
        $this->assertGreaterThan(0, $fields->where('player_editable', true)->count());
        $ysane = $world->firstWhere('name', $text['entities']['ysane']['name']);
        $this->assertNotEmpty($ysane->field_values);

        // Tous les types de champ utiles à un jeu : référence à une fiche, document, champ commun à deux types.
        $byName = $fields->keyBy('name');
        $brannoc = $world->firstWhere('name', $text['entities']['brannoc']['name']);
        $this->assertStringContainsString($text['entities']['greythread']['name'], $brannoc->field_values[$byName[$text['fields']['allegiance']]->id]);
        $this->assertCount(2, $byName[$text['fields']['danger']]->typeIds());
        $city = $world->firstWhere('name', $text['entities']['city']['name']);
        $this->assertSame($text['documents']['plan']['title'], Document::find($city->field_values[$byName[$text['fields']['reference']]->id])->title);

        // Une pièce jointe réservée au MJ, des tags colorés.
        $seal = $world->firstWhere('name', $text['entities']['seal']['name']);
        $this->assertSame(Zone::GameMaster, $seal->attachments()->sole()->zone);
        $this->assertSame(0, Tag::where('user_id', $gm->id)->whereNull('color')->count());

        // Scénario, scènes, documents, règles, secrets, carte et chronologie.
        $scenes = Scene::whereIn('scenario_id', $campaign->scenarios()->pluck('id'))->get();
        $this->assertGreaterThanOrEqual(6, $scenes->count());
        $this->assertGreaterThanOrEqual(3, Document::where('campaign_id', $campaign->id)->count());
        $this->assertGreaterThan(0, Rule::where('game_system_id', $campaign->game_system_id)->count());
        $this->assertGreaterThan(0, Rule::where('campaign_id', $campaign->id)->count());
        $this->assertGreaterThanOrEqual(3, Secret::where('campaign_id', $campaign->id)->count());

        // Carte de campagne (le port) et sa sous-carte de combat (la cave du phare), ouverte d'un point.
        $maps = TableMap::where('campaign_id', $campaign->id)->get();
        $this->assertCount(2, $maps);
        $map = $maps->sole('is_campaign_map', true);
        $this->assertTrue($map->grid_enabled);
        $this->assertGreaterThan(0, $map->width);
        $this->assertGreaterThanOrEqual(5, MapToken::where('table_map_id', $map->id)->count());
        $this->assertSame(1, MapToken::where('table_map_id', $map->id)->where('hidden', true)->count());
        $this->assertGreaterThan(0, $map->points()->where('shown_at_table', true)->count());
        // Un point relie plusieurs fiches (le lieu, ses PNJ) et plusieurs scènes.
        $hall = $map->points()->where('label', $text['entities']['hall']['name'])->sole();
        $this->assertCount(4, $hall->entities);
        $this->assertCount(2, $hall->scenes);
        $cellar = $maps->sole('is_campaign_map', false);
        $this->assertSame($cellar->id, $map->points()->whereNotNull('target_map_id')->sole()->target_map_id);
        $this->assertSame([$map->id], array_map(fn (TableMap $parent) => $parent->id, $cellar->parents()));
        $this->assertGreaterThan(0, $cellar->points()->count());
        $this->assertSame(1, $cellar->tokens()->where('hidden', true)->count());
        $this->assertEqualsCanonicalizing(['circle', 'cone'], $cellar->templates()->pluck('shape')->all());
        $this->assertSame(1, $cellar->templates()->where('hidden', true)->count());

        // Ordre d'initiative préparé, pas encore montré ; les Noyeux cachés aux joueurs, la marée sans ordre.
        $initiative = TableInitiative::state($campaign);
        $this->assertFalse($initiative['shown']);
        $this->assertCount(6, $initiative['entries']);
        $this->assertSame(1, collect($initiative['entries'])->where('visible', false)->count());
        $this->assertSame(1, collect($initiative['entries'])->where('ordered', false)->count());

        $events = TimelineEvent::where('campaign_id', $campaign->id)->get();
        $this->assertSame(['planned', 'played', 'world'], $events->pluck('kind')->unique()->sort()->values()->all());
        $this->assertGreaterThan(0, $events->where('zone', Zone::Public)->count());

        // Sons en boucle, liés aux scènes, dont un de la bibliothèque « Mes sons » ; fichiers lisibles.
        $tracks = $campaign->availableTracks()->orderBy('id')->get();
        $this->assertSame(array_values($text['sounds']), $tracks->pluck('title')->all());
        $this->assertSame([$text['sounds']['storm']], AudioTrack::where('user_id', $gm->id)->whereNull('campaign_id')->pluck('title')->all());
        foreach ($tracks as $track) {
            $this->assertTrue($track->loop);
            $this->assertStringStartsWith('RIFF', (string) Storage::disk(AudioTrack::DISK)->get($track->path));
            $this->assertNotEmpty($track->scenes()->pluck('scenes.id'));
        }

        // Séance 1 jouée : résumé avec liens, événement de la chronologie, demande d'avis ouverte.
        $session = $campaign->playSessions()->sole();
        $this->assertNotNull($session->ended_at);
        $this->assertNull($campaign->openSession());
        $this->assertMatchesRegularExpression(EntityLinks::PATTERN, (string) $session->summary);
        $this->assertSame(1, $events->where('play_session_id', $session->id)->count());
        $this->assertCount(3, $session->notes);
        $feedback = $campaign->feedbackRequests()->sole();
        $this->assertTrue($feedback->isOpen() && $feedback->anonymous);
        $this->assertSame($session->id, $feedback->play_session_id);

        // Un personnage joueur tiré d'un prétiré, sans joueur, avec tout ce qu'un joueur peut recevoir.
        $character = $campaign->playerCharacters()->sole();
        $this->assertNull($character->user_id);
        $this->assertSame($text['entities']['teska']['name'], $character->entity->name);
        $this->assertEqualsCanonicalizing(['document', 'entity', 'information', 'possession', 'rule'], $character->grants()->pluck('kind')->unique()->values()->all());
        $this->assertSame(1, $character->grants()->whereNotNull('secret_id')->count());
        $this->assertSame(['clue', 'lie', 'rumour', 'truth'], Secret::where('campaign_id', $campaign->id)->pluck('kind')->unique()->sort()->values()->all());
        $this->assertTrue($campaign->players_edit_profile);

        // Scènes jouées : l'une comme prévu, l'autre racontée dans « Ce qui s'est passé ».
        $played = $scenes->where('status', SceneStatus::Played);
        $this->assertSame(1, $played->where('played_as_planned', true)->count());
        $this->assertMatchesRegularExpression(EntityLinks::PATTERN, (string) $played->whereNotNull('what_happened')->sole()->what_happened);

        // Contenu maison : la version officielle remplacée reste au MJ.
        $this->assertNotNull($world->firstWhere('name', $text['entities']['drowned']['name'])->official_version);
        $this->assertTrue($world->firstWhere('name', $text['entities']['drowned']['name'])->is_house);
        $this->assertNotNull(Rule::where('campaign_id', $campaign->id)->where('title', $text['rules']['mist']['title'])->sole()->official_version);

        // Dés : lancers de la table qui lisent la fiche, un lancer du MJ pour lui, trois lancers de la séance 1.
        $presets = TableDice::presets($campaign);
        $this->assertCount(6, $presets);
        $this->assertTrue(DiceFormula::fromArray($presets[0]['formula'])->readsSheet());
        $this->assertCount(1, TableDice::presets($campaign, $gm));
        $rolls = $campaign->diceRolls()->get();
        $this->assertCount(3, $rolls);
        $skill = $character->entity->field_values[$byName[$text['fields']['skill']]->id];
        $this->assertSame($skill, $rolls->firstWhere('label', $text['dice']['rolls']['follow'])->formula()->modifier);
        $request = $campaign->diceRequests()->sole();
        $this->assertFalse($request->isOpen());
        $this->assertSame(1, $request->rolls()->count());

        // « À jouer » et fiches épinglées.
        $this->assertSame(4, ToPlayItem::where('campaign_id', $campaign->id)->count());
        $this->assertSame(1, ToPlayItem::where('campaign_id', $campaign->id)->whereNotNull('done_at')->count());
        $this->assertCount(3, $campaign->pins);
    }

    public function test_every_page_of_the_demonstration_opens(): void
    {
        $gm = User::factory()->create();
        $campaign = app(LoadDemoCampaign::class)->handle($gm, 'fr');
        $character = $campaign->playerCharacters()->sole();
        $session = $campaign->playSessions()->sole();

        foreach ([
            route('campaigns.show', $campaign),
            route('characters.index', $campaign),
            route('characters.show', [$campaign, $character]),
            route('characters.show', [$campaign, $character, 'comme' => 1]),
            route('sessions.show', [$campaign, $session]),
            route('feedback.index', $campaign),
            route('secrets.index', $campaign),
            route('tags.index'),
            route('maps.atlas', $campaign),
            route('maps.atlas', [$campaign, $campaign->maps()->where('is_campaign_map', false)->sole()]),
            route('maps.show', [$campaign, $campaign->maps()->where('is_campaign_map', false)->sole()]),
            route('book.show', $campaign),
            route('audio.index', $campaign),
            route('library.sounds'),
        ] as $url) {
            $this->actingAs($gm)->get($url)->assertOk();
        }
    }

    public function test_the_internal_links_of_the_demonstration_all_point_at_a_sheet(): void
    {
        $campaign = app(LoadDemoCampaign::class)->handle(User::factory()->create(), 'fr');
        $scenes = Scene::whereIn('scenario_id', $campaign->scenarios()->pluck('id'))->get();
        $names = Entity::where('world_id', $campaign->world_id)->orWhere('campaign_id', $campaign->id)->pluck('id', 'name');

        $links = 0;

        foreach ($scenes as $scene) {
            preg_match_all(EntityLinks::PATTERN, (string) $scene->description.(string) $scene->gm_notes, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $links++;
                $this->assertNotEmpty($match[2] ?? null, "Le lien « {$match[0] } » n’a pas d’identifiant.");
                $this->assertSame($names[$match[1]] ?? null, (int) $match[2], "Le lien « {$match[0]} » ne pointe pas sur la bonne fiche.");
            }
        }

        $this->assertGreaterThan(10, $links);
    }

    public function test_a_game_master_loads_the_demonstration_from_the_campaign_list(): void
    {
        $gm = User::factory()->create();

        Livewire::actingAs($gm)
            ->test(CampaignIndex::class)
            ->set('demoLocale', 'fr')
            ->call('loadDemo')
            ->assertRedirect();

        $campaign = Campaign::where('user_id', $gm->id)->sole();
        $this->assertSame(LoadDemoCampaign::text('fr')['campaign']['name'], $campaign->name);

        // Elle s'ouvre, et le MJ y retrouve ses outils.
        $this->actingAs($gm)->get(route('campaigns.show', $campaign))->assertOk()->assertSee('Pierrecendre');
        $this->actingAs($gm)->get(route('graph.index', $campaign))->assertOk();
        $this->actingAs($gm)->get(route('timeline.index', $campaign))->assertOk();
    }

    public function test_the_demonstration_can_be_exported_and_handed_to_another_game_master(): void
    {
        $campaign = app(LoadDemoCampaign::class)->handle(User::factory()->create(), 'fr');
        $other = User::factory()->create();

        $path = (new CampaignExport($campaign))->write();
        $imported = (new CampaignImport($other))->handle($path);
        @unlink($path);

        $this->assertSame(LoadDemoCampaign::text('fr')['campaign']['name'], $imported->name);
        $this->assertSame($campaign->table_theme, $imported->table_theme);
        $this->assertSame(
            Entity::where('world_id', $campaign->world_id)->count(),
            Entity::where('world_id', $imported->world_id)->count(),
        );
        $this->assertSame(
            Secret::where('campaign_id', $campaign->id)->count(),
            Secret::where('campaign_id', $imported->id)->count(),
        );
        $this->assertSame(
            TimelineEvent::where('campaign_id', $campaign->id)->count(),
            TimelineEvent::where('campaign_id', $imported->id)->count(),
        );
        $this->assertSame(2, TableMap::where('campaign_id', $imported->id)->count());

        // Les liens des scènes sont recalculés vers les nouvelles fiches.
        $brannoc = Entity::where('world_id', $imported->world_id)->where('name', LoadDemoCampaign::text('fr')['entities']['brannoc']['name'])->sole();
        $first = Scene::whereIn('scenario_id', $imported->scenarios()->pluck('id'))->orderBy('position')->first();
        $this->assertStringContainsString('[['.$brannoc->name.'|'.$brannoc->id.']]', (string) $first->description);
        // « À jouer » : ni ce qui est fait, ni ce qui vise un personnage de la table d'origine.
        $this->assertSame(2, ToPlayItem::where('campaign_id', $imported->id)->count());
        $this->assertCount(3, $imported->pins);

        // Le personnage joueur reste à la table d'origine ; le modèle de jeu emporte types, champs et règles.
        $this->assertSame(0, $imported->playerCharacters()->count());
        $template = CampaignExport::template($campaign, withRules: true);
        $this->assertCount(14, $template['field_definitions']);
        $this->assertCount(3, $template['rules']);
    }

    public function test_every_language_has_exactly_the_same_demonstration_content(): void
    {
        $reference = LoadDemoCampaign::text('fr');
        $keys = fn (array $text) => collect(Arr::dot($text))
            ->keys()
            // Les lignes d'un PDF peuvent varier en nombre d'une langue à l'autre.
            ->reject(fn (string $key) => preg_match('/\.lines\.\d+$/', $key))
            ->sort()->values()->all();
        $links = fn (array $text) => collect(Arr::dot($text))
            ->map(fn ($value) => is_string($value) && preg_match_all('/\[\[[a-z0-9_]+\]\]/', $value, $m) ? implode(' ', $m[0]) : null)
            ->filter()->all();

        $this->assertSame(array_keys(Locale::available()), LoadDemoCampaign::locales());

        foreach (LoadDemoCampaign::locales() as $locale) {
            $text = LoadDemoCampaign::text($locale);
            $this->assertSame($keys($reference), $keys($text), "La démonstration en {$locale} n’a pas les mêmes éléments qu’en français.");
            $this->assertSame($links($reference), $links($text), "Les liens [[…]] de la démonstration en {$locale} diffèrent du français.");
        }
    }

    public function test_the_demonstration_loads_in_the_chosen_language(): void
    {
        $gm = User::factory()->create();

        foreach (LoadDemoCampaign::locales() as $locale) {
            $campaign = app(LoadDemoCampaign::class)->handle($gm, $locale);
            $text = LoadDemoCampaign::text($locale);

            $this->assertSame($text['campaign']['name'], $campaign->name);
            $this->assertSame($text['types']['faction'], EntityType::where('user_id', $gm->id)->latest('id')->value('name'));
            $this->assertSame($text['relations']['ysane_hall'][0], EntityRelation::latest('id')->skip(22)->value('label'));
        }

        // Sans langue précisée, celle de l'interface ; une langue inconnue retombe sur le français.
        app()->setLocale('de');
        // Déjà chargées une fois : les copies sont numérotées.
        $this->assertSame(LoadDemoCampaign::text('de')['campaign']['name'].' (2)', app(LoadDemoCampaign::class)->handle($gm)->name);
        $again = app(LoadDemoCampaign::class)->handle($gm, 'xx');
        $this->assertSame(LoadDemoCampaign::text('fr')['campaign']['name'].' (2)', $again->name);
        $this->assertSame('Vehrmund (10)', $again->world->name);
        $this->assertSame(LoadDemoCampaign::text('fr')['game']['name'].' (2)', $again->gameSystem->name);
    }

    public function test_the_campaign_list_offers_the_interface_language_first(): void
    {
        // La langue de l'interface, déjà résolue (choix du compte, sinon navigateur) par le middleware.
        app()->setLocale('it');

        Livewire::actingAs(User::factory()->create())
            ->test(CampaignIndex::class)
            ->assertSet('demoLocale', 'it')
            ->set('demoLocale', 'xx')
            ->call('loadDemo')
            ->assertHasErrors('demoLocale');
    }
}
