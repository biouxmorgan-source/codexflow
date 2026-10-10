<div class="mx-auto max-w-2xl">
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        @if ($this->character)
            › <a href="{{ route('characters.show', [$campaign, $this->character]) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        @else
            › {{ $campaign->name }}
        @endif
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Votre avis') }}</h1>
    <p class="mb-6 text-stone-600">{{ $feedbackRequest->playSession ? __('Le MJ aimerait savoir ce que vous avez pensé de cette séance : :session.', ['session' => $feedbackRequest->subject()]) : __('Le MJ aimerait savoir ce que vous pensez de la campagne.') }}</p>

    @if ($this->answered)
        <div class="rounded-xl border border-stone-200 bg-white p-6 text-center shadow-sm" role="status">
            <p class="text-3xl text-amber-500" aria-hidden="true">★</p>
            <p class="mt-2 font-semibold">{{ __('Merci pour votre avis !') }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ __('Le MJ en tiendra compte pour la suite.') }}</p>
        </div>
    @elseif (! $feedbackRequest->isOpen())
        <div class="rounded-xl border border-stone-200 bg-white p-6 text-center text-stone-600 shadow-sm">{{ __('Cette demande est close.') }}</div>
    @else
        <form wire:submit="save" class="space-y-5 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">
                {{ $feedbackRequest->anonymous ? __('Avis anonyme : le MJ verra votre note et vos commentaires, pas votre nom.') : __('Avis signé : le MJ verra votre nom avec votre réponse.') }}
            </p>

            <fieldset x-data="{ hover: 0 }">
                <legend class="label">{{ __('Votre note') }}</legend>
                <div class="flex gap-1" x-on:mouseleave="hover = 0">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer" x-on:mouseenter="hover = {{ $i }}">
                            <input type="radio" wire:model="rating" name="rating" value="{{ $i }}" class="peer sr-only">
                            <span class="block rounded px-0.5 text-4xl leading-none peer-focus-visible:outline-2 peer-focus-visible:outline-codex"
                                x-bind:class="(hover || $wire.rating || 0) >= {{ $i }} ? 'text-amber-500' : 'text-stone-300'" aria-hidden="true">★</span>
                            <span class="sr-only">{{ trans_choice(':count étoile|:count étoiles', $i) }}</span>
                        </label>
                    @endfor
                </div>
                @error('rating') <p class="error">{{ $message }}</p> @enderror
            </fieldset>

            <div>
                <label for="liked" class="label">{{ __('Ce qui vous a plu') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                <textarea id="liked" wire:model="liked" rows="3" maxlength="2000" class="field" placeholder="{{ __('Une scène, un personnage, une ambiance…') }}"></textarea>
                @error('liked') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="improve" class="label">{{ __('Ce qui pourrait être mieux') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                <textarea id="improve" wire:model="improve" rows="3" maxlength="2000" class="field" placeholder="{{ __('Le rythme, les règles, ce que vous aimeriez voir…') }}"></textarea>
                @error('improve') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">{{ __('Envoyer mon avis') }}</button>
        </form>
    @endif
</div>
