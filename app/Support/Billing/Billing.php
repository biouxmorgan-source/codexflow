<?php

namespace App\Support\Billing;

use App\Models\User;
use App\Support\Plans\Plans;
use Illuminate\Support\Carbon;
use Stripe\StripeClient;

/**
 * Abonnement premium payé par Stripe : la page de paiement et le portail client sont
 * hébergés par Stripe, CodexFlow ne voit jamais de carte. Le webhook règle la formule.
 */
class Billing
{
    /** Statuts Stripe qui donnent le premium jusqu'à la fin de la période payée. */
    public const PAYING = ['active', 'trialing', 'past_due'];

    /** Statuts définitifs : l'abonnement est terminé. */
    public const ENDED = ['canceled', 'unpaid', 'incomplete_expired'];

    public function configured(): bool
    {
        return filled(config('services.stripe.secret')) && $this->prices() !== [];
    }

    /** @return array<string, string> intervalle (monthly, yearly) => identifiant du prix Stripe */
    public function prices(): array
    {
        return array_filter([
            'monthly' => config('services.stripe.prices.monthly'),
            'yearly' => config('services.stripe.prices.yearly'),
        ]);
    }

    /** @return array<string, string> */
    public static function intervals(): array
    {
        return ['monthly' => __('Mensuel'), 'yearly' => __('Annuel')];
    }

    /** Adresse de la page de paiement Stripe pour passer premium. */
    public function checkoutUrl(User $user, string $interval): string
    {
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $this->customerId($user),
            'client_reference_id' => (string) $user->id,
            'line_items' => [['price' => $this->prices()[$interval], 'quantity' => 1]],
            'subscription_data' => ['metadata' => ['user_id' => (string) $user->id]],
            'allow_promotion_codes' => true,
            'locale' => 'auto',
            'success_url' => route('billing.success'),
            'cancel_url' => route('preferences'),
        ]);

        return $session->url;
    }

    /** Adresse du portail client Stripe : moyen de paiement, factures, résiliation. */
    public function portalUrl(User $user): string
    {
        return $this->client()->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('preferences'),
        ])->url;
    }

    /**
     * Applique l'état d'un abonnement reçu par webhook : premium jusqu'à la fin de la période
     * payée, ou fin de l'abonnement. La formule échue redevient gratuite d'elle-même.
     *
     * @param  array<string, mixed>  $subscription
     */
    public function apply(array $subscription): ?User
    {
        $user = $this->owner($subscription);

        if (! $user) {
            return null;
        }

        $status = (string) ($subscription['status'] ?? '');

        // Un abonnement terminé ne revit pas : un événement plus ancien arrivé en retard est ignoré.
        if ($user->stripe_subscription_id === $subscription['id'] && in_array($user->subscription_status, self::ENDED, true)) {
            return $user;
        }

        if (! in_array($status, [...self::PAYING, ...self::ENDED], true)) {
            return $user; // incomplet : rien n'est encore payé
        }

        $user->forceFill([
            'stripe_customer_id' => $subscription['customer'] ?? $user->stripe_customer_id,
            'stripe_subscription_id' => $subscription['id'],
            'subscription_status' => $status,
        ]);

        if (in_array($status, self::PAYING, true)) {
            $end = $subscription['cancel_at'] ?? $subscription['current_period_end'] ?? $subscription['items']['data'][0]['current_period_end'] ?? null;
            $user->forceFill([
                'plan' => Plans::PREMIUM,
                'plan_started_at' => isset($subscription['start_date']) ? Carbon::createFromTimestamp($subscription['start_date']) : now(),
                'plan_ends_at' => $end ? Carbon::createFromTimestamp($end) : null,
            ]);
        } else {
            $ended = $subscription['ended_at'] ?? null;
            $user->forceFill(['plan_ends_at' => $ended ? Carbon::createFromTimestamp($ended) : now()]);
        }

        $user->save();

        return $user;
    }

    /** Relie le client Stripe au compte à la fin du paiement. */
    public function linkCustomer(string $userId, ?string $customerId): void
    {
        if ($customerId) {
            User::whereKey($userId)->whereNull('stripe_customer_id')->update(['stripe_customer_id' => $customerId]);
        }
    }

    /** @param  array<string, mixed>  $subscription */
    private function owner(array $subscription): ?User
    {
        $id = $subscription['metadata']['user_id'] ?? null;

        return ($id ? User::find($id) : null)
            ?? (isset($subscription['customer']) ? User::where('stripe_customer_id', $subscription['customer'])->first() : null);
    }

    private function customerId(User $user): string
    {
        if (! $user->stripe_customer_id) {
            $customer = $this->client()->customers->create([
                'email' => $user->email,
                'metadata' => ['user_id' => (string) $user->id],
            ]);
            $user->forceFill(['stripe_customer_id' => $customer->id])->save();
        }

        return $user->stripe_customer_id;
    }

    private function client(): StripeClient
    {
        return new StripeClient((string) config('services.stripe.secret'));
    }
}
