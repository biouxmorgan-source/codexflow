{{-- Sans Reverb (et toujours pour un invité), l'écran se met à jour tout seul toutes les 10 secondes. --}}
@php($theme = \App\Support\TableTheme::of($campaign->table_theme))
<div wire:poll.10s @class(['table-screen fixed inset-0 flex items-center justify-center overflow-hidden', $theme['background'], $theme['text'], $theme['font']])
    x-data="{ full: !! document.fullscreenElement }"
    x-on:fullscreenchange.document="full = !! document.fullscreenElement">
    <div wire:key="display-{{ $display['key'] ?? 'empty' }}" class="table-fade flex h-full w-full items-center justify-center">
        @switch($display['kind'] ?? null)
            @case('document')
                @if ($display['document']->isImage())
                    <img src="{{ $this->fileUrl('file', ['v' => $display['key']]) }}" alt="{{ $display['document']->title }}" class="h-full w-full object-contain">
                @elseif ($display['document']->isPdf())
                    @php($leads = (bool) auth()->user()?->can('update', $campaign) && $guest === null)
                    <x-pdf-viewer :url="$this->fileUrl('file', ['v' => $display['key']])" :title="$display['document']->title" mode="screen" :page="$display['page']" :sync="$leads" class="h-full w-full" wire:ignore
                        x-on:pdf-page-turned="$wire.turnTo($event.detail.page)" x-on:pdf-pages-known="$wire.knowPages($event.detail.pages)" />
                    {{-- Seul l'écran du MJ ($sync) envoie ces deux événements. Page choisie par le MJ (télécommande, page du document) : l'écran la suit. --}}
                    <span wire:key="pdf-page-{{ $display['page'] }}" x-init="$dispatch('table-pdf-page', { page: {{ $display['page'] }} })" hidden></span>
                @else
                    <p class="p-12 text-center text-4xl font-semibold">{{ $display['document']->title }}</p>
                @endif
                @break

            @case('entity')
                @php($entity = $display['entity'])
                {{-- « safe » : un contenu plus haut que l'écran (téléphone) défile au lieu d'être coupé en haut. --}}
                <article class="flex max-h-full w-full max-w-6xl flex-col items-center-safe gap-6 overflow-y-auto p-6 md:flex-row md:gap-12 md:p-12">
                    @if ($entity->hasImage())
                        <img src="{{ $this->fileUrl('image', ['v' => $display['key'].'-'.$entity->updated_at?->timestamp]) }}" alt="" class="max-h-[40vh] max-w-full shrink-0 rounded-2xl object-contain shadow-2xl md:max-h-[80vh] md:max-w-[45%]">
                    @endif
                    <div class="w-full min-w-0 md:flex-1">
                        <h1 class="text-3xl font-semibold tracking-tight break-words md:text-5xl lg:text-6xl">{{ $entity->name }}</h1>
                        @if ($entity->summary)
                            <p @class(['mt-4 text-xl md:text-2xl lg:text-3xl', $theme['muted']])>{{ $entity->summary }}</p>
                        @endif
                        @if ($entity->description)
                            <div class="mt-6 text-lg leading-relaxed break-words md:text-xl">{{ \App\Support\EntityLinks::plain($entity->description) }}</div>
                        @endif
                        @if ($display['fields']->isNotEmpty())
                            <dl class="mt-8 grid gap-x-8 gap-y-2 text-lg sm:grid-cols-2 md:text-xl">
                                @foreach ($display['fields'] as $field)
                                    <div class="flex justify-between gap-4 border-b border-current/20 pb-1">
                                        <dt @class($theme['muted'])>{{ $field['label'] }}</dt>
                                        <dd class="min-w-0 text-right font-medium break-words">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </article>
                @break

            @case('portrait')
                <img src="{{ $this->fileUrl('image', ['v' => $display['key'].'-'.$display['entity']->updated_at?->timestamp]) }}" alt="" class="h-full w-full object-contain">
                @break

            @case('attachment')
                <img src="{{ $this->fileUrl('file', ['v' => $display['key']]) }}" alt="" class="h-full w-full object-contain">
                @break

            @case('rule')
                @php($rule = $display['rule'])
                <article class="max-h-full w-full max-w-5xl overflow-y-auto p-6 md:p-12">
                    <h1 class="text-3xl font-semibold tracking-tight break-words md:text-5xl lg:text-6xl">{{ $rule->title }}</h1>
                    @if ($rule->summary)
                        <p @class(['mt-6 text-xl md:text-3xl', $theme['muted']])>{{ $rule->summary }}</p>
                    @endif
                    @if ($rule->procedure)
                        <div class="mt-8 text-lg leading-relaxed break-words md:text-2xl">{{ \App\Support\EntityLinks::plain($rule->procedure) }}</div>
                    @endif
                </article>
                @break

            @case('map')
                {{-- Chacun glisse les jetons qu'il a le droit de déplacer (vérifié côté serveur). --}}
                <div x-data="mapTokens" class="h-full w-full touch-none" x-on:pointerdown="down($event)" x-on:pointermove="move($event)" x-on:pointerup="up($event)" x-on:pointercancel="up($event)">
                    <x-table-map :map="$display['map']" :tokens="$display['map']->tokens" :movable="$movable"
                        :image-url="$this->fileUrl('file', ['v' => $display['key']])"
                        :token-url="fn ($token) => $this->fileUrl('token', ['v' => $token->entity?->updated_at?->timestamp], $token)" />
                </div>
                @break

            @case('text')
                <p class="max-w-5xl p-12 text-center font-serif text-5xl leading-tight whitespace-pre-line lg:text-6xl">{{ $display['text'] }}</p>
                @break

            @default
                <div class="text-center">
                    <p @class(['text-3xl font-semibold tracking-tight opacity-60', $theme['muted']]) translate="no"><span>Saga</span><span @class($theme['accent'])>Wyn</span></p>
                    <p @class(['mt-2 text-xl opacity-70', $theme['muted']])>{{ $stopped ? __("Le MJ ne partage plus l'écran de table.") : $campaign->name }}</p>
                </div>
        @endswitch
    </div>

    @if ($initiative)
        {{-- Ordre d'initiative montré par le MJ, par-dessus ce qui est affiché. --}}
        <aside class="absolute top-4 left-4 max-h-[calc(100%-2rem)] w-64 max-w-[45vw] overflow-y-auto rounded-2xl bg-black/70 p-3 text-white shadow-2xl backdrop-blur-sm" aria-label="{{ __('Initiative') }}">
            @if ($initiative['ordered'] !== [])
                <p class="mb-2 flex justify-between text-xs font-semibold tracking-wide text-white/70 uppercase">
                    <span>{{ __('Initiative') }}</span><span>{{ __('Round :round', ['round' => $initiative['round']]) }}</span>
                </p>
                <ol class="space-y-1">
                    @foreach ($initiative['ordered'] as $entry)
                        @php($current = $initiative['turn'] === $entry['id'])
                        <li wire:key="table-init-{{ $entry['id'] }}" @class(['flex items-center gap-2 rounded-lg px-2 py-1 text-base md:text-lg', 'bg-white/25 font-semibold ring-2 ring-amber-300' => $current]) @if ($current) aria-current="true" @endif>
                            <span class="size-3 shrink-0 rounded-full ring-1 ring-white/70" style="background: {{ $entry['color'] }}"></span>
                            <span class="min-w-0 flex-1 truncate">{{ $entry['label'] }}</span>
                            @if ($entry['value'] !== null)
                                <span class="shrink-0 text-sm text-white/70 tabular-nums">{{ rtrim(rtrim(number_format($entry['value'], 2, ',', ''), '0'), ',') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
            @if ($initiative['others'] !== [])
                <p @class(['mb-1 text-xs font-semibold tracking-wide text-white/70 uppercase', 'mt-3' => $initiative['ordered'] !== []])>{{ __('Aussi présents') }}</p>
                <ul class="flex flex-wrap gap-1.5">
                    @foreach ($initiative['others'] as $entry)
                        <li wire:key="table-other-{{ $entry['id'] }}" class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2 py-0.5 text-sm">
                            <span class="size-2.5 rounded-full" style="background: {{ $entry['color'] }}"></span>{{ $entry['label'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    @endif

    @if ($dice)
        {{-- Dernier lancer à la table : il roule sur l'écran, puis s'efface au bout de quelques secondes. --}}
        @php($remaining = max(1, \App\Support\Dice\TableDice::SCREEN_SECONDS - (int) $dice['at']->diffInSeconds(now(), true)))
        <aside wire:key="table-dice-{{ $dice['id'] }}" x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ $remaining * 1000 }})" x-show="show" x-transition.opacity.duration.500ms
            class="absolute inset-x-4 bottom-16 mx-auto max-w-3xl rounded-3xl bg-black/75 px-6 py-5 text-white shadow-2xl ring-1 ring-white/20 backdrop-blur-sm" aria-live="polite" aria-label="{{ __('Lancer de dés') }}">
            <p class="text-center text-lg text-white/80 md:text-2xl"><span class="font-semibold text-white">{{ $dice['who'] }}</span>@if ($dice['label']) · {{ $dice['label'] }}@endif</p>
            <x-dice-result :roll="$dice" rolling large class="mt-3" />
            <p class="mt-2 text-center text-sm text-white/60">{{ $dice['describe'] }}</p>
        </aside>
    @endif

    {{-- Musique lancée par le MJ : jouée ici, par les enceintes de l’écran de table. --}}
    <div wire:ignore x-data="tableAudio($wire.audio)" x-on:table-audio.window="apply($event.detail.state)">
        <button type="button" x-show="blocked" x-cloak x-on:click="unlock()"
            class="absolute bottom-4 left-4 rounded-lg bg-current/10 px-4 py-2 text-sm hover:bg-current/20">
            🔊 {{ __('Activer le son') }}
        </button>
    </div>

    {{-- Masqué là où le plein écran n’existe pas (iPhone). --}}
    <button type="button" x-show="! full && document.fullscreenEnabled" x-on:click="document.documentElement.requestFullscreen()"
        class="absolute right-4 bottom-4 rounded-lg bg-current/10 px-4 py-2 text-sm hover:bg-current/20">
        {{ __('Plein écran') }}
    </button>
</div>
