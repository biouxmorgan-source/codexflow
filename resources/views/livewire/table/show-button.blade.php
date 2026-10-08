<span class="inline-flex items-center gap-2">
    @cannot('use-feature', ['table', $campaign])
        {{-- Écran de table hors de la formule du propriétaire : le bouton reste visible, inactif. --}}
        <button type="button" disabled @class(['btn-secondary cursor-not-allowed opacity-60' => ! $compact, 'text-xs text-stone-400' => $compact]) title="{{ __('Fonction Premium, non comprise dans la formule du propriétaire de la campagne') }}">
            {{ $label ?: __('Afficher à la table') }} <x-premium />
        </button>
    @elseif ($showing)
        <span @class(['inline-flex items-center gap-1 font-medium text-flow', 'text-xs' => $compact, 'text-sm' => ! $compact]) title="{{ __("Affiché sur l'écran de table") }}">
            <span aria-hidden="true">●</span> {{ $label ? __(':label : à la table', ['label' => $label]) : __('À la table') }}
        </span>
        @unless ($compact)
            <a href="{{ route('table.screen', $campaign) }}" target="codexflow-table" class="text-sm link">{{ __("Ouvrir l'écran") }}</a>
        @endunless
    @else
        <button type="button" wire:click="show" @if ($gmOnly) wire:confirm="{{ __('Ce contenu est réservé au MJ. Le montrer à toute la table ?') }}" @endif @class(['btn-secondary' => ! $compact, 'text-xs link' => $compact]) title="{{ __("Montrer sur l'écran de table") }}">
            {{ $label ?: __('Afficher à la table') }}
        </button>
    @endif
</span>
