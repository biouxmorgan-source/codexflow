{{-- Scènes et fiches d'un point d'intérêt, chacune ouverte d'un clic ; $removable : le MJ peut les retirer du point. --}}
@php($removable ??= false)
@if ($point->scenes->isNotEmpty() || $point->entities->isNotEmpty())
    <ul class="space-y-1 text-sm">
        @foreach ($point->scenes as $scene)
            <li wire:key="link-scene-{{ $point->id }}-{{ $scene->id }}" class="flex items-center gap-2">
                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $scene->status->badge() }}">{{ $scene->status->label() }}</span>
                <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="link min-w-0 flex-1 truncate" wire:navigate>{{ $scene->name }}</a>
                @if ($removable)
                    <button type="button" wire:click="unlink('scenes', {{ $scene->id }})" class="text-stone-400 hover:text-red-700" aria-label="{{ __('Retirer :name', ['name' => $scene->name]) }}">×</button>
                @endif
            </li>
        @endforeach
        @foreach ($point->entities as $entity)
            <li wire:key="link-entity-{{ $point->id }}-{{ $entity->id }}" class="flex items-center gap-2">
                <span class="shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ __('Fiche') }}</span>
                <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link min-w-0 flex-1 truncate" wire:navigate>{{ $entity->name }}</a>
                @if ($removable)
                    <button type="button" wire:click="unlink('entities', {{ $entity->id }})" class="text-stone-400 hover:text-red-700" aria-label="{{ __('Retirer :name', ['name' => $entity->name]) }}">×</button>
                @endif
            </li>
        @endforeach
    </ul>
@endif
