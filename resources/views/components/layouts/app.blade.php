@props(['title' => null])
<x-layouts.base :title="$title">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3">
            <a href="{{ route('campaigns.index') }}" class="text-xl font-semibold tracking-tight" wire:navigate>
                <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
            </a>
            @php($searchCampaign = request()->route('campaign'))
            @if ($searchCampaign instanceof \App\Models\Campaign && ! request()->routeIs('search.index'))
                <form method="GET" action="{{ route('search.index', $searchCampaign) }}" role="search" class="order-last w-full sm:order-none sm:w-auto sm:max-w-xs sm:flex-1"
                    x-data x-on:keydown.slash.window="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable) { $event.preventDefault(); $refs.q.focus() }">
                    <label for="header-search" class="sr-only">Rechercher dans {{ $searchCampaign->name }}</label>
                    <input id="header-search" x-ref="q" type="search" name="q" class="field py-1.5 text-sm" placeholder="Rechercher…  /" autocomplete="off">
                </form>
            @endif
            <nav class="flex items-center gap-3 text-sm whitespace-nowrap sm:gap-4">
                <a href="{{ route('campaigns.index') }}" class="font-medium hover:text-codex" wire:navigate>Mes campagnes</a>
                <a href="{{ route('entity-types.index') }}" class="hidden hover:text-codex sm:inline" wire:navigate>Types de fiche</a>
                <span class="hidden text-stone-500 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600 hover:text-codex">Se déconnecter</button>
                </form>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>
</x-layouts.base>
