<div class="max-w-4xl">
    <p class="mb-2 text-sm"><a href="{{ route('campaigns.index') }}" class="text-stone-500 hover:text-codex" wire:navigate>← {{ __('Mes campagnes') }}</a></p>
    <h1 class="text-2xl font-semibold">{{ __('Bibliothèque de démos') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Des campagnes prêtes à jouer : un univers, un court scénario et des personnages prétirés. Chargez-en une : vous recevez votre propre copie, dont vous êtes le MJ, à modifier librement.') }}</p>

    @if ($this->locked)
        <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900" role="status">{{ __('Votre compte de démonstration a déjà sa campagne. La bibliothèque s’ouvre quand le compte est débloqué.') }}</p>
    @endif
    @error('demo') <p class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800" role="alert">{{ $message }}</p> @enderror

    <ul class="grid gap-4 sm:grid-cols-2">
        <li class="flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-codex">Brume &amp; Serment · SagaWyn</p>
            <h2 class="mt-1 text-lg font-semibold">{{ __('Démonstration de SagaWyn') }}</h2>
            <p class="mt-1 flex-1 text-sm text-stone-600">{{ __('Un jeu inventé et une intrigue de trois séances, qui montrent chaque fonction : fiches, carte, indices, sons, chronologie et une séance déjà jouée.') }}</p>
            @unless ($this->locked)
                <div class="mt-4">@include('livewire.campaigns.demo-loader', ['class' => 'btn-primary', 'suffix' => 'Library'])</div>
            @endunless
        </li>
        @foreach ($this->items as $item)
            <li wire:key="demo-{{ $item->id }}" class="flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-codex">{{ $item->game_name }}@if ($item->publisher) · {{ $item->publisher }}@endif · {{ \App\Support\Locale::available()[$item->locale] ?? $item->locale }}</p>
                <h2 class="mt-1 text-lg font-semibold">{{ $item->title }}</h2>
                @if ($item->summary)
                    <p class="mt-1 flex-1 whitespace-pre-line text-sm text-stone-600">{{ $item->summary }}</p>
                @else
                    <div class="flex-1"></div>
                @endif
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    @unless ($this->locked)
                        <button type="button" wire:click="load({{ $item->id }})" wire:loading.attr="disabled" wire:target="load({{ $item->id }})" class="btn-primary">
                            <span wire:loading.remove wire:target="load({{ $item->id }})">{{ __('Charger cette démo') }}</span>
                            <span wire:loading wire:target="load({{ $item->id }})">{{ __('Chargement…') }}</span>
                        </button>
                    @endunless
                    @if ($item->store_url)
                        <a href="{{ $item->store_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-codex hover:underline">{{ __('Acheter le jeu') }} ↗</a>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</div>
