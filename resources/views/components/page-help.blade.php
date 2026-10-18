@props(['topic', 'gm' => true, 'beside' => false])
{{--
    Bouton « ? » à côté du titre d'un écran : son mode d'emploi en quelques lignes (App\Support\PageHelp),
    puis un lien vers la question de la page « Aide ». Sur grand écran l'encadré s'ouvre sous le bouton ;
    sur téléphone il s'étale en haut de l'écran pour ne jamais déborder. Échap ou un clic ailleurs le ferme.
    $beside : à droite du plateau de dés (fixé en bas à gauche, 27rem de large), qui couperait un encadré ouvert dessous.
--}}
@php($help = \App\Support\PageHelp::topic($topic))
@php($panel = 'page-help-'.$topic)
<div x-data="{ open: false }" {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 align-middle']) }} @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="open = ! open" :aria-expanded="open" aria-controls="{{ $panel }}"
        class="flex size-6 items-center justify-center rounded-full border border-stone-300 bg-white text-sm font-semibold text-stone-600 hover:border-codex hover:text-codex"
        aria-label="{{ __('Mode d’emploi : :title', ['title' => $help['title']]) }}" title="{{ __('Mode d’emploi') }}">?</button>
    <div id="{{ $panel }}" x-show="open" x-cloak x-transition.opacity role="region" aria-label="{{ __('Mode d’emploi : :title', ['title' => $help['title']]) }}"
        @class(['fixed inset-x-4 top-20 z-50 max-h-[70vh] overflow-y-auto rounded-xl border border-stone-200 bg-white p-4 text-left text-sm font-normal text-stone-700 shadow-xl lg:inset-x-auto lg:w-96',
            'lg:absolute lg:top-full lg:left-0 lg:mt-2' => ! $beside,
            'lg:top-auto lg:bottom-4 lg:left-[28.5rem]' => $beside])>
        <div class="mb-2 flex items-start justify-between gap-3">
            <p class="font-semibold text-ink">{{ __('Mode d’emploi : :title', ['title' => $help['title']]) }}</p>
            <button type="button" @click="open = false" class="-mt-1 rounded px-1.5 text-stone-500 hover:bg-stone-100 hover:text-ink" aria-label="{{ __('Fermer') }}">×</button>
        </div>
        <ul class="list-disc space-y-1.5 pl-5">
            @foreach ($help['lines'] as $line)
                <li>{{ $line }}</li>
            @endforeach
            @if ($gm)
                @foreach ($help['gm'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            @endif
        </ul>
        <a href="{{ route('help') }}#{{ \App\Support\PageHelp::anchor($help['faq']) }}" class="link mt-3 inline-block text-xs">{{ __('En savoir plus dans l’aide') }}</a>
    </div>
</div>
