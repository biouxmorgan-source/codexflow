@props(['entity', 'campaign', 'fieldDefinitions', 'note' => null, 'source' => null])
{{-- Carte de fiche pour l'écran de session : l'essentiel visible, le détail dépliable. --}}
@php($state = $entity->campaignStates->first())
@php($fields = $fieldDefinitions->filter(fn ($d) => $d->entity_type_id === null || $d->entity_type_id === $entity->entity_type_id))
<details class="group rounded-lg border border-stone-200 bg-white" wire:key="card-{{ $entity->id }}-{{ $source }}">
    <summary class="flex cursor-pointer list-none items-start gap-3 p-3">
        @if ($entity->hasImage())
            <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover">
        @else
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-stone-100 font-semibold text-stone-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($entity->name, 0, 1)) }}</span>
        @endif
        <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-baseline gap-x-2">
                <span class="font-semibold">{{ $entity->name }}</span>
                <span class="text-xs text-stone-500">{{ $entity->type->name }}</span>
                @if ($state?->status)
                    <span class="rounded-full bg-flow/10 px-2 py-0.5 text-xs font-medium text-flow">{{ $state->status }}</span>
                @endif
            </span>
            @if ($note)
                <span class="block text-sm font-medium text-ink">{{ $note }}</span>
            @endif
            @if ($entity->summary)
                <span class="block text-sm text-stone-600">{{ $entity->summary }}</span>
            @endif
        </span>
        <span class="shrink-0 text-stone-400 transition group-open:rotate-90" aria-hidden="true">›</span>
        {{ $actions ?? '' }}
    </summary>
    <div class="space-y-3 border-t border-stone-100 px-3 pt-2 pb-3 text-sm">
        @if ($entity->gm_notes)
            <div class="rounded-md bg-flow/5 p-2 text-stone-700"><span class="font-semibold text-flow">{{ __('MJ :') }}</span> {{ \App\Support\EntityLinks::render($entity->gm_notes, $campaign) }}</div>
        @endif
        @if ($state?->gm_notes)
            <div class="rounded-md bg-stone-50 p-2 text-stone-700"><span class="font-semibold">{{ __('Dans cette campagne :') }}</span> {{ $state->gm_notes }}</div>
        @endif
        @if ($entity->description)
            <div class="text-stone-700">{{ \App\Support\EntityLinks::render($entity->description, $campaign) }}</div>
        @endif
        <x-field-values :definitions="$fields" :entity="$entity" :campaign="$campaign" />
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
            <a href="{{ route('entities.show', [$campaign, $entity]) }}" target="_blank" rel="noopener" class="link">{{ __('Ouvrir la fiche ↗') }}</a>
            <button type="button" wire:click="showOnTable('entity', {{ $entity->id }})" class="link">{{ __('Montrer à la table') }}</button>
            @if ($entity->hasImage())
                <button type="button" wire:click="showOnTable('portrait', {{ $entity->id }})" class="link">{{ __('Portrait seul') }}</button>
            @endif
        </div>
    </div>
</details>
