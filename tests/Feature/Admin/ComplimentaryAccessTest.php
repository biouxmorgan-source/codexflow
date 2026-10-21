<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Accès offert : l'administrateur repère les comptes qu'il ne fait pas payer (amis, testeurs). */
class ComplimentaryAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email' => 'morgan@example.com']);
    }

    public function test_the_administrator_marks_an_account_as_complimentary_with_a_note(): void
    {
        $friend = User::factory()->create(['email' => 'ami@example.com']);

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $friend->id)
            ->assertSet('isComplimentary', false)
            ->set('isComplimentary', true)
            ->set('complimentaryNote', '  ami  ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Accès offert : ami');

        $friend->refresh();
        $this->assertTrue($friend->complimentary);
        $this->assertSame('ami', $friend->complimentary_note);
        // Un simple repère : la formule ne change pas.
        $this->assertSame('free', $friend->plan);
    }

    public function test_unticking_removes_the_note_too(): void
    {
        $friend = User::factory()->create(['complimentary' => true, 'complimentary_note' => 'testeur']);

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $friend->id)
            ->assertSet('isComplimentary', true)
            ->assertSet('complimentaryNote', 'testeur')
            ->set('isComplimentary', false)
            ->call('save');

        $friend->refresh();
        $this->assertFalse($friend->complimentary);
        $this->assertNull($friend->complimentary_note);
    }

    public function test_the_list_filters_and_counts_complimentary_accounts(): void
    {
        User::factory()->create(['email' => 'ami@example.com', 'complimentary' => true]);
        User::factory()->create(['email' => 'client@example.com']);

        $page = Livewire::actingAs($this->admin)->test(Users::class);
        $this->assertSame(1, $page->instance()->totals()['complimentary']);

        $page->assertSee('client@example.com')
            ->set('show', 'complimentary')
            ->assertSee('ami@example.com')
            ->assertDontSee('client@example.com');
    }

    public function test_the_note_is_limited_in_length(): void
    {
        $friend = User::factory()->create();

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $friend->id)
            ->set('isComplimentary', true)
            ->set('complimentaryNote', str_repeat('a', 121))
            ->call('save')
            ->assertHasErrors('complimentaryNote');
    }
}
