<?php

namespace Tests\Feature;

use App\Enums\CampaignRole;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Feedback\Answer;
use App\Livewire\Feedback\Index;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FeedbackRequest;
use App\Models\FeedbackResponse;
use App\Models\User;
use App\Support\FeedbackRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Avis des joueurs : le MJ demande une note en étoiles et des retours en fin de séance
 * ou de campagne ; chaque joueur répond une fois ; l'anonymat cache les auteurs.
 */
class PlayerFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alice;

    private User $bruno;

    private User $spectator;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alice = User::factory()->create(['name' => 'Alice']);
        $this->bruno = User::factory()->create(['name' => 'Bruno']);
        $this->spectator = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alice, ['role' => CampaignRole::Player->value]);
        $this->campaign->members()->attach($this->bruno, ['role' => CampaignRole::Player->value]);
        $this->campaign->members()->attach($this->spectator, ['role' => CampaignRole::Spectator->value]);
    }

    private function ask(bool $anonymous = true, bool $session = false): FeedbackRequest
    {
        $playSession = $session ? $this->campaign->playSessions()->create(['number' => 3, 'started_at' => now()]) : null;
        $this->actingAs($this->gm);

        return FeedbackRequests::ask($this->campaign, $playSession, $anonymous, $this->gm);
    }

    private function answer(User $player, FeedbackRequest $request, int $rating, string $liked = '', string $improve = '')
    {
        return Livewire::actingAs($player)->test(Answer::class, ['campaign' => $this->campaign, 'feedbackRequest' => $request])
            ->set('rating', $rating)
            ->set('liked', $liked)
            ->set('improve', $improve)
            ->call('save');
    }

    public function test_gm_asks_about_a_session_and_players_are_notified(): void
    {
        $session = $this->campaign->playSessions()->create(['number' => 3, 'started_at' => now()]);

        Livewire::actingAs($this->gm)->withQueryParams(['seance' => $session->id])
            ->test(Index::class, ['campaign' => $this->campaign])
            ->assertSet('subject', (string) $session->id)
            ->call('ask')
            ->assertHasNoErrors()
            ->assertSee('Session 3')
            ->assertSee('Réponses : 0 sur 2');

        $request = FeedbackRequest::sole();
        $this->assertTrue($request->anonymous);
        $this->assertSame($session->id, $request->play_session_id);
        $this->assertSame('Le MJ vous demande votre avis sur la session 3.', $this->alice->notifications()->sole()->data['text']);
        $this->assertSame(1, $this->bruno->notifications()->count());
        $this->assertSame(0, $this->spectator->notifications()->count());

        // Pas deux demandes ouvertes sur le même sujet.
        Livewire::actingAs($this->gm)->withQueryParams(['seance' => $session->id])
            ->test(Index::class, ['campaign' => $this->campaign])
            ->call('ask')
            ->assertHasErrors('subject');
        $this->assertSame(1, FeedbackRequest::count());
    }

    public function test_a_player_answers_once_and_the_gm_is_notified(): void
    {
        $request = $this->ask(anonymous: false, session: true);

        $this->answer($this->alice, $request, 0)->assertHasErrors('rating');
        $this->answer($this->alice, $request, 5, 'Le combat sur le pont', 'Un peu long au début')
            ->assertHasNoErrors()
            ->assertSee('Merci pour votre avis');

        $this->assertSame('Alice a donné son avis sur la session 3 : 5/5.', $this->gm->fresh()->notifications()->sole()->data['text']);

        $this->answer($this->alice, $request, 1)->assertHasErrors('rating');
        $this->assertSame(1, $request->responses()->count());
        $this->assertSame(5, $request->responses()->sole()->rating);
    }

    public function test_only_players_of_the_campaign_can_answer(): void
    {
        $request = $this->ask();
        $stranger = User::factory()->create();

        foreach ([$this->spectator, $stranger, $this->gm] as $user) {
            Livewire::actingAs($user)->test(Answer::class, ['campaign' => $this->campaign, 'feedbackRequest' => $request])->assertForbidden();
        }

        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        Livewire::actingAs($this->alice)->test(Answer::class, ['campaign' => $other, 'feedbackRequest' => $request])->assertNotFound();
        Livewire::actingAs($this->alice)->test(Index::class, ['campaign' => $this->campaign])->assertForbidden();
    }

    public function test_anonymous_answers_never_reveal_their_authors(): void
    {
        $request = $this->ask();
        $this->answer($this->alice, $request, 4, 'Les intrigues de cour');
        $this->answer($this->bruno, $request, 2, '', 'Plus de combats');

        $this->assertEqualsCanonicalizing(
            ['Nouvel avis anonyme sur la campagne : 4/5.', 'Nouvel avis anonyme sur la campagne : 2/5.'],
            $this->gm->notifications()->get()->pluck('data.text')->all(),
        );

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['3,0', 'Réponses : 2 sur 2', 'Les intrigues de cour', 'Plus de combats'])
            ->assertDontSee('Alice')
            ->assertDontSee('Bruno');

        $this->assertArrayNotHasKey('user_id', $request->responses()->first()->toArray());
    }

    public function test_signed_answers_show_names_with_average_and_distribution(): void
    {
        $request = $this->ask(anonymous: false);
        $this->answer($this->alice, $request, 5, 'Tout');
        $this->answer($this->bruno, $request, 4);

        $request->load('responses');
        $this->assertSame(4.5, $request->average());
        $this->assertSame([5 => 1, 4 => 1, 3 => 0, 2 => 0, 1 => 0], $request->distribution());

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['4,5', 'Alice', 'Tout']);
    }

    public function test_a_closed_request_refuses_answers_and_can_be_reopened_or_deleted(): void
    {
        $request = $this->ask();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('close', $request->id);
        $this->assertFalse($request->fresh()->isOpen());

        Livewire::actingAs($this->alice)->test(Answer::class, ['campaign' => $this->campaign, 'feedbackRequest' => $request->fresh()])
            ->assertSee('Cette demande est close.');
        $this->answer($this->alice, $request, 3)->assertHasErrors('rating');

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('reopen', $request->id);
        $this->answer($this->alice, $request, 3)->assertHasNoErrors();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('delete', $request->id);
        $this->assertModelMissing($request);
        $this->assertSame(0, FeedbackResponse::count());
    }

    public function test_the_player_sees_pending_requests_on_their_character_page(): void
    {
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $character = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alice->id]);
        $request = $this->ask(session: true);

        Livewire::actingAs($this->alice)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $character])
            ->assertSee('Le MJ demande votre avis sur une séance : Session 3.');

        $this->answer($this->alice, $request, 4);

        Livewire::actingAs($this->alice)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $character])
            ->assertDontSee('Le MJ demande votre avis');
    }
}
