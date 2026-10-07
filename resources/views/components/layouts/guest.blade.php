@props(['title' => null])
<x-layouts.base :title="$title">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-8 text-center text-3xl font-semibold tracking-tight" translate="no">
            <span class="text-ink">CODEX</span><span class="text-flow">FLOW</span>
        </a>
        <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            @if ($title)
                <h1 class="mb-6 text-xl font-semibold">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </div>
        <nav class="mt-6 flex flex-wrap justify-center gap-x-3 gap-y-1 text-xs text-stone-500" aria-label="{{ __('Langue') }}">
            @foreach (\App\Support\Locale::available() as $code => $name)
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                    @class(['hover:text-ink hover:underline', 'font-semibold text-ink' => app()->getLocale() === $code])
                    @if (app()->getLocale() === $code) aria-current="true" @endif>{{ $name }}</a>
            @endforeach
        </nav>
    </main>
</x-layouts.base>
