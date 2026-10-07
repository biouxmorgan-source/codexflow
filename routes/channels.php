<?php

use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Canal personnel : seule la personne elle-même l'écoute.
Broadcast::channel('users.{id}', fn (User $user, string $id) => $user->id === (int) $id);

// Fiche d'un personnage : son MJ et son joueur, selon la même règle que la page.
Broadcast::channel('characters.{character}', fn (User $user, PlayerCharacter $character) => $user->can('view', $character));
