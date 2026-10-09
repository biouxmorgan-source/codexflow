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

        @php($images = $attachments->filter->isImage())
        @if ($images->isNotEmpty())
            <section class="mt-6">
                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Illustrations') }}</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($images as $attachment)
                        <a href="{{ route('characters.entity-attachment', [$campaign, $character, $entity, $attachment]) }}" target="_blank" rel="noopener">
                            <img src="{{ route('characters.entity-attachment', [$campaign, $character, $entity, $attachment]) }}" alt="{{ $attachment->original_name }}" class="aspect-square w-full rounded-lg border border-stone-200 object-cover" loading="lazy">
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
        @php($files = $attachments->reject->isImage())
        @if ($files->isNotEmpty())
            <section class="mt-6">
                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Fichiers joints') }}</h2>
                <ul class="space-y-1 text-sm">
                    @foreach ($files as $attachment)
                        <li><a href="{{ route('characters.entity-attachment', [$campaign, $character, $entity, $attachment]) }}" class="link" target="_blank" rel="noopener">{{ $attachment->original_name }}</a> <span class="text-stone-500">· {{ $attachment->humanSize() }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($relations->isNotEmpty())
            <section class="mt-6">
                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Relations') }}</h2>
                <ul class="space-y-1 text-sm">
                    @foreach ($relations as $item)
                        <li>
                            <span class="text-stone-600">{{ $item['relation']->labelFrom($entity) }}</span>
                            @if ($item['other']->id === $character->entity_id)
                                <span class="font-medium">{{ $item['other']->name }}</span>
                            @else
                                <a href="{{ $link($item['other']) }}" class="link">{{ $item['other']->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
</x-layouts.app>
