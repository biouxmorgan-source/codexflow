<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Message;
use App\Support\Notify;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Liens « Messages » et cloche de l'en-tête, avec leurs compteurs de non-lus,
 * mis à jour en direct quand quelque chose arrive pour la personne connectée.
 */
class HeaderBadges extends Component
{
    #[Locked]
    public ?Campaign $campaign = null;

    #[Locked]
    public string $active = '';

    public function mount(?Campaign $campaign = null): void
    {
        $this->campaign = $campaign !== null && auth()->user()->can('view', $campaign) ? $campaign : null;
        $this->active = match (true) {
            request()->routeIs('messages.*') => 'messages',
            request()->routeIs('notifications.*') => 'notifications',
            default => '',
        };
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.auth()->id().',.activity' => '$refresh',
            'messages-read' => '$refresh',
        ];
    }

    public function render()
    {
        return view('livewire.header-badges', [
            'unreadMessages' => $this->campaign ? Message::unreadCount(auth()->user(), $this->campaign) : 0,
            'unreadNotifications' => Notify::unreadCount(auth()->user()),
        ]);
    }
}
