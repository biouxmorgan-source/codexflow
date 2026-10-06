@props(['definitions'])
{{-- Saisie des champs libres d'une zone, regroupés comme dans « Champs du jeu ». --}}
@foreach ($definitions->groupBy(fn ($definition) => $definition->groupLabel()) as $groupName => $group)
    <fieldset class="rounded-lg border border-stone-200 p-4" wire:key="fields-{{ $groupName }}">
        <legend class="px-1 text-sm font-semibold">{{ $groupName }}</legend>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($group as $definition)
                @php($id = 'field-'.$definition->id)
                <div wire:key="{{ $id }}" @class(['sm:col-span-2' => $definition->type === \App\Enums\FieldType::LongText])>
                    @switch($definition->type)
                        @case(\App\Enums\FieldType::Boolean)
                            <label class="flex min-h-11 items-center gap-2 text-sm font-medium">
                                <input id="{{ $id }}" type="checkbox" wire:model="fields.{{ $definition->id }}"> {{ $definition->name }}
                            </label>
                            @break
                        @case(\App\Enums\FieldType::LongText)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <textarea id="{{ $id }}" wire:model="fields.{{ $definition->id }}" rows="3" class="field"></textarea>
                            @break
                        @case(\App\Enums\FieldType::Select)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <select id="{{ $id }}" wire:model="fields.{{ $definition->id }}" class="field">
                                <option value="">—</option>
                                @foreach ($definition->options ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break
                        @default
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <input id="{{ $id }}" wire:model="fields.{{ $definition->id }}" class="field"
                                type="{{ $definition->type === \App\Enums\FieldType::Date ? 'date' : 'text' }}"
                                @if ($definition->type === \App\Enums\FieldType::Number) inputmode="decimal" @endif>
                    @endswitch
                    @error('fields.'.$definition->id) <p class="error">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </fieldset>
@endforeach
