@props(['url', 'title', 'mode' => 'scroll', 'download' => null, 'page' => 1, 'sync' => false])
{{--
    PDF affiché par pdf.js (resources/js/pdf-viewer.js) : identique sur ordinateur, tablette et téléphone.
    En cas d'échec, un lien ouvre le fichier dans le navigateur. $download : adresse du fichier à télécharger.
    Écran de table : $page est la page choisie par le MJ, suivie aussi via l'événement « table-pdf-page » ;
    $sync : l'écran du MJ, dont les flèches tournent la page pour tous.
--}}
<div x-data="pdfViewer(@js($url), @js($mode), @js((int) $page), @js((bool) $sync))" {{ $attributes->merge(['class' => 'relative flex flex-col']) }} role="document" aria-label="{{ $title }}"
    @if ($mode === 'screen') x-on:table-pdf-page.window="follow($event.detail.page)" x-on:keydown.right.window="go(1)" x-on:keydown.left.window="go(-1)" x-on:keydown.page-down.window="go(1)" x-on:keydown.page-up.window="go(-1)" @endif>
    @if ($mode === 'scroll')
        <div class="flex items-center gap-2 border-b border-stone-200 bg-stone-50 px-3 py-1.5 text-sm text-stone-600" x-show="status === 'ready'" x-cloak>
            <template x-if="pages > 1">
                <form x-on:submit.prevent="goTo(jump)" class="flex items-center gap-1">
                    <span class="tabular-nums" x-text="@js(__('Page')) + ' ' + page + ' / ' + pages"></span>
                    <label class="sr-only" for="pdf-jump-{{ $attributes->get('wire:key', 'x') }}">{{ __('Aller à la page') }}</label>
                    <input id="pdf-jump-{{ $attributes->get('wire:key', 'x') }}" type="number" min="1" x-bind:max="pages" x-model="jump" class="w-16 rounded border border-stone-300 px-1.5 py-0.5 text-sm" placeholder="{{ __('n°') }}">
                    <button type="submit" class="rounded px-2 py-0.5 hover:bg-stone-200">{{ __('Aller') }}</button>
                </form>
            </template>
            <span class="ml-auto"></span>
            <button type="button" x-on:click="setZoom(-0.25)" class="rounded px-2 py-0.5 hover:bg-stone-200" aria-label="{{ __('Réduire') }}">−</button>
            <span x-text="Math.round(zoom * 100) + ' %'" class="w-12 text-center tabular-nums"></span>
            <button type="button" x-on:click="setZoom(0.25)" class="rounded px-2 py-0.5 hover:bg-stone-200" aria-label="{{ __('Agrandir') }}">+</button>
            @if ($download)
                <a href="{{ $download }}" class="link ml-2" download>{{ __('Télécharger') }}</a>
            @endif
        </div>
    @endif
    <div x-ref="scroller" @if ($mode === 'scroll') x-on:scroll.throttle.150ms="track()" @endif @class(['min-h-0 flex-1', 'overflow-auto bg-stone-100 p-3' => $mode === 'scroll', 'flex items-center justify-center overflow-hidden' => $mode === 'screen'])>
        <div x-ref="pages" @class(['flex items-center justify-center' => $mode === 'screen'])></div>
        <p x-show="status === 'loading'" class="py-10 text-center text-sm text-stone-500">{{ __('Chargement du PDF…') }}</p>
        <p x-show="status === 'error'" x-cloak class="py-10 text-center text-sm text-stone-600">
            {{ __('Ce PDF ne peut pas être affiché ici.') }} <a href="{{ $url }}" target="_blank" class="link">{{ __('L’ouvrir dans le navigateur') }}</a>
        </p>
    </div>
    @if ($mode === 'screen')
        <div x-show="pages > 1" x-cloak class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-3 text-sm opacity-40 transition hover:opacity-100">
            <button type="button" x-on:click="go(-1)" class="rounded-full bg-black/50 px-3 py-1 text-white" aria-label="{{ __('Page précédente') }}">‹</button>
            <span class="rounded-full bg-black/50 px-3 py-1 text-white tabular-nums" x-text="page + ' / ' + pages"></span>
            <button type="button" x-on:click="go(1)" class="rounded-full bg-black/50 px-3 py-1 text-white" aria-label="{{ __('Page suivante') }}">›</button>
        </div>
    @endif
</div>
