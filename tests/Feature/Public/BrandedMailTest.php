<?php

namespace Tests\Feature\Public;

use App\Models\User;
use App\Notifications\EmailChanged;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BrandedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_emails_carry_the_loremundi_look(): void
    {
        $user = User::factory()->create();

        $html = (string) (new EmailChanged('nouvelle@exemple.test'))->toMail($user)->render();

        $this->assertStringContainsString('class="wordmark-mundi"', $html);
        $this->assertStringContainsString('Every world has a story.', $html);
        $this->assertStringContainsString('by Autistic Intelligence', $html);
        $this->assertStringNotContainsString('Laravel', $html);
        $this->assertStringContainsString('background-color: #2e5b6e', (string) (new ResetPassword('jeton'))->toMail($user)->render());
    }

    public function test_emails_are_written_in_the_recipients_language(): void
    {
        Notification::fake();
        $user = User::factory()->create(['preferences' => ['locale' => 'de']]);

        $user->notify(new ResetPassword('jeton'));

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification, array $channels, object $notifiable, ?string $locale) {
            return $locale === 'de';
        });
    }
}
