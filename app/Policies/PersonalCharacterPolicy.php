<?php

namespace App\Policies;

use App\Models\PersonalCharacter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Une feuille personnelle n'existe que pour son propriétaire : pour tout autre compte, elle n'existe pas. */
class PersonalCharacterPolicy
{
    public function view(User $user, PersonalCharacter $character): Response
    {
        return $character->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, PersonalCharacter $character): Response
    {
        return $this->view($user, $character);
    }

    public function delete(User $user, PersonalCharacter $character): Response
    {
        return $this->view($user, $character);
    }
}
