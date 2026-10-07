@props(['id' => 'tags', 'model' => 'tags', 'existing' => [], 'note' => null])
{{-- Saisie de tags « a, b, c » avec les tags déjà utilisés en raccourcis. --}}
<div x-data>
    <label for="{{ $id }}" class="label">{{ __('Tags') }} <span class="font-normal text-stone-500">{{ $note ?? __('(séparés par des virgules)') }}</span></label>
    <input id="{{ $id }}" type="text" wire:model="{{ $model }}" class="field" placeholder="{{ __('combat, voyage, acte 1…') }}">
    @error($model) <p class="error">{{ $message }}</p> @enderror
    @if ($existing)
        <p class="mt-2 flex flex-wrap items-center gap-1 text-xs">
            <span class="text-stone-500">{{ __('Déjà utilisés :') }}</span>
            @foreach ($existing as $tag)
                <button type="button" class="rounded-full bg-stone-100 px-2 py-0.5 text-stone-700 hover:bg-codex-soft"
                    x-on:click="$wire.{{ $model }} = ($wire.{{ $model }}.trim() ? $wire.{{ $model }}.replace(/,\s*$/, '') + ', ' : '') + @js($tag)">{{ $tag }}</button>
            @endforeach
        </p>
    @endif
</div>
