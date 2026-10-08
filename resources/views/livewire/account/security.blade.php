<div class="mt-8 space-y-8">
    <section class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="two-factor-title">
        <div>
            <h2 id="two-factor-title" class="text-lg font-semibold">{{ __('Double authentification') }}</h2>
            <p class="mt-1 text-sm text-stone-600">{{ __('Facultatif. À chaque connexion, en plus du mot de passe, un code à 6 chiffres donné par une application d’authentification de votre téléphone (Google Authenticator, Microsoft Authenticator, Aegis…).') }}</p>
        </div>

        @if ($enabled)
            <p class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ __('La double authentification est activée.') }}</p>
        @elseif ($pending)
            <div class="space-y-3">
                <p class="text-sm">{{ __('Scannez ce code avec votre application, puis saisissez le code à 6 chiffres qu’elle affiche.') }}</p>
                <div class="inline-block rounded-lg bg-white p-3">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>
                <p class="text-xs text-stone-500">{{ __('Ou saisissez cette clé à la main :') }} <code class="select-all font-mono">{{ decrypt(auth()->user()->two_factor_secret) }}</code></p>
                <form wire:submit="confirm" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="two-factor-code" class="label">{{ __('Code') }}</label>
                        <input id="two-factor-code" type="text" inputmode="numeric" autocomplete="one-time-code" wire:model="code" class="field w-40">
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Activer') }}</button>
                </form>
                @error('code') <p class="error">{{ $message }}</p> @enderror
            </div>
        @endif

        @if ($enabled && $showRecoveryCodes)
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-medium">{{ __('Vos codes de secours') }}</p>
                <p class="mt-1">{{ __('Gardez-les en lieu sûr : chacun permet une connexion si vous perdez votre téléphone. Ils ne seront plus affichés.') }}</p>
                <ul class="mt-2 grid grid-cols-2 gap-1 font-mono">
                    @foreach (auth()->user()->recoveryCodes() as $recovery)
                        <li class="select-all">{{ $recovery }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless ($pending)
            <form wire:submit="{{ $enabled ? 'disable' : 'enable' }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="two-factor-password" class="label">{{ __('Mot de passe actuel') }}</label>
                    <input id="two-factor-password" type="password" autocomplete="current-password" wire:model="password" class="field">
                </div>
                @if ($enabled)
                    <button type="submit" class="btn-secondary">{{ __('Désactiver') }}</button>
                    <button type="button" wire:click="regenerate" class="btn-secondary">{{ __('Nouveaux codes de secours') }}</button>
                @else
                    <button type="submit" class="btn-primary">{{ __('Mettre en place') }}</button>
                @endif
            </form>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        @endunless
    </section>

    <section class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="my-data-title">
        <div>
            <h2 id="my-data-title" class="text-lg font-semibold">{{ __('Mes données') }}</h2>
            <p class="mt-1 text-sm text-stone-600">{{ __('Téléchargez ce que LoreMundi garde sur vous : compte, connexions, campagnes, personnages, notes, messages et signalements. Le contenu complet d’une campagne se télécharge depuis la campagne.') }}</p>
        </div>
        <a href="{{ route('account.data') }}" class="btn-secondary inline-block">{{ __('Télécharger mes données') }}</a>
        <p class="text-sm">
            <a href="{{ route('privacy') }}" class="link" wire:navigate>{{ __('Confidentialité et mentions légales') }}</a>
        </p>

        {{-- wire:ignore.self : le volet reste ouvert après une réponse du serveur (refus, erreur). --}}
        <details class="rounded-lg border border-red-200 p-4" wire:ignore.self>
            <summary class="cursor-pointer text-sm font-medium text-red-700">{{ __('Supprimer mon compte') }}</summary>
            <form wire:submit="deleteAccount" class="mt-4 space-y-3">
                <p class="text-sm text-stone-700">{{ __('La suppression est définitive. Vos campagnes, mondes et jeux sont effacés avec leurs fichiers, pour tous leurs joueurs. Dans les campagnes des autres, vos personnages restent, sans joueur, mais vos messages sont effacés.') }}</p>
                @if ($owned > 0)
                    <p class="text-sm font-medium text-red-700">{{ trans_choice('{1} Vous êtes propriétaire d’une campagne : pensez à la télécharger avant.|[2,*] Vous êtes propriétaire de :count campagnes : pensez à les télécharger avant.', $owned, ['count' => $owned]) }}</p>
                @endif
                <div>
                    <label for="delete-password" class="label">{{ __('Mot de passe actuel') }}</label>
                    <input id="delete-password" type="password" autocomplete="current-password" wire:model="deletePassword" class="field">
                    @error('deletePassword') <p class="error">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="deleteConfirmed" class="rounded border-stone-300">
                    {{ __('Je comprends que tout sera effacé.') }}
                </label>
                @error('deleteConfirmed') <p class="error">{{ $message }}</p> @enderror
                <button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">{{ __('Supprimer définitivement') }}</button>
            </form>
        </details>
    </section>
</div>
