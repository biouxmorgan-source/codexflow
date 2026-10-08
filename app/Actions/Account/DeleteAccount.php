<?php

namespace App\Actions\Account;

use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\GameSystem;
use App\Models\User;
use App\Models\World;
use App\Support\Billing\Billing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Suppression d'un compte à la demande de son titulaire (RGPD) : ses campagnes, mondes et jeux
 * partent avec leurs fichiers ; dans les campagnes des autres, ses personnages restent sans joueur.
 */
class DeleteAccount
{
    public function handle(User $user): void
    {
        if (in_array($user->subscription_status, Billing::PAYING, true)) {
            throw ValidationException::withMessages(['deletePassword' => __('Résiliez d’abord votre abonnement, puis supprimez le compte.')]);
        }

        if ($user->is_admin && ! User::where('is_admin', true)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages(['deletePassword' => __('Vous êtes le seul administrateur : confiez d’abord l’administration à un autre compte.')]);
        }

        DB::transaction(function () use ($user) {
            $user->ownedCampaigns()->each(fn (Campaign $campaign) => $campaign->delete());

            $user->worlds()->each(function (World $world) {
                $world->entities()->each(fn (Entity $entity) => $entity->delete());
                $world->documents()->each(fn (Document $document) => $document->delete());
                $world->delete();
            });

            $user->gameSystems()->each(fn (GameSystem $gameSystem) => $gameSystem->delete());
            $user->pushSubscriptions()->delete();
            $user->notifications()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });
    }
}
