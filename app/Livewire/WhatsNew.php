<?php

namespace App\Livewire;

use App\Support\Changelog;
use Livewire\Component;

/**
 * Fenêtre « Quoi de neuf », montrée une fois après chaque mise à jour : en détail à
 * l'administrateur, en version courte et pour les versions notables aux autres comptes.
 */
class WhatsNew extends Component
{
    public function dismiss(): void
    {
        auth()->user()->forceFill(['last_seen_version' => Changelog::version()])->save();
    }

    public function render()
    {
        return view('livewire.whats-new', [
            'versions' => Changelog::unseenSince(auth()->user()->last_seen_version, Changelog::detailedFor(auth()->user())),
        ]);
    }
}
