{{-- Sans Reverb, l'écran se met à jour tout seul toutes les 10 secondes. --}}
<div wire:poll.10s class="table-screen fixed inset-0 flex items-center justify-center overflow-hidden bg-black text-stone-100"
    x-data="{ full: !! document.fullscreenElement }"
    x-on:fullscreenchange.document="full = !! document.fullscreenElement">
    <div wire:key="display-{{ $display['key'] ?? 'empty' }}" class="table-fade flex h-full w-full items-center justify-center">
        @switch($display['kind'] ?? null)
            @case('document')
                @if ($display['document']->isImage())
                    <img src="{{ route('table.file', [$campaign, 'v' => $display['key']]) }}" alt="{{ $display['document']->title }}" class="h-full w-full object-contain">
                @elseif ($display['document']->isPdf())
                    <iframe src="{{ route('table.file', [$campaign, 'v' => $display['key']]) }}#toolbar=0&navpanes=0&view=Fit" title="{{ $display['document']->title }}" class="h-full w-full border-0 bg-white"></iframe>
                @else
                    <p class="p-12 text-center text-4xl font-semibold">{{ $display['document']->title }}</p>
                @endif
                @break

            @case('entity')
                @php($entity = $display['entity'])
                <article class="flex max-h-full w-full max-w-6xl items-center gap-12 overflow-y-auto p-12">
                    @if ($entity->hasImage())
                        <img src="{{ route('table.image', [$campaign, 'v' => $display['key'].'-'.$entity->updated_at?->timestamp]) }}" alt="" class="max-h-[80vh] max-w-[45%] shrink-0 rounded-2xl object-contain shadow-2xl">
                    @endif
                    <div class="min-w-0 flex-1">
                        <h1 class="text-5xl font-semibold tracking-tight lg:text-6xl">{{ $entity->name }}</h1>
                        @if ($entity->summary)
                            <p class="mt-4 text-2xl text-stone-300 lg:text-3xl">{{ $entity->summary }}</p>
                        @endif
                        @if ($entity->description)
                            <div class="mt-6 text-xl leading-relaxed text-stone-200">{{ \App\Support\EntityLinks::plain($entity->description) }}</div>
                        @endif
                        @if ($display['fields']->isNotEmpty())
                            <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-2 text-xl">
                                @foreach ($display['fields'] as $field)
                                    <div class="flex justify-between gap-4 border-b border-stone-700 pb-1">
                                        <dt class="text-stone-400">{{ $field['label'] }}</dt>
                                        <dd class="font-medium">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </article>
                @break

            @case('text')
                <p class="max-w-5xl p-12 text-center font-serif text-5xl leading-tight whitespace-pre-line lg:text-6xl">{{ $display['text'] }}</p>
                @break

            @default
                <div class="text-center">
                    <p class="text-3xl font-semibold tracking-tight text-stone-500"><span>CODEX</span><span class="text-flow">FLOW</span></p>
                    <p class="mt-2 text-xl text-stone-600">{{ $campaign->name }}</p>
                </div>
        @endswitch
    </div>

    <button type="button" x-show="! full" x-on:click="document.documentElement.requestFullscreen()"
        class="absolute right-4 bottom-4 rounded-lg bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/20">
        Plein écran
    </button>
</div>
