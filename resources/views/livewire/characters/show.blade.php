@php($entity = $this->entity)
@php($counters = $this->fields->where('type', \App\Enums\FieldType::Counter))
@php($others = $this->fields->reject(fn ($definition) => $definition->type === \App\Enums\FieldType::Counter))
<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        @if ($this->isGameMaster)
            › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
            › <a href="{{ route('characters.index', $campaign) }}" class="crumb" wire:navigate>Personnages</a>
        @else
            › {{ $campaign->name }}
        @endif
    </nav>

    @if ($this->isGameMaster)
        <p class="mb-4 rounded-md bg-flow/10 px-3 py-2 text-sm text-ink">
            Vous voyez la fiche comme {{ $character->player?->name ?? 'le joueur' }} la voit : sans la zone MJ.
            <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link" wire:navigate>Fiche complète</a>
        </p>
    @endif

    <header class="mb-6 flex flex-wrap items-start gap-4">
        @if ($entity->hasImage())
            <img src="{{ route('characters.portrait', [$campaign, $character]) }}?v={{ $entity->updated_at?->timestamp }}" alt="Portrait de {{ $entity->name }}" class="h-24 w-24 shrink-0 rounded-xl object-cover shadow-sm">
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-semibold">{{ $entity->name }}</h1>
            @if ($entity->summary)
                <p class="mt-1 text-stone-700">{{ $entity->summary }}</p>
            @endif
            <p class="mt-1 text-sm text-stone-500">
                {{ $character->player?->name ? 'Joué par '.$character->player->name : 'Sans joueur' }}
                @if ($character->locked) · <span class="font-medium text-flow">fiche verrouillée par le MJ</span> @endif
            </p>
        </div>
    </header>

    @if ($counters->isNotEmpty())
        <section aria-labelledby="counters-title" class="mb-8">
            <h2 id="counters-title" class="mb-2 font-semibold">Compteurs</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($counters as $definition)
                    @php($counter = $entity->fieldValue($definition))
                    @php($canChange = $this->canPlay && $definition->player_editable)
                    <li wire:key="counter-{{ $definition->id }}" class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                        <p class="text-sm text-stone-600">{{ $definition->name }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            @if ($canChange)
                                <button type="button" wire:click="adjust({{ $definition->id }}, -1)" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="Retirer 1 à {{ $definition->name }}">−</button>
                            @endif
                            <p class="flex-1 text-center text-2xl font-semibold tabular-nums" aria-live="polite">
                                {{ $counter === null ? '—' : $definition->type->format($counter) }}
                            </p>
                            @if ($canChange)
                                <button type="button" wire:click="adjust({{ $definition->id }}, 1)" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="Ajouter 1 à {{ $definition->name }}">+</button>
                            @endif
                        </div>
                        @if (isset($counter['max']) && $counter['max'] > 0)
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                                <div class="h-full rounded-full bg-codex" style="width: {{ min(100, max(0, $counter['value'] / $counter['max'] * 100)) }}%"></div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        <section class="space-y-6 lg:col-span-2">
            @if ($editing)
                <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">Modifier ma fiche</h2>
                    <x-field-inputs :definitions="$this->fields->where('player_editable', true)" model="values" />
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary">Enregistrer</button>
                        <button type="button" wire:click="cancel" class="btn-secondary">Annuler</button>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h2 class="font-semibold">Caractéristiques</h2>
                        @if ($this->canPlay && $this->fields->where('player_editable', true)->isNotEmpty())
                            <button type="button" wire:click="edit" class="link text-sm">Modifier</button>
                        @endif
                    </div>
                    @php($filled = $others->filter(fn ($definition) => $entity->fieldValue($definition) !== null))
                    @if ($filled->isEmpty())
                        <p class="text-sm text-stone-600">Rien de renseigné pour l'instant.</p>
                    @else
                        <dl class="space-y-1">
                            @foreach ($filled as $definition)
                                <div @class(['flex justify-between gap-3 border-b border-stone-100 pb-1', 'flex-col' => $definition->type === \App\Enums\FieldType::LongText])>
                                    <dt class="text-sm text-stone-600">{{ $definition->name }}</dt>
                                    <dd class="font-medium">
                                        @if ($definition->type === \App\Enums\FieldType::LongText)
                                            <span class="font-normal text-stone-700">{{ \App\Support\EntityLinks::plain($entity->fieldValue($definition)) }}</span>
                                        @else
                                            {{ $definition->type->format($entity->fieldValue($definition)) }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endif

            @if ($entity->description)
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 font-semibold">Description</h2>
                    <div class="text-stone-700">{{ \App\Support\EntityLinks::render($entity->description, $campaign, $this->knownLink(...)) }}</div>
                </div>
            @endif

            @php($grants = $this->grants)
            @foreach (['knowledge' => 'Connaissances', 'possession' => 'Possessions', 'document' => 'Documents'] as $section => $sectionTitle)
                @php($items = $grants->filter(fn ($grant) => $section === 'knowledge' ? in_array($grant->kind, ['entity', 'information'], true) : $grant->kind === $section))
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm" wire:key="section-{{ $section }}">
                    <h2 class="mb-2 font-semibold">{{ $sectionTitle }}</h2>
                    @if ($items->isEmpty())
                        <p class="text-sm text-stone-600">
                            @switch($section)
                                @case('knowledge') Rien de révélé pour l'instant. @break
                                @case('possession') Aucun objet reçu. @break
                                @default Aucun document reçu.
                            @endswitch
                        </p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($items as $grant)
                                <li wire:key="grant-{{ $grant->id }}" class="flex items-start gap-3">
                                    <div class="min-w-0 flex-1">
                                        @switch($grant->kind)
                                            @case('entity')
                                                <a href="{{ route('characters.entity', [$campaign, $character, $grant->entity]) }}" class="link font-medium" wire:navigate>{{ $grant->entity->name }}</a>
                                                <span class="text-xs text-stone-500">{{ $grant->entity->type->name }}</span>
                                                @if ($grant->entity->summary)
                                                    <p class="text-sm text-stone-600">{{ $grant->entity->summary }}</p>
                                                @endif
                                                @break
                                            @case('document')
                                                <a href="{{ route('characters.document', [$campaign, $character, $grant->document]) }}" target="_blank" class="link font-medium">{{ $grant->document->title }}</a>
                                                <span class="text-xs text-stone-500">{{ $grant->document->isPdf() ? 'PDF' : 'Image' }}</span>
                                                @break
                                            @default
                                                <p class="font-medium">{{ $grant->label() }}</p>
                                                @if ($grant->body)
                                                    <p class="text-sm text-stone-700">{{ \App\Support\EntityLinks::render($grant->body, $campaign, $this->knownLink(...)) }}</p>
                                                @endif
                                        @endswitch
                                        <p class="text-xs text-stone-400">{{ $grant->created_at->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                                    </div>
                                    @if ($this->isGameMaster)
                                        <button type="button" wire:click="revoke({{ $grant->id }})" wire:confirm="{{ $grant->kind === 'entity' ? 'Cacher à nouveau cette fiche au personnage ?' : 'Retirer cet élément au personnage ?' }}" class="shrink-0 text-xs text-red-700 hover:underline">{{ $grant->kind === 'entity' ? 'Cacher' : 'Retirer' }}</button>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            @if ($this->isGameMaster)
                <livewire:characters.give :campaign="$campaign" :character-id="$character->id" :key="'give-character-'.$character->id" />
            @endif
        </section>

        <section class="lg:col-span-3">
            <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <h2 class="font-semibold">Feuille de personnage</h2>
                    @if ($character->hasSheet())
                        <a href="{{ route('characters.sheet', [$campaign, $character]) }}" target="_blank" class="link text-sm">Ouvrir en grand</a>
                    @endif
                </div>
                @if ($character->hasSheet())
                    <iframe src="{{ route('characters.sheet', [$campaign, $character]) }}" title="Feuille de {{ $entity->name }}" class="h-[75vh] w-full rounded-b-xl border-t border-stone-200"></iframe>
                @else
                    <p class="border-t border-stone-100 px-4 py-6 text-sm text-stone-600">Le MJ n'a pas encore joint de feuille PDF.</p>
                @endif
            </div>
        </section>
    </div>
</div>
