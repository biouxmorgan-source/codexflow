<x-layouts.app title="Quoi de neuf">
    <div class="max-w-3xl">
        <h1 class="text-2xl font-semibold">Quoi de neuf</h1>
        <p class="mt-1 mb-6 text-sm text-stone-600">Vous utilisez CodexFlow {{ \App\Support\Changelog::version() }}. CodexFlow est en développement : voici ce qui est arrivé, version après version.</p>

        <div class="space-y-6">
            @foreach (\App\Support\Changelog::all() as $version => $entry)
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">
                        Version {{ $version }} · {{ $entry['title'] }}
                        <span class="text-sm font-normal text-stone-500">· {{ \Illuminate\Support\Carbon::parse($entry['date'])->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
                    </h2>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-stone-700">
                        @foreach ($entry['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.app>
