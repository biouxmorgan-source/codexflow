<?php

namespace Tests\Feature\Notifications;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\ReceivedPopup;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Ce que le personnage reçoit s'affiche aussitôt, sans passer par les notifications. */
class ReceivedPopupTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['last_seen_version' => Changelog::version()]);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Enfants de la Peur']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $this->alex->id]);
    }

    public function test_a_reveal_pops_up_on_the_players_page_until_dismissed(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Professeur Armitage', 'summary' => 'Bibliothécaire de Miskatonic', 'gm_notes' => 'Membre du culte']);

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Le bureau a été fouillé', 'body' => 'Les tiroirs sont vides.']);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $npc->id]);

        // Sur n'importe quelle page, la première réception s'affiche avec son contenu.
        $this->actingAs($this->alex)->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee(['Le bureau a été fouillé', 'Les tiroirs sont vides.', '2 en attente']);

        // Le MJ, qui révèle, ne voit pas la fenêtre.
        $this->actingAs($this->gm)->get(route('campaigns.index'))->assertDontSee('Les tiroirs sont vides.');

        $popup = Livewire::actingAs($this->alex)->test(ReceivedPopup::class);
        $first = $this->alex->unreadNotifications()->reorder('created_at')->first();
        $popup->call('dismiss', $first->id)
            ->assertSee(['Professeur Armitage', 'Bibliothécaire de Miskatonic'])
            ->assertDontSee('Membre du culte');

        $second = $this->alex->unreadNotifications()->sole();
        $popup->call('open', $second->id)->assertRedirect(route('characters.entity', [$this->campaign, $this->harvey, $npc]));
        $this->assertSame(0, $this->alex->unreadNotifications()->count());

        $this->actingAs($this->alex)->get(route('campaigns.index'))->assertDontSee('en attente')->assertDontSee('Les tiroirs sont vides.');
    }

    public function test_an_item_taken_back_before_being_seen_is_not_shown(): void
    {
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Revolver .38']);
        GiveToCharacters::revoke($this->harvey->grants()->sole());

        $this->actingAs($this->alex)->get(route('campaigns.index'))->assertOk()->assertDontSee('Revolver .38');
        $this->assertSame(1, $this->alex->unreadNotifications()->count(), 'Reste la notification « repris », dans la cloche.');
    }
}
