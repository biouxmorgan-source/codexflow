<div class="max-w-4xl">
    @include('livewire.library.header', ['kindLabel' => __('Monde'), 'title' => $world->name, 'text' => $world->description, 'item' => $world, 'imageRoute' => 'worlds.image'])

    <section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 font-semibold">{{ __('Fiches du monde') }}</h2>
        <p class="mb-3 text-sm text-stone-600">{{ __('Réutilisables dans toutes les campagnes de ce monde ; chaque campagne garde ses propres états et notes.') }}</p>
        @forelse ($entities as $type => $group)
            <h3 class="mt-4 mb-1 text-sm font-semibold text-flow first:mt-0">{{ $type }} · {{ $group->count() }}</h3>
            <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                @foreach ($group as $entity)
                    <li wire:key="world-entity-{{ $entity->id }}">
                        @if ($campaign)
                            <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link" wire:navigate>{{ $entity->name }}</a>
                        @else
                            {{ $entity->name }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucune fiche dans ce monde.') }}</p>
        @endforelse
    </section>

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 font-semibold">{{ __('Documents du monde') }}</h2>
        @forelse ($documents as $document)
            <p wire:key="world-document-{{ $document->id }}" class="py-1 text-sm">
                @if ($campaign)
                    <a href="{{ route('documents.show', [$campaign, $document]) }}" class="link" wire:navigate>{{ $document->title }}</a>
                @else
                    {{ $document->title }}
                @endif
            </p>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucun document commun au monde.') }}</p>
        @endforelse
    </section>
</div>
