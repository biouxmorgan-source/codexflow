{{-- Sans Reverb, l'écran se met à jour tout seul toutes les 10 secondes. --}}
@php($theme = \App\Support\TableTheme::of($campaign->table_theme))
<div wire:poll.10s @class(['table-screen fixed inset-0 flex items-center justify-center overflow-hidden', $theme['background'], $theme['text'], $theme['font']])
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
                            <p @class(['mt-4 text-2xl lg:text-3xl', $theme['muted']])>{{ $entity->summary }}</p>
                        @endif
                        @if ($entity->description)
                            <div class="mt-6 text-xl leading-relaxed">{{ \App\Support\EntityLinks::plain($entity->description) }}</div>
                        @endif
                        @if ($display['fields']->isNotEmpty())
                            <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-2 text-xl">
                                @foreach ($display['fields'] as $field)
                                    <div class="flex justify-between gap-4 border-b border-current/20 pb-1">
                                        <dt @class($theme['muted'])>{{ $field['label'] }}</dt>
                                        <dd class="font-medium">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </article>
                @break

            @case('portrait')
                <img src="{{ route('table.image', [$campaign, 'v' => $display['key'].'-'.$display['entity']->updated_at?->timestamp]) }}" alt="" class="h-full w-full object-contain">
                @break

            @case('attachment')
                <img src="{{ route('table.file', [$campaign, 'v' => $display['key']]) }}" alt="" class="h-full w-full object-contain">
                @break

            @case('rule')
                @php($rule = $display['rule'])
                <article class="max-h-full w-full max-w-5xl overflow-y-auto p-12">
                    <h1 class="text-5xl font-semibold tracking-tight lg:text-6xl">{{ $rule->title }}</h1>
                    @if ($rule->summary)
                        <p @class(['mt-6 text-3xl', $theme['muted']])>{{ $rule->summary }}</p>
                    @endif
                    @if ($rule->procedure)
                        <div class="mt-8 text-2xl leading-relaxed">{{ \App\Support\EntityLinks::plain($rule->procedure) }}</div>
                    @endif
                </article>
                @break

            @case('map')
                <x-table-map :map="$display['map']" :tokens="$display['map']->tokens"
                    :image-url="route('table.file', [$campaign, 'v' => $display['key']])"
                    :token-url="fn ($token) => route('table.token', [$campaign, $token, 'v' => $token->entity?->updated_at?->timestamp])" />
                @break

            @case('text')
                <p class="max-w-5xl p-12 text-center font-serif text-5xl leading-tight whitespace-pre-line lg:text-6xl">{{ $display['text'] }}</p>
                @break

            @default
                <div class="text-center">
                    <p @class(['text-3xl font-semibold tracking-tight opacity-60', $theme['muted']]) translate="no"><span>CODEX</span><span @class($theme['accent'])>FLOW</span></p>
                    <p @class(['mt-2 text-xl opacity-70', $theme['muted']])>{{ $stopped ? __("Le MJ ne partage plus l'écran de table.") : $campaign->name }}</p>
                </div>
        @endswitch
    </div>

    <button type="button" x-show="! full" x-on:click="document.documentElement.requestFullscreen()"
        class="absolute right-4 bottom-4 rounded-lg bg-current/10 px-4 py-2 text-sm hover:bg-current/20">
        {{ __('Plein écran') }}
    </button>
</div>
