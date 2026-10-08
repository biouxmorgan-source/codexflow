<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
    </nav>
    <h1 class="text-2xl font-semibold">{{ __('Recherche') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Cherche dans toutes vos campagnes, leurs mondes et leurs jeux : en MJ, dans tout leur contenu ; en joueur, dans ce que votre personnage connaît.') }}</p>

    <div class="mb-6">
        <label for="q" class="sr-only">{{ __('Rechercher') }}</label>
        <input id="q" type="search" wire:model.live.debounce.300ms="q" class="field" placeholder="{{ __('Nom, tag, mot du texte, scène…') }}" autofocus autocomplete="off">
    </div>

    @php($words = \App\Support\Search\GlobalSearch::words($q))
    @if ($words === [])
        <p class="text-stone-600">{{ __('Tapez au moins deux lettres. Tous les mots doivent apparaître ; les accents et majuscules ne comptent pas.') }}</p>
    @elseif ($this->groups->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-stone-600">{{ __('Aucun résultat pour « :query ».', ['query' => $q]) }}</p>
        </div>
    @else
        <div class="space-y-6" wire:loading.class="opacity-60">
            @foreach ($this->groups as $group)
                <section wire:key="campaign-{{ $group['campaign']->id }}">
                    <h2 class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                        <span class="font-semibold">{{ $group['campaign']->name }} <span class="text-sm font-normal text-stone-500">({{ $group['total'] }})</span></span>
                        @if ($group['total'] > $group['results']->count())
                            <a href="{{ route('search.index', [$group['campaign'], 'q' => $q]) }}" class="link text-sm" wire:navigate>{{ __('Voir les :count résultats', ['count' => $group['total']]) }}</a>
                        @endif
                    </h2>
                    <ul class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm">
                        @foreach ($group['results'] as ['kind' => $kind, 'result' => $result])
                            <li>
                                <a href="{{ $result->url }}" class="block px-4 py-3 hover:bg-codex-soft" wire:navigate>
                                    <span class="font-medium text-codex">{{ \App\Support\Search\GlobalSearch::highlight($result->title, $words) }}</span>
                                    <span class="ml-1 text-xs text-stone-500">{{ $kind }} · {{ $result->subtitle }}</span>
                                    @if ($result->snippet)
                                        <span class="mt-0.5 block text-sm text-stone-600">
                                            <span @class(['text-xs font-medium', 'text-flow' => $result->snippet['label'] === __('Zone MJ'), 'text-stone-500' => $result->snippet['label'] !== __('Zone MJ')])>{{ __(':label :', ['label' => $result->snippet['label']]) }}</span>
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
