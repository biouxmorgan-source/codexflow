<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ __('Préférences') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Réglages de votre compte, sur tous vos appareils.') }}</p>

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    @php($me = auth()->user())
    @php($plan = \App\Support\Plans\Plans::effective($me))
    @php($limit = \App\Support\Plans\Plans::storageLimit($me))
    @php($max = \App\Support\Plans\Plans::maxCampaigns($me))
    <section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="plan-title">
        <h2 id="plan-title" class="font-semibold">{{ __('Votre formule : :plan', ['plan' => \App\Support\Plans\Plans::labels()[$plan]]) }}</h2>
        <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-3">
            <div><dt class="text-stone-500">{{ __('Stockage') }}</dt><dd>{{ \App\Support\Plans\StorageUsage::format(\App\Support\Plans\StorageUsage::bytes($me)) }} / {{ $limit === null ? __('illimité') : \App\Support\Plans\StorageUsage::format($limit) }}</dd></div>
            <div><dt class="text-stone-500">{{ __('Campagnes en tant que MJ') }}</dt><dd>{{ $me->ownedCampaigns()->count() }} / {{ $max ?? __('illimité') }}</dd></div>
            @if ($plan === 'trial')
                <div><dt class="text-stone-500">{{ __('Fin de l’essai') }}</dt><dd>{{ \App\Support\Plans\Plans::trialEndsAt($me)->isoFormat('LL') }}</dd></div>
            @endif
            @if ($me->plan_ends_at && $plan === 'premium')
                <div><dt class="text-stone-500">{{ __('Fin de l’abonnement') }}</dt><dd>{{ $me->plan_ends_at->isoFormat('LL') }}</dd></div>
            @endif
        </dl>
        @if ($plan === 'free' && ! $me->trial_started_at && \App\Support\Plans\Plans::trialWeeks() > 0)
            <p class="mt-2 text-sm">{{ trans_choice('Essai offert : :count semaine avec toutes les fonctions, à partir de votre première campagne en tant que MJ.|Essai offert : :count semaines avec toutes les fonctions, à partir de votre première campagne en tant que MJ.', \App\Support\Plans\Plans::trialWeeks()) }}</p>
        @endif
        @php($billing = app(\App\Support\Billing\Billing::class))
        @if ($billing->configured() && ! $me->is_admin)
            <div class="mt-4 flex flex-wrap items-center gap-2">
                @if (in_array($me->subscription_status, \App\Support\Billing\Billing::PAYING, true) && $plan === 'premium')
                    @if ($me->subscription_status === 'past_due')
                        <p class="w-full text-sm text-red-700" role="alert">{{ __('Le dernier paiement n’est pas passé : mettez à jour votre moyen de paiement.') }}</p>
                    @endif
                @else
                    @foreach ($billing->prices() as $interval => $price)
                        <form method="POST" action="{{ route('billing.checkout', $interval) }}">
                            @csrf
                            <button type="submit" class="btn-primary">{{ __('Passer Premium : :interval', ['interval' => \App\Support\Billing\Billing::intervals()[$interval]]) }}</button>
                        </form>
                    @endforeach
                @endif
                @if ($me->stripe_customer_id)
                    <form method="POST" action="{{ route('billing.portal') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">{{ __('Gérer mon abonnement et mes factures') }}</button>
                    </form>
                @endif
            </div>
            <p class="mt-2 text-xs text-stone-500">{{ __('Paiement sécurisé par Stripe : LoreMundi ne voit jamais votre carte. Résiliable à tout moment, le premium dure jusqu’à la fin de la période payée.') }}</p>
        @endif
        <p class="mt-2 text-xs text-stone-500">{{ __('Jouer, être co-MJ ou spectateur dans la campagne d’un autre ne compte pas : vous profitez alors de la formule de son MJ.') }}</p>
    </section>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div>
            <label for="locale" class="label">{{ __('Langue') }}</label>
            <select id="locale" wire:model="locale" class="field max-w-xs">
                <option value="">{{ __('Comme le navigateur') }}</option>
                @foreach ($locales as $code => $name)
                    <option value="{{ $code }}" lang="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <fieldset>
            <legend class="label">{{ __('Thème') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['theme'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $theme === $value, 'border-stone-300' => $theme !== $value])>
                        <input type="radio" wire:model.live="theme" value="{{ $value }}" class="sr-only">
                        <span aria-hidden="true">{{ ['system' => '🖥️', 'light' => '☀️', 'dark' => '🌙'][$value] }}</span> {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="label">{{ __("Couleur d'accent") }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['accent'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $accent === $value, 'border-stone-300' => $accent !== $value])>
                        <input type="radio" wire:model.live="accent" value="{{ $value }}" class="sr-only">
                        <span class="h-4 w-4 rounded-full" style="background: {{ ['codex' => '#2e5b6e', 'blue' => '#2f5d9c', 'green' => '#3d6b45', 'violet' => '#5b4a8a', 'red' => '#7a2e3a'][$value] }}" aria-hidden="true"></span>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="label">{{ __('Taille du texte') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['size'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $size === $value, 'border-stone-300' => $size !== $value])>
                        <input type="radio" wire:model.live="size" value="{{ $value }}" class="sr-only">
                        <span @class(['text-sm' => $value === 'normal', 'text-base' => $value === 'large', 'text-lg' => $value === 'xlarge'])>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
    </form>

    <livewire:account.profile />

    <livewire:account.ai-key />

    <livewire:account.security />

</div>
