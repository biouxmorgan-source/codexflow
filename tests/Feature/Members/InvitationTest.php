<?php

namespace Tests\Feature\Members;

use App\Enums\CampaignRole;
use App\Livewire\Journal\Index as JournalIndex;
use App\Livewire\Members\Index;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CampaignInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Enfants de la Peur']);
    }

    private function invite(array $data = []): CampaignInvitation
    {
        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->set('label', $data['label'] ?? 'Alex')
            ->set('email', $data['email'] ?? '')
            ->call('invite')
            ->assertHasNoErrors();

        return CampaignInvitation::latest('id')->firstOrFail();
    }

    public function test_a_new_player_joins_through_the_invitation_link(): void
    {
        $invitation = $this->invite(['email' => 'Alex@Exemple.fr']);
        $this->assertSame(CampaignRole::Player, $invitation->role);
        $this->assertSame('alex@exemple.fr', $invitation->email);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['Alex', $invitation->url(), 'Copier le lien']);

        // Le visiteur voit l'invitation, puis crée son compte et revient sur l'invitation.
        auth()->logout();
        $this->get($invitation->url())
            ->assertOk()
            ->assertSee(['Morgane vous invite', 'Les Enfants de la Peur', 'en tant que joueur'])
            ->assertSee(route('register', ['email' => 'alex@exemple.fr']), false);

        $this->get(route('register', ['email' => 'alex@exemple.fr']))->assertSee(['Vous êtes invité à rejoindre « Les Enfants de la Peur »', 'value="alex@exemple.fr"'], false);

        $this->post(route('register'), [
            'name' => 'Alex', 'email' => 'alex@exemple.fr', 'password' => 'motdepasse-solide-42', 'password_confirmation' => 'motdepasse-solide-42',
        ])->assertRedirect($invitation->url());

        $this->get($invitation->url())->assertSee('Rejoindre la campagne');
        $this->post(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('campaigns.index'));

        $alex = User::where('email', 'alex@exemple.fr')->sole();
        $this->assertSame(CampaignRole::Player, $this->campaign->roleOf($alex));
        $this->assertSame($alex->id, $invitation->fresh()->accepted_by);

        $this->get(route('campaigns.index'))->assertSee(['Vous avez rejoint la campagne « Les Enfants de la Peur ».', 'Joueur']);

        // Le joueur n'accède ni à l'espace MJ ni à la gestion des joueurs.
        $this->get(route('campaigns.show', $this->campaign))->assertForbidden();
        $this->get(route('members.index', $this->campaign))->assertForbidden();

        // Le lien ne sert qu'une fois.
        $this->flushSession();
        $this->actingAs(User::factory()->create());
        $this->get($invitation->url())->assertSee('déjà été utilisé');
        $this->post(route('invitations.accept', $invitation->token))->assertStatus(410);
    }

    public function test_an_existing_account_logs_in_and_comes_back_to_the_invitation(): void
    {
        $invitation = $this->invite();
        $sam = User::factory()->create(['email' => 'sam@exemple.fr']);

        auth()->logout();
        $this->get($invitation->url());
        $this->post(route('login'), ['email' => 'sam@exemple.fr', 'password' => 'password'])->assertRedirect($invitation->url());
        $this->post(route('invitations.accept', $invitation->token));

        $this->assertSame(CampaignRole::Player, $this->campaign->roleOf($sam));
    }

    public function test_expired_cancelled_and_unknown_links_do_not_work(): void
    {
        $expired = $this->invite();
        $expired->forceFill(['expires_at' => now()->subDay()])->save();
        $cancelled = $this->invite();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertDontSee($expired->url())
            ->call('revoke', $cancelled->id)
            ->assertDontSee($cancelled->url());

        $player = User::factory()->create();
        $this->actingAs($player)->get($expired->url())->assertSee('a expiré');
        $this->post(route('invitations.accept', $expired->token))->assertStatus(410);
        $this->get($cancelled->url())->assertSee('n\'existe pas');
        $this->get(route('invitations.show', 'inconnu'))->assertSee('n\'existe pas');

        $this->assertNull($this->campaign->roleOf($player));
    }

    public function test_a_member_keeps_their_role_and_the_link_stays_available(): void
    {
        $invitation = $this->invite();

        $this->actingAs($this->gm)->get($invitation->url())->assertSee('Vous faites déjà partie de cette campagne (maître de jeu)');
        $this->post(route('invitations.accept', $invitation->token))->assertRedirect(route('campaigns.show', $this->campaign));

        $this->assertSame(CampaignRole::GameMaster, $this->campaign->roleOf($this->gm));
        $this->assertNull($invitation->fresh()->accepted_at);
    }

    public function test_the_game_master_removes_a_player_and_the_journal_keeps_track(): void
    {
        $alex = User::factory()->create(['name' => 'Alex']);
        $invitation = $this->invite(['label' => 'Pour la table']);
        $this->actingAs($alex)->post(route('invitations.accept', $invitation->token));

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['Morgane', 'Maître de jeu', 'Alex', 'Joueur'])
            ->call('remove', $alex->id)
            ->assertDontSee($alex->email)
            ->call('remove', $this->gm->id)
            ->assertForbidden();

        $this->assertNull($this->campaign->roleOf($alex));
        $this->assertSame(CampaignRole::GameMaster, $this->campaign->roleOf($this->gm));
        $this->assertSame(['created', 'deleted'], ActivityLog::where('subject_type', 'member')->orderBy('id')->pluck('event')->all());

        Livewire::actingAs($this->gm)->test(JournalIndex::class, ['campaign' => $this->campaign])
            ->assertSee(['a ajouté', 'a retiré', 'membre', 'Alex', 'Rôle', 'Joueur']);
    }

    public function test_only_the_game_master_manages_players(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $this->actingAs($player)->get(route('members.index', $this->campaign))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('members.index', $this->campaign))->assertForbidden();
        $this->post(route('invitations.accept', 'x'))->assertStatus(410);

        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))->assertSee(route('members.index', $this->campaign));
    }
}
