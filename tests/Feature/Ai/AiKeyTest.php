<?php

namespace Tests\Feature\Ai;

use App\Livewire\Account\AiKey;
use App\Livewire\Ai\Index;
use App\Models\AiSuggestion;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use App\Support\Ai\Clients\AiRequestFailed;
use App\Support\Ai\Clients\AnthropicClient;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiKeyTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'sk-test-0123456789abcdefWXYZ';

    private User $gm;

    private Campaign $campaign;

    private Entity $morel;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id, 'name' => 'Les Ombres d’Arkham']);
        $this->morel = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Morel', 'world_id' => $world->id, 'campaign_id' => null]);
    }

    private function saveKey(string $provider, string $model): void
    {
        Livewire::actingAs($this->gm)->test(AiKey::class)
            ->set('provider', $provider)
            ->set('model', $model)
            ->set('apiKey', self::KEY)
            ->call('save')
            ->assertHasNoErrors();
    }

    /** Parcours : le MJ enregistre sa clé ; elle est chiffrée, jamais réaffichée, et peut être effacée. */
    public function test_the_key_is_stored_encrypted_and_never_shown_again(): void
    {
        $this->saveKey('mistral', 'mistral-large-latest');

        $this->gm->refresh();
        $this->assertSame(self::KEY, $this->gm->ai_api_key);
        $this->assertNotSame(self::KEY, DB::table('users')->where('id', $this->gm->id)->value('ai_api_key'));
        $this->assertArrayNotHasKey('ai_api_key', $this->gm->toArray());

        $this->actingAs($this->gm)->get(route('preferences'))->assertOk()
            ->assertDontSee(self::KEY)->assertDontSee('0123456789')->assertSee('…WXYZ');

        // Changer de modèle garde la clé ; changer de fournisseur en demande une nouvelle.
        Livewire::actingAs($this->gm)->test(AiKey::class)
            ->set('model', 'mistral-medium-latest')->call('save')->assertHasNoErrors()
            ->set('provider', 'openai')->call('save')->assertHasErrors('apiKey')
            ->call('forget');

        $this->assertFalse($this->gm->fresh()->hasAiKey());
    }

    public function test_the_game_master_analyses_a_session_with_their_own_key(): void
    {
        $this->saveKey('openai', 'gpt-5');

        Http::fake(['api.openai.com/*' => Http::response([
            'choices' => [['finish_reason' => 'stop', 'message' => ['content' => json_encode([
                'summary' => 'Morel s’enfuit.',
                'statuses' => [['entity' => $this->morel->id, 'status' => 'en fuite']],
            ])]]],
        ])]);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee('Analyser directement avec ChatGPT (OpenAI)')
            ->set('extraNotes', 'Morel a pris la fuite par les toits.')
            ->call('analyseDirectly')
            ->assertHasNoErrors();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request->header('Authorization')[0] === 'Bearer '.self::KEY
            && $request['model'] === 'gpt-5'
            && str_contains($request['messages'][0]['content'], 'Les Ombres d’Arkham')
            && str_contains($request['messages'][0]['content'], 'Morel a pris la fuite par les toits.'));

        // Comme en mode « texte à coller » : des propositions, rien d'appliqué.
        $this->assertSame(['summary', 'status'], AiSuggestion::orderBy('id')->pluck('kind')->all());
        $this->assertNull($this->morel->stateIn($this->campaign)->status);
    }

    public function test_a_refused_key_is_explained_without_saving_anything(): void
    {
        $this->saveKey('mistral', 'mistral-large-latest');
        Http::fake(['api.mistral.ai/*' => Http::response(['message' => 'Unauthorized'], 401)]);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('analyseDirectly')
            ->assertHasErrors('direct')
            ->assertSee('Le Chat (Mistral) refuse la clé');

        $this->assertSame(0, AiSuggestion::count());
    }

    public function test_without_a_key_nothing_is_sent(): void
    {
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertDontSee('Analyser directement')
            ->call('analyseDirectly')
            ->assertHasErrors('direct');

        Http::assertNothingSent();
    }

    public function test_claude_is_called_with_the_game_masters_key_only(): void
    {
        // Une clé du serveur ne doit jamais servir à la place de celle du MJ.
        putenv('ANTHROPIC_API_KEY=sk-ant-server-key');
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
                'content' => [['type' => 'text', 'text' => '{"summary": "Résumé"}']],
                'stop_reason' => 'end_turn', 'stop_sequence' => null,
                'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
            ])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'msg_2', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
                'content' => [], 'stop_reason' => 'refusal', 'stop_sequence' => null,
                'usage' => ['input_tokens' => 10, 'output_tokens' => 0],
            ])),
        ]));
        $stack->push(Middleware::history($history));

        try {
            $client = new AnthropicClient(self::KEY, 'claude-opus-5-5', new Guzzle(['handler' => $stack]));
            $this->assertSame('{"summary": "Résumé"}', $client->complete('Bonjour'));

            $request = $history[0]['request'];
            $body = json_decode((string) $request->getBody(), true);
            $this->assertSame(self::KEY, $request->getHeaderLine('x-api-key'));
            $this->assertSame('https://api.anthropic.com/v1/messages?beta=true', (string) $request->getUri());
            $this->assertSame('claude-opus-5-5', $body['model']);
            $this->assertSame('default', $body['fallbacks']);
            $this->assertStringContainsString('server-side-fallback-2026-07-01', $request->getHeaderLine('anthropic-beta'));

            $this->expectException(AiRequestFailed::class);
            $client->complete('Bonjour');
        } finally {
            putenv('ANTHROPIC_API_KEY');
        }
    }
}
