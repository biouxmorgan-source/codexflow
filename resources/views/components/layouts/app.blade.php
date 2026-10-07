@props(['title' => null])
<x-layouts.base :title="$title">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3">
            <a href="{{ route('campaigns.index') }}" class="text-xl font-semibold tracking-tight" wire:navigate>
                <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
            </a>
            @php($searchCampaign = request()->route('campaign'))
            @if ($searchCampaign instanceof \App\Models\Campaign && ! request()->routeIs('search.index') && auth()->user()->can('view', $searchCampaign))
                <form method="GET" action="{{ route('search.index', $searchCampaign) }}" role="search" class="order-last w-full sm:order-none sm:w-auto sm:max-w-xs sm:flex-1"
                    x-data x-on:keydown.slash.window="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable) { $event.preventDefault(); $refs.q.focus() }">
                    <label for="header-search" class="sr-only">Rechercher dans {{ $searchCampaign->name }}</label>
                    <input id="header-search" x-ref="q" type="search" name="q" class="field py-1.5 text-sm" placeholder="Rechercher…  /" autocomplete="off">
                </form>
            @endif
            <nav class="flex items-center gap-3 text-sm whitespace-nowrap sm:gap-4">
                <a href="{{ route('campaigns.index') }}" @class(['rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft', 'bg-codex-soft' => request()->routeIs('campaigns.index')]) wire:navigate>Mes campagnes</a>
                @if ($searchCampaign instanceof \App\Models\Campaign && auth()->user()->can('view', $searchCampaign))
                    @php($unreadMessages = \App\Models\Message::unreadCount(auth()->user(), $searchCampaign))
                    <a href="{{ route('messages.index', $searchCampaign) }}" @class(['rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft', 'bg-codex-soft' => request()->routeIs('messages.*')]) wire:navigate>
                        Messages
                        @if ($unreadMessages > 0)
                            <span class="ml-1 rounded-full bg-flow px-1.5 py-0.5 text-xs font-semibold text-white">{{ $unreadMessages }} <span class="sr-only">non lus</span></span>
                        @endif
                    </a>
                @endif
                @if (auth()->user()->gameSystems()->exists())
                    <a href="{{ route('entity-types.index') }}" @class(['hidden rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft sm:inline', 'bg-codex-soft' => request()->routeIs('entity-types.*')]) wire:navigate>Types de fiche</a>
                @endif
                @php($unreadNotifications = \App\Support\Notify::unreadCount(auth()->user()))
                <a href="{{ route('notifications.index') }}" @class(['relative rounded-md px-2 py-1 text-codex hover:bg-codex-soft', 'bg-codex-soft' => request()->routeIs('notifications.*')]) title="Notifications" wire:navigate>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="sr-only">Notifications{{ $unreadNotifications > 0 ? ' : '.$unreadNotifications.' non lue'.($unreadNotifications > 1 ? 's' : '') : '' }}</span>
                    @if ($unreadNotifications > 0)
                        <span class="absolute -top-1 -right-1 rounded-full bg-flow px-1.5 text-xs font-semibold text-white" aria-hidden="true">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                    @endif
                </a>
                <span class="hidden text-stone-500 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md px-2 py-1 text-stone-600 hover:bg-stone-100 hover:text-ink">Se déconnecter</button>
                </form>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>
</x-layouts.base>
