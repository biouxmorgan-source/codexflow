@props(['definitions', 'model' => 'fields', 'entities' => [], 'documents' => []])
{{--
    Saisie des champs libres d'une zone, regroupés comme dans « Champs du jeu ».
    $entities : noms proposés pour une référence à une fiche ; $documents : [id => titre] des fichiers possibles.
--}}
@php($listId = $model.'-entities-'.$definitions->pluck('id')->implode('-'))
@if ($definitions->contains('type', \App\Enums\FieldType::EntityRef))
    <datalist id="{{ $listId }}">
        @foreach ($entities as $entityName)
            <option value="{{ $entityName }}"></option>
        @endforeach
    </datalist>
@endif
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
                                <input id="{{ $id }}" type="checkbox" wire:model="{{ $model }}.{{ $definition->id }}"> {{ $definition->name }}
                            </label>
                            @break
                        @case(\App\Enums\FieldType::LongText)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <x-link-textarea :id="$id" :model="$model.'.'.$definition->id" rows="3" />
                            @break
                        @case(\App\Enums\FieldType::Select)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <select id="{{ $id }}" wire:model="{{ $model }}.{{ $definition->id }}" class="field">
                                <option value="">—</option>
                                @foreach ($definition->options ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break
                        @case(\App\Enums\FieldType::File)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <select id="{{ $id }}" wire:model="{{ $model }}.{{ $definition->id }}" class="field">
                                <option value="">—</option>
                                @foreach ($documents as $documentId => $documentTitle)
                                    <option value="{{ $documentId }}">{{ $documentTitle }}</option>
                                @endforeach
                            </select>
                            @break
                        @case(\App\Enums\FieldType::EntityRef)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <input id="{{ $id }}" type="text" wire:model="{{ $model }}.{{ $definition->id }}" class="field" list="{{ $listId }}" autocomplete="off" placeholder="{{ __('Nom de la fiche') }}">
                            @break
                        @case(\App\Enums\FieldType::Link)
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <input id="{{ $id }}" type="text" wire:model="{{ $model }}.{{ $definition->id }}" class="field" inputmode="url" placeholder="https://…">
                            @break
                        @default
                            <label for="{{ $id }}" class="label">{{ $definition->name }}</label>
                            <input id="{{ $id }}" wire:model="{{ $model }}.{{ $definition->id }}" class="field"
                                type="{{ $definition->type === \App\Enums\FieldType::Date ? 'date' : 'text' }}"
                                @if ($definition->type === \App\Enums\FieldType::Number) inputmode="decimal" @endif
                                @if ($definition->type === \App\Enums\FieldType::Counter) placeholder="{{ __('valeur / maximum, ex. 9 / 12') }}" @endif>
                    @endswitch
                    @error($model.'.'.$definition->id) <p class="error">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </fieldset>
@endforeach
