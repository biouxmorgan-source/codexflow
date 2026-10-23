@php($detailed = \App\Support\Changelog::detailedFor(auth()->user()))
<x-layouts.app :title="__('Quoi de neuf')">
    <div class="max-w-3xl">
        <h1 class="text-2xl font-semibold">{{ __('Quoi de neuf') }}</h1>
        <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Vous utilisez SagaWyn :version. SagaWyn est en développement : voici ce qui est arrivé, version après version.', ['version' => \App\Support\Changelog::version()]) }}</p>
        @if ($detailed)
            <p class="-mt-4 mb-6 text-sm text-stone-600">{{ __('Vous voyez le détail de chaque version. Les autres comptes ne voient que les versions notables, en une phrase.') }}</p>
        @endif

        <div class="space-y-6">
            @foreach (\App\Support\Changelog::all($detailed) as $version => $entry)
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">
                        {{ __('Version :number', ['number' => $version]) }} · {{ $entry['title'] }}
                        <span class="text-sm font-normal text-stone-500">· {{ \Illuminate\Support\Carbon::parse($entry['date'])->isoFormat('D MMMM YYYY') }}</span>
                    </h2>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-stone-700">
                        @foreach ($entry['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    @if ($detailed)
                        <p class="mt-3 border-t border-stone-200 pt-3 text-sm text-stone-500">
                            @if (filled($entry['short'] ?? null))
                                {{ __('Version courte : :text', ['text' => $entry['short']]) }}
                            @else
                                {{ __('Version non montrée aux autres comptes.') }}
                            @endif
                        </p>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.app>
