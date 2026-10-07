<?php

namespace Tests\Feature\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Livewire\Characters\Index;
use App\Livewire\Characters\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerAdditionsTest extends TestCase
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
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $this->alex->id]);
    }

    private function sheet(User $user)
    {
        return Livewire::actingAs($user)->test(Show::class, ['campaign' => $this->campaign, 'character' => $this->harvey]);
    }

    public function test_a_player_notes_a_knowledge_and_the_game_master_is_told(): void
    {
        $this->sheet($this->alex)
            ->call('openAdd', 'information')
            ->set('addTitle', 'Le professeur cache quelque chose')
            ->set('addBody', 'Il a menti sur son voyage au Caire.')
            ->call('addOwn')
            ->assertHasNoErrors()
            ->assertSee('Le professeur cache quelque chose')
            ->assertSee('noté par le joueur')
            ->assertSee('Le joueur a noté :');

        $grant = $this->harvey->grants()->sole();
        $this->assertTrue($grant->added_by_player);
        $this->assertFalse($grant->isPending());
        $this->assertSame('Harvey a noté une connaissance : « Le professeur cache quelque chose ».', $this->gm->notifications()->sole()->data['text']);
    }

    public function test_an_object_added_by_the_player_waits_for_the_game_master(): void
    {
        $this->sheet($this->alex)
            ->call('openAdd', 'possession')
            ->set('addTitle', 'Revolver .38')
            ->set('addQuantity', 1)
            ->call('addOwn')
            ->assertSee('en attente du MJ')
            ->assertDontSee('Valider');

        $revolver = $this->harvey->grants()->sole();
        $this->assertTrue($revolver->isPending());
        $this->assertSame('Harvey a ajouté un objet à valider : « Revolver .38 ».', $this->gm->notifications()->sole()->data['text']);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->assertSee('1 objet à valider');

        // Le joueur ne valide pas lui-même.
        $this->sheet($this->alex)->call('validateGrant', $revolver->id)->assertForbidden();

        $this->sheet($this->gm)->assertSee('Valider')->call('validateGrant', $revolver->id)->assertDontSee('en attente du MJ');
        $this->assertFalse($revolver->fresh()->isPending());
        $this->assertContains('Le MJ a validé « Revolver .38 ».', $this->alex->notifications->pluck('data.text'));
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->assertDontSee('objet à valider');
    }

    public function test_the_player_erases_only_what_they_added_and_not_on_a_locked_sheet(): void
    {
        $this->sheet($this->alex)->call('openAdd', 'possession')->set('addTitle', 'Lampe')->call('addOwn');
        $lamp = $this->harvey->grants()->sole();

        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Carnet du MJ']);
        $given = $this->harvey->grants()->where('title', 'Carnet du MJ')->sole();

        $this->sheet($this->alex)->call('removeOwn', $given->id)->assertForbidden();
        $this->sheet($this->alex)->call('removeOwn', $lamp->id);
        $this->assertModelMissing($lamp);

        $this->harvey->update(['locked' => true]);
        $this->sheet($this->alex)->assertDontSee('+ Ajouter un objet')->call('openAdd', 'information')->assertForbidden();
    }

    public function test_a_pending_object_cannot_be_given_away(): void
    {
        $sam = User::factory()->create();
        $this->campaign->members()->attach($sam, ['role' => CampaignRole::Player->value]);
        $jack = $this->campaign->playerCharacters()->create(['entity_id' => Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Jack'])->id, 'user_id' => $sam->id]);

        $this->sheet($this->alex)->call('openAdd', 'possession')->set('addTitle', 'Lingot d\'or')->call('addOwn');
        $gold = $this->harvey->grants()->sole();

        $this->sheet($this->alex)->call('startExchange', $gold->id)->set('exchangeTo', (string) $jack->id)->call('exchange')->assertHasErrors('exchange');
        $this->assertSame($this->harvey->id, $gold->fresh()->player_character_id);
    }

    public function test_the_player_is_told_in_their_own_language(): void
    {
        $this->alex->forceFill(['preferences' => ['locale' => 'en']])->save();
        $this->gm->forceFill(['preferences' => ['browser_locale' => 'de']])->save();

        $this->sheet($this->alex)
            ->call('openAdd', 'possession')
            ->set('addTitle', 'Revolver .38')
            ->set('addQuantity', 1)
            ->call('addOwn');

        // Chacun reçoit le texte dans sa langue, quelle que soit celle de l'auteur.
        $this->assertSame(
            __(':name a ajouté un objet à valider : :label.', ['name' => 'Harvey', 'label' => __('« :text »', ['text' => 'Revolver .38'], 'de')], 'de'),
            $this->gm->notifications()->sole()->data['text'],
        );

        $this->sheet($this->gm)->call('validateGrant', $this->harvey->grants()->sole()->id);
        $text = $this->alex->notifications()->latest('id')->first()->data['text'];
        $this->assertSame(__('Le MJ a validé « :label ».', ['label' => 'Revolver .38'], 'en'), $text);
        $this->assertStringNotContainsString('a validé', $text);
    }
}
