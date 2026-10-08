<div class="max-w-4xl">
    @include('livewire.library.header', ['kindLabel' => __('Jeu'), 'title' => $gameSystem->name, 'text' => $gameSystem->description])

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 font-semibold">{{ __('Règles de référence') }}</h2>
            @forelse ($rules as $rule)
                <p wire:key="game-rule-{{ $rule->id }}" class="py-1 text-sm">
                    @if ($campaign)
                        <a href="{{ route('rules.show', [$campaign, $rule]) }}" class="link" wire:navigate>{{ $rule->title }}</a>
                    @else
                        {{ $rule->title }}
                    @endif
                </p>
            @empty
                <p class="text-sm text-stone-500">{{ __('Aucune règle commune au jeu.') }}</p>
            @endforelse
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 font-semibold">{{ __('Documents du jeu') }}</h2>
            @forelse ($documents as $document)
                <p wire:key="game-document-{{ $document->id }}" class="py-1 text-sm">
                    @if ($campaign)
                        <a href="{{ route('documents.show', [$campaign, $document]) }}" class="link" wire:navigate>{{ $document->title }}</a>
                    @else
                        {{ $document->title }}
                    @endif
                </p>
            @empty
                <p class="text-sm text-stone-500">{{ __('Aucun document commun au jeu.') }}</p>
            @endforelse
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm md:col-span-2">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">{{ __('Champs du jeu') }}</h2>
                @if ($campaign)
                    <a href="{{ route('fields.index', $campaign) }}" class="link text-sm" wire:navigate>{{ __('Gérer les champs →') }}</a>
                @endif
            </div>
            @if ($fieldGroups->isEmpty())
                <p class="text-sm text-stone-500">{{ __('Aucun champ : les fiches n’ont que leur nom, leur résumé et leurs zones de texte.') }}</p>
            @else
                <ul class="flex flex-wrap gap-2 text-sm">
                    @foreach ($fieldGroups as $group => $count)
                        <li class="rounded-full bg-stone-100 px-3 py-1">{{ $group }} · {{ $count }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
