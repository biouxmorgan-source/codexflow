<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Centre de notifications de la personne connectée : chacun ne reçoit que les siennes,
 * MJ comme joueur, toutes campagnes confondues.
 */
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'non-lues', except: false)]
    public bool $unreadOnly = false;

    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        return auth()->user()->notifications()
            ->when($this->unreadOnly, fn ($q) => $q->whereNull('read_at'))
            ->paginate(30);
    }

    public function updatedUnreadOnly(): void
    {
        $this->resetPage();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        unset($this->notifications);
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    public function render()
    {
        return view('livewire.notifications.index')->title('Notifications');
    }
}
