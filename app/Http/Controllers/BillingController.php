<?php

namespace App\Http\Controllers;

use App\Support\Billing\Billing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class BillingController extends Controller
{
    public function checkout(Request $request, Billing $billing, string $interval): RedirectResponse
    {
        abort_unless($billing->configured() && isset($billing->prices()[$interval]), 404);
        abort_if($request->user()->is_admin, 403);

        return redirect()->away($billing->checkoutUrl($request->user(), $interval));
    }

    public function portal(Request $request, Billing $billing): RedirectResponse
    {
        abort_unless($billing->configured() && $request->user()->stripe_customer_id, 404);

        return redirect()->away($billing->portalUrl($request->user()));
    }

    public function success(): RedirectResponse
    {
        return redirect()->route('preferences')
            ->with('status', __('Merci ! Votre abonnement premium s’active dès que Stripe confirme le paiement, en général en quelques secondes.'));
    }

    /** Stripe annonce les paiements, renouvellements et résiliations ; la signature est vérifiée. */
    public function webhook(Request $request, Billing $billing): Response
    {
        $secret = (string) config('services.stripe.webhook_secret');
        abort_if($secret === '', 404);

        try {
            $event = Webhook::constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'), $secret);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response('Invalid signature', 400);
        }

        // En transaction : si le traitement échoue, Stripe renverra l'événement.
        DB::transaction(function () use ($event, $billing) {
            $fresh = DB::table('stripe_events')->insertOrIgnore(['id' => $event->id, 'type' => $event->type, 'processed_at' => now()]);

            if (! $fresh) {
                return;
            }

            $object = $event->data->object->toArray();

            match ($event->type) {
                'checkout.session.completed' => $billing->linkCustomer((string) ($object['client_reference_id'] ?? ''), $object['customer'] ?? null),
                'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted' => $billing->apply($object),
                default => null,
            };
        });

        return response('OK');
    }
}
