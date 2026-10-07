@props(['title' => null])
<x-layouts.base :title="$title">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3">
            <a href="{{ route('campaigns.index') }}" class="text-xl font-semibold tracking-tight" translate="no" wire:navigate>
                <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
            </a>
            @php($searchCampaign = request()->route('campaign'))
            @if ($searchCampaign instanceof \App\Models\Campaign && ! request()->routeIs('search.index') && auth()->user()->can('play', $searchCampaign))
                <form method="GET" action="{{ route('search.index', $searchCampaign) }}" role="search" class="order-last w-full sm:order-none sm:w-auto sm:max-w-xs sm:flex-1"
                    x-data x-on:keydown.slash.window="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable) { $event.preventDefault(); $refs.q.focus() }">
                    <label for="header-search" class="sr-only">{{ __('Rechercher dans :name', ['name' => $searchCampaign->name]) }}</label>
                    <input id="header-search" x-ref="q" type="search" name="q" class="field py-1.5 text-sm" placeholder="{{ __('Rechercher…') }}  /" autocomplete="off">
                    {{-- Mode « Voir comme… » : la recherche se fait dans ce que connaît le personnage (vérifié par la page de recherche). --}}
                    @php($viewAsCharacter = request()->route('character'))
                    @if ($viewAsCharacter && request()->routeIs('characters.show', 'characters.entity') && request()->boolean('comme'))
                        <input type="hidden" name="comme" value="{{ $viewAsCharacter instanceof \App\Models\PlayerCharacter ? $viewAsCharacter->id : $viewAsCharacter }}">
                    @endif
                </form>
            @endif
            <nav class="flex items-center gap-3 text-sm whitespace-nowrap sm:gap-4">
                <a href="{{ route('campaigns.index') }}" @class(['rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft', 'bg-codex-soft' => request()->routeIs('campaigns.index')]) wire:navigate>{{ __('Mes campagnes') }}</a>
                @if (auth()->user()->gameSystems()->exists())
                    <a href="{{ route('entity-types.index') }}" @class(['hidden rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft sm:inline', 'bg-codex-soft' => request()->routeIs('entity-types.*')]) wire:navigate>{{ __('Types de fiche') }}</a>
                @endif
                @if (auth()->user()->tags()->exists())
                    <a href="{{ route('tags.index') }}" @class(['hidden rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft sm:inline', 'bg-codex-soft' => request()->routeIs('tags.*')]) wire:navigate>{{ __('Tags') }}</a>
                @endif
                @livewire(\App\Livewire\HeaderBadges::class, ['campaign' => $searchCampaign instanceof \App\Models\Campaign ? $searchCampaign : null])
                <a href="{{ route('preferences') }}" @class(['hidden rounded-md px-2 py-1 text-stone-600 hover:bg-stone-100 hover:text-ink sm:inline', 'bg-stone-100' => request()->routeIs('preferences')]) title="{{ __('Préférences') }}" wire:navigate>{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md px-2 py-1 text-stone-600 hover:bg-stone-100 hover:text-ink">{{ __('Se déconnecter') }}</button>
                </form>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 pt-8 pb-12">
        {{ $slot }}
    </main>
    <footer class="mx-auto max-w-6xl px-4 pb-24 text-xs text-stone-500">
        CodexFlow {{ \App\Support\Changelog::version() }}
        · <a href="{{ route('changelog') }}" class="hover:text-ink hover:underline" wire:navigate>{{ __('Quoi de neuf') }}</a>
        · <a href="{{ route('recommended') }}" class="hover:text-ink hover:underline" wire:navigate>{{ __('Configuration recommandée') }}</a>
        · <a href="{{ route('preferences') }}" class="hover:text-ink hover:underline" wire:navigate>{{ __('Préférences') }}</a>
        · <a href="{{ route('help') }}" class="hover:text-ink hover:underline" wire:navigate>{{ __('Aide') }}</a>
        · <a href="{{ route('bugs.create', request()->routeIs('bugs.*') ? [] : ['page' => '/'.ltrim(request()->path(), '/')]) }}" class="hover:text-ink hover:underline" wire:navigate>{{ __('Signaler un problème') }}</a>
        @can('admin')
            @php($openReports = \App\Models\BugReport::open()->count())
            · <a href="{{ route('bugs.index') }}" @class(['hover:text-ink hover:underline', 'font-semibold text-flow' => $openReports]) wire:navigate>{{ __('Problèmes signalés') }}@if ($openReports) ({{ $openReports }})@endif</a>
        @endcan
    </footer>
    @livewire(\App\Livewire\WhatsNew::class)
    @if ($searchCampaign instanceof \App\Models\Campaign && ! request()->routeIs('messages.*') && auth()->user()->can('play', $searchCampaign))
        @persist('chat-'.$searchCampaign->id)
            @livewire(\App\Livewire\ChatDock::class, ['campaign' => $searchCampaign], key('chat-'.$searchCampaign->id))
        @endpersist
    @endif
</x-layouts.base>
