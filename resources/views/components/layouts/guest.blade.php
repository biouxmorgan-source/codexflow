@props(['title' => null])
<x-layouts.base :title="$title">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-8 text-center text-3xl font-semibold tracking-tight">
            <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
        </a>
        <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            @if ($title)
                <h1 class="mb-6 text-xl font-semibold">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </div>
    </main>
</x-layouts.base>
