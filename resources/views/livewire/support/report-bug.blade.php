<div @class(['max-w-2xl', 'mx-auto px-4 pb-16' => auth()->guest()])>
    <h1 class="text-2xl font-semibold">{{ __('Signaler un problème') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __("Quelque chose ne marche pas, ou pas comme vous l'attendiez ? Décrivez-le ici : votre message arrive directement à l'équipe LoreMundi.") }}</p>

    @if ($sent)
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ __('Merci ! Votre signalement a bien été envoyé.') }}</p>
    @endif

    <form wire:submit="send" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div>
            <label for="message" class="label">{{ __('Que s’est-il passé ?') }}</label>
            <textarea id="message" wire:model="message" rows="7" class="field" placeholder="{{ __('Ce que vous faisiez, ce qui s’est passé, ce que vous attendiez…') }}"></textarea>
            @error('message') <p class="error">{{ $message }}</p> @enderror
        </div>

        @guest
            <div>
                <label for="contact" class="label">{{ __('Votre adresse e-mail') }}</label>
                <input id="contact" type="email" wire:model="contact" class="field" autocomplete="email" required>
                <p class="mt-1 text-xs text-stone-500">{{ __('Pour vous répondre, seulement.') }}</p>
                @error('contact') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>
        @endguest

        @if ($page)
            <p class="text-sm text-stone-600">{{ __('Page concernée :') }} <code class="break-all">{{ $page }}</code></p>
        @endif

        <p class="text-xs text-stone-500">{{ __('Sont joints automatiquement : la page, votre navigateur, la langue et la version de LoreMundi. Rien d’autre de vos campagnes n’est transmis.') }}</p>

        <button type="submit" class="btn-primary">{{ __('Envoyer') }}</button>
    </form>
</div>
