<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ __('Mes récompenses') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Vos récompenses du Kickstarter et vos codes cadeaux.') }}</p>

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    @if ($me->isFounder() || $me->backer_reward)
        <section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="backer-title">
            <h2 id="backer-title" class="font-semibold">{{ __('Contributeur du Kickstarter') }} <x-founder-badge :user="$me" /></h2>
            <p class="mt-1 text-sm text-stone-600">{{ __('Merci d’avoir soutenu SagaWyn !') }}</p>
            <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                @if ($me->backer_reward)
                    <div><dt class="text-stone-500">{{ __('Votre récompense') }}</dt><dd>{{ $me->backer_reward }}</dd></div>
                @endif
                @if ($me->isFounder())
                    <div><dt class="text-stone-500">{{ __('Fondateur depuis') }}</dt><dd>{{ $me->founder_since->isoFormat('LL') }}</dd></div>
                    @if ($bonusMb > 0)
                        <div><dt class="text-stone-500">{{ __('Espace en plus') }}</dt><dd>{{ \App\Support\Plans\StorageUsage::format($bonusMb * 1024 * 1024) }}</dd></div>
                    @endif
                @endif
                <div>
                    <dt class="text-stone-500">{{ __('Premium') }}</dt>
                    <dd>
                        @if ($plan === 'premium' && $me->plan_ends_at === null)
                            {{ __('à vie') }}
                        @elseif ($plan === 'premium')
                            {{ __('jusqu’au :date', ['date' => $me->plan_ends_at->isoFormat('LL')]) }}
                        @else
                            {{ __('pas en cours') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </section>
    @endif

    @if ($gifts->isNotEmpty())
        @php($left = $gifts->reject->isRedeemed()->count())
        <section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="gifts-title">
            <h2 id="gifts-title" class="font-semibold">{{ __('Codes à offrir') }}</h2>
            <p class="mt-1 text-sm text-stone-600">{{ trans_choice(':count code sur :total reste à offrir. Envoyez le lien à la personne de votre choix : elle l’utilise depuis son compte, et vous voyez ici qui l’a reçu.|:count codes sur :total restent à offrir. Envoyez le lien à la personne de votre choix : elle l’utilise depuis son compte, et vous voyez ici qui l’a reçu.', $left, ['total' => $gifts->count()]) }}</p>
            <ul class="mt-4 space-y-3">
                @foreach ($gifts as $gift)
                    <li wire:key="gift-{{ $gift->id }}" class="rounded-lg border border-stone-200 p-3" x-data="{ copied: false }">
                        <div class="flex flex-wrap items-baseline gap-x-3">
                            <span class="font-mono text-sm">{{ $gift->code }}</span>
                            <span class="text-sm text-stone-600">{{ trans_choice(':count mois de Premium|:count mois de Premium', $gift->months) }}</span>
                            @if ($gift->isRedeemed())
                                <span class="ml-auto text-sm text-green-800">{{ __('Utilisé par :name le :date', ['name' => $gift->redeemer?->name ?? __('un compte supprimé'), 'date' => $gift->redeemed_at->isoFormat('LL')]) }}</span>
                            @else
                                <span class="ml-auto text-xs text-stone-500">{{ __('Disponible') }}</span>
                            @endif
                        </div>
                        @unless ($gift->isRedeemed())
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <label for="gift-link-{{ $gift->id }}" class="sr-only">{{ __('Lien du code cadeau') }}</label>
                                <input id="gift-link-{{ $gift->id }}" type="text" readonly value="{{ $gift->url() }}" x-ref="link" x-on:focus="$el.select()" class="field min-w-0 flex-1 font-mono text-xs">
                                <button type="button" class="btn-secondary" x-on:click="navigator.clipboard.writeText($refs.link.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
                                    <span x-show="! copied">{{ __('Copier le lien') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('Copié !') }}</span>
                                </button>
                                <button type="button" class="btn-secondary" x-show="navigator.share" x-cloak x-on:click="navigator.share({ title: @js(__('SagaWyn Premium offert')), url: $refs.link.value }).catch(() => {})">{{ __('Partager') }}</button>
                            </div>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </section>
    @elseif (! $me->isFounder() && ! $me->backer_reward)
        <p class="mb-6 text-sm text-stone-600">{{ __('Vous n’avez pas de récompense Kickstarter. Si quelqu’un vous a offert un code, utilisez-le ci-dessous.') }}</p>
    @endif

    <form wire:submit="redeem" class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">{{ __('Utiliser un code cadeau') }}</h2>
        <p class="mt-1 text-sm text-stone-600">{{ __('Les mois de Premium s’ajoutent à la suite de votre formule en cours.') }}</p>
        <div class="mt-3 flex flex-wrap items-start gap-2">
            <div class="min-w-0 flex-1">
                <label for="gift-code" class="sr-only">{{ __('Code cadeau') }}</label>
                <input id="gift-code" type="text" wire:model="code" class="field font-mono uppercase" placeholder="SW-XXXX-XXXX" autocomplete="off">
                @error('code') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">{{ __('Utiliser') }}</button>
        </div>
    </form>
</div>
