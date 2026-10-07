<span class="inline-flex items-center gap-2">
    @if ($showing)
        <span @class(['inline-flex items-center gap-1 font-medium text-flow', 'text-xs' => $compact, 'text-sm' => ! $compact])>
            <span aria-hidden="true">●</span> {{ __('À la table') }}
        </span>
        @unless ($compact)
            <a href="{{ route('table.screen', $campaign) }}" target="codexflow-table" class="text-sm link">{{ __("Ouvrir l'écran") }}</a>
        @endunless
    @else
        <button type="button" wire:click="show" @class(['btn-secondary' => ! $compact, 'text-xs link' => $compact]) title="{{ __("Montrer sur l'écran de table") }}">
            {{ $label ?: __('Afficher à la table') }}
        </button>
    @endif
</span>
