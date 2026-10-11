<?php

namespace App\Support;

use App\Models\GiftCode;
use App\Models\User;
use App\Support\Billing\Billing;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\DB;

/**
 * Codes cadeaux : l'administrateur en donne aux contributeurs du Kickstarter, qui les
 * partagent par lien. Utiliser un code ajoute ses mois de Premium à la suite de ceux en cours.
 */
class GiftCodes
{
    /** @return list<GiftCode> */
    public static function issue(?User $owner, int $count, int $months, ?string $note = null): array
    {
        return collect(range(1, $count))->map(fn () => tap((new GiftCode)->forceFill([
            'owner_id' => $owner?->id,
            'months' => $months,
            'note' => $note,
        ]))->save())->all();
    }

    /** Pourquoi ce compte ne peut pas utiliser de code ; null s'il le peut. */
    public static function problem(User $user, ?GiftCode $gift): ?string
    {
        if ($gift === null) {
            return __('Ce code n’existe pas. Vérifiez qu’il est bien recopié.');
        }

        if ($gift->isRedeemed()) {
            return $gift->redeemed_by === $user->id
                ? __('Vous avez déjà utilisé ce code.')
                : __('Ce code a déjà été utilisé.');
        }

        if ($user->is_admin) {
            return __('Un compte administrateur a déjà toutes les fonctions : gardez ce code pour quelqu’un d’autre.');
        }

        if (Plans::effective($user) === Plans::PREMIUM && $user->plan_ends_at === null) {
            return __('Votre Premium n’a pas de date de fin : gardez ce code pour quelqu’un d’autre.');
        }

        if (in_array($user->subscription_status, Billing::PAYING, true)) {
            return __('Vous avez un abonnement en cours : résiliez-le d’abord, ou offrez ce code à quelqu’un d’autre.');
        }

        return null;
    }

    /** Utilise le code ; renvoie le problème s'il y en a un, sinon null. */
    public static function redeem(User $user, string $code): ?string
    {
        return DB::transaction(function () use ($user, $code) {
            $gift = GiftCode::where('code', GiftCode::normalize($code))->lockForUpdate()->first();

            if ($problem = self::problem($user, $gift)) {
                return $problem;
            }

            $active = Plans::effective($user) === Plans::PREMIUM;
            $from = $active && $user->plan_ends_at?->isAfter(today()) ? $user->plan_ends_at : today();

            $user->forceFill([
                'plan' => Plans::PREMIUM,
                'plan_started_at' => $active ? $user->plan_started_at : today(),
                'plan_ends_at' => $from->copy()->addMonthsNoOverflow($gift->months),
            ])->save();

            $gift->forceFill(['redeemed_by' => $user->id, 'redeemed_at' => now()])->save();

            return null;
        });
    }
}
