<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        @if ($isGameMaster)
            › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        @elseif ($myCharacter)
            › <a href="{{ route('characters.show', [$campaign, $myCharacter]) }}" class="crumb" wire:navigate>{{ $myCharacter->entity->name }}</a>
        @endif
    </nav>
    <h1 class="text-2xl font-semibold">Recherche</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">
        @if ($isGameMaster)
            Cherche dans les fiches, scènes, règles, documents et notes de session de la campagne.
        @else
            Cherche dans ce que votre personnage connaît : fiches révélées, informations, objets, règles ouvertes, documents et notes partagées.
        @endif
        Astuce : la touche / place le curseur dans la recherche depuis n'importe quelle page.
    </p>

    <div class="mb-6 flex flex-wrap gap-3">
        <div class="min-w-0 flex-1 basis-64">
            <label for="q" class="sr-only">Rechercher</label>
            <input id="q" type="search" wire:model.live.debounce.300ms="q" class="field" placeholder="Nom, tag, mot du texte, scène…" autofocus autocomplete="off">
        </div>
        <label for="kind" class="sr-only">Type de résultat</label>
        <select id="kind" wire:model.live="kind" class="field w-auto">
            <option value="">Tout</option>
            @foreach ($kinds as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @if ($entityTypes->isNotEmpty() && in_array($kind, ['', 'entities'], true))
            <label for="entityTypeId" class="sr-only">Type de fiche</label>
            <select id="entityTypeId" wire:model.live="entityTypeId" class="field w-auto">
                <option value="">Tous les types de fiche</option>
                @foreach ($entityTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    @php($words = $this->search->highlightWords())
    @if ($this->search->isEmpty())
        <p class="text-stone-600">Tapez au moins deux lettres. Tous les mots doivent apparaître ; les accents et majuscules ne comptent pas.</p>
    @elseif ($this->results === [])
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-stone-600">Aucun résultat pour « {{ $q }} ».</p>
        </div>
    @else
        <div class="space-y-6" wire:loading.class="opacity-60">
            @foreach ($this->results as $group => $items)
                <section wire:key="group-{{ $group }}">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ $kinds[$group] ?? \App\Support\Search\GlobalSearch::KINDS[$group] }} <span class="font-normal">({{ $items->count() }})</span></h2>
                    <ul class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm">
                        @foreach ($items as $result)
                            <li>
                                <a href="{{ $result->url }}" class="block px-4 py-3 hover:bg-codex-soft" wire:navigate>
                                    <span class="font-medium text-codex">{{ \App\Support\Search\GlobalSearch::highlight($result->title, $words) }}</span>
                                    <span class="ml-1 text-xs text-stone-500">{{ $result->subtitle }}</span>
                                    @if ($result->snippet)
                                        <span class="mt-0.5 block text-sm text-stone-600">
                                            <span @class(['text-xs font-medium', 'text-flow' => $result->snippet['label'] === 'Zone MJ', 'text-stone-500' => $result->snippet['label'] !== 'Zone MJ'])>{{ $result->snippet['label'] }} :</span>
                                            {{ \App\Support\Search\GlobalSearch::highlight($result->snippet['text'], $words) }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</div>
