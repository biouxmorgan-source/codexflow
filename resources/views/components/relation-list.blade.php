@props(['relations', 'entity', 'campaign'])
{{-- Relations d'une zone, lues depuis la fiche affichée. --}}
@if ($relations->isNotEmpty())
    <ul class="mt-4 divide-y divide-stone-100 rounded-lg border border-stone-200 text-sm">
        @foreach ($relations as $relation)
            @php($other = $relation->otherSide($entity))
            <li wire:key="relation-{{ $relation->id }}" class="flex flex-wrap items-center gap-x-2 gap-y-1 px-3 py-2">
                <span class="text-stone-600">{{ $relation->labelFrom($entity) }}</span>
                <a href="{{ route('entities.show', [$campaign, $other]) }}" class="font-medium link" wire:navigate>{{ $other->name }}</a>
                <span class="text-xs text-stone-500">{{ $other->type->name }}</span>
                @if ($relation->campaign_id)
                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">cette campagne</span>
                @endif
                <button type="button" wire:click="deleteRelation({{ $relation->id }})" wire:confirm="Supprimer cette relation ?" class="ml-auto text-xs text-red-700 hover:underline" aria-label="Supprimer la relation avec {{ $other->name }}">Supprimer</button>
            </li>
        @endforeach
    </ul>
@endif
