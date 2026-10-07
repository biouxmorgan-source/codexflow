<section @class(['rounded-xl border border-flow/30 bg-white shadow-sm', 'p-4' => $compact, 'p-6' => ! $compact])>
    <div class="mb-2 flex items-center gap-2">
        <h2 @class(['mr-auto font-semibold', 'text-sm tracking-wide text-flow uppercase' => $compact])>{{ __('Secrets') }}</h2>
        @if ($link !== '')
            <a href="{{ route('secrets.index', [$campaign, 'lier' => $link]) }}" class="text-sm link" wire:navigate>{{ __('+ Nouveau') }}</a>
        @else
            <a href="{{ route('secrets.index', $campaign) }}" target="_blank" rel="noopener" class="text-sm link">{{ __('Tous ↗') }}</a>
        @endif
    </div>
    @if ($this->secrets->isEmpty())
        <p class="text-sm text-stone-500">{{ __('Aucun secret relié.') }}</p>
    @else
        <div class="space-y-2">
            @foreach ($this->secrets as $secret)
                <x-secret-card :secret="$secret" :campaign="$campaign" :characters="$this->tableCharacters" :compact="$compact" />
            @endforeach
        </div>
    @endif
</section>
