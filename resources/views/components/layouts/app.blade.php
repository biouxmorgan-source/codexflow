@props(['title' => null])
<x-layouts.base :title="$title">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('campaigns.index') }}" class="text-xl font-semibold tracking-tight" wire:navigate>
                <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
            </a>
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
