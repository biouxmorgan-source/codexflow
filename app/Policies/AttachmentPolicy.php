<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Les droits suivent ceux de la fiche. Lot 2 : un joueur ne verra que la zone publique révélée.
 */
class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool|Response
    {
        return $user->can('view', $attachment->entity) ?: Response::denyAsNotFound();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->can('update', $attachment->entity);
    }
}
