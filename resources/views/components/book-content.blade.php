{{--
    Contenu du livre des aventures, à l'écran comme à l'impression.
    $version : « assemblee » (d'après les scènes et les séances) ou « redigee » ($narrative).
    $hints : rappels pour le MJ (scènes à confirmer), absents de l'impression.
--}}
@props(['campaign', 'chapters', 'sessions', 'version' => 'assemblee', 'narrative' => '', 'hints' => true])
<article class="adventure-book space-y-8">
    <header class="text-center">
        <p class="text-xs font-semibold tracking-[0.2em] text-stone-500 uppercase">{{ __('Livre des aventures') }}</p>
        <h2 class="mt-2 font-serif text-3xl font-semibold">{{ $campaign->name }}</h2>
        @if ($campaign->description)
            <p class="mx-auto mt-3 max-w-2xl text-stone-600 italic">{{ $campaign->description }}</p>
        @endif
    </header>

    @if ($version === 'redigee')
        <div class="book-text space-y-3 leading-relaxed">{{ \App\Support\EntityLinks::render($narrative, $campaign) }}</div>
    @else
        @foreach ($chapters as $chapter)
            <section class="space-y-5 break-inside-avoid-page">
                <h3 class="border-b border-stone-300 pb-1 font-serif text-2xl font-semibold">{{ $chapter['name'] }}</h3>
                @php($currentChapter = null)
                @foreach ($chapter['scenes'] as $entry)
                    @if ($entry['scene']->chapter && $entry['scene']->chapter !== $currentChapter)
                        @php($currentChapter = $entry['scene']->chapter)
                        <p class="text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $currentChapter }}</p>
                    @endif
                    <div class="break-inside-avoid">
                        <h4 class="mb-2 font-serif text-lg font-semibold">{{ $entry['scene']->name }}</h4>
                        @if ($hints && $entry['source'] === 'unconfirmed')
                            <p class="mb-2 rounded-md bg-amber-50 px-3 py-1.5 text-xs text-amber-800 print:hidden">{{ __('Scène jouée sans récit : le livre reprend ce qui était prévu. Cochez « Jouée comme prévu » ou écrivez ce qui s’est passé.') }}
                                <a href="{{ route('scenes.edit', [$campaign, $entry['scene']]) }}" class="link" wire:navigate>{{ __('Modifier la scène') }}</a></p>
                        @endif
                        @if ($entry['text'] !== '')
                            <div class="book-text space-y-3 leading-relaxed">{{ \App\Support\EntityLinks::render($entry['text'], $campaign) }}</div>
                        @else
                            <p class="text-sm text-stone-500 italic">{{ __('(rien d’écrit)') }}</p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endforeach

        @if ($sessions->isNotEmpty())
            <section class="space-y-5">
                <h3 class="border-b border-stone-300 pb-1 font-serif text-2xl font-semibold">{{ __('Les séances') }}</h3>
                @foreach ($sessions as $session)
                    <div class="break-inside-avoid">
                        <h4 class="mb-1 font-serif text-lg font-semibold">{{ $session->label() }}</h4>
                        <p class="mb-2 text-xs text-stone-500">{{ $session->started_at?->translatedFormat('j F Y') }}</p>
                        <div class="book-text space-y-3 leading-relaxed">{{ \App\Support\EntityLinks::render($session->summary, $campaign) }}</div>
                    </div>
                @endforeach
            </section>
        @endif
    @endif
</article>
