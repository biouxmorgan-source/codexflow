<x-layouts.app :title="$entity->name.' · '.$campaign->name">
    @if ($viewAs)
        <x-view-as-banner :name="$character->entity->name" :exit="route('characters.show', [$campaign, $character])" />
    @endif
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('characters.show', [$campaign, $character, ...$viewAs]) }}" class="crumb" wire:navigate>{{ $character->entity->name }}</a>
    </nav>

    <article class="max-w-3xl rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex flex-wrap items-start gap-4">
            @if ($entity->hasImage())
                <img src="{{ route('characters.entity-image', [$campaign, $character, $entity]) }}?v={{ $entity->updated_at?->timestamp }}" alt="" class="h-24 w-24 shrink-0 rounded-xl object-cover">
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm text-stone-500">{{ $entity->type->name }}</p>
                <h1 class="text-2xl font-semibold">{{ $entity->name }}</h1>
                @if ($entity->summary)
                    <p class="mt-1 text-stone-700">{{ $entity->summary }}</p>
                @endif
            </div>
        </header>

        @if ($entity->description)
            <div class="text-stone-700">{{ $description }}</div>
        @endif

        @if ($fields->isNotEmpty())
            <dl class="mt-6 grid gap-x-6 gap-y-2 sm:grid-cols-2">
                @foreach ($fields as $definition)
                    <div class="flex justify-between gap-3 border-b border-stone-100 pb-1">
                        <dt class="text-sm text-stone-600">{{ $definition->name }}</dt>
                        <dd class="font-medium">{{ $definition->type->format($entity->fieldValueIn($definition, $campaign)) }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </article>
</x-layouts.app>
