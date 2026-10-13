{{-- Compte démo (avant le Kickstarter) : ce que montre la démonstration, et la suite. --}}
<div data-demo-banner role="note" class="mb-6 rounded-xl border border-flow/30 bg-flow/10 px-4 py-3 text-sm"
    x-data="{ message: null, timer: null }"
    x-on:demo-locked.window="message = $event.detail.message; clearTimeout(timer); timer = setTimeout(() => message = null, 8000)">
    <p class="font-semibold text-flow">{{ __('Version de démonstration') }}</p>
    <p class="mt-1">{{ __('Visitez toutes les fonctions de SagaWyn sur la campagne d’exemple. Son contenu se consulte mais ne se modifie qu’en partie : lancer une séance, prendre des notes, déplacer les jetons de la carte. Vous pouvez aussi créer une petite campagne à vous.') }}</p>
    <p class="mt-1">{{ __('Une campagne de financement participatif va bientôt être lancée : vous serez prévenu par courriel.') }}</p>
    <p x-show="message" x-cloak x-text="message" role="alert" class="fixed inset-x-4 bottom-4 z-50 rounded-lg bg-flow px-4 py-3 font-medium text-on-accent shadow-lg sm:left-auto sm:max-w-md"></p>
</div>
