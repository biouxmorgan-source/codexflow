@props(['definitions', 'entity', 'campaign'])
{{-- Valeurs des champs libres d'une zone dans la campagne ; seuls les champs remplis s'affichent. --}}
@php($filled = $definitions->filter(fn ($definition) => $entity->fieldValueIn($definition, $campaign) !== null))
{{-- Toujours un élément racine : Livewire met à jour la page sans erreur quand le premier élément apparaît. --}}
<div>
    @foreach ($filled->groupBy(fn ($definition) => $definition->groupLabel()) as $groupName => $group)
        <div class="mt-4" wire:key="values-{{ $groupName }}">
            <h3 class="mb-2 text-sm font-semibold text-stone-700">{{ $groupName }}</h3>
            <dl class="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                @foreach ($group as $definition)
                    @php($value = $entity->fieldValueIn($definition, $campaign))
                    @php($overridden = $entity->overridesFieldIn($definition, $campaign))
                    <div @class(['flex justify-between gap-3 border-b border-stone-100 pb-1', 'flex-col sm:col-span-2' => $definition->type === \App\Enums\FieldType::LongText])>
                        <dt class="text-sm text-stone-600">
                            {{ $definition->name }}
                            @if ($overridden)
                                @php($worldValue = $entity->fieldValue($definition))
                                <span class="ml-1 rounded-full bg-flow/10 px-1.5 py-0.5 text-[10px] font-medium text-flow" title="{{ __('Valeur du monde : :value', ['value' => $worldValue === null ? __('vide') : $definition->type->format($worldValue)]) }}">{{ __('campagne') }}</span>
                            @endif
                        </dt>
                        <dd class="font-medium">
                            @if ($definition->type === \App\Enums\FieldType::LongText)
                                <span class="font-normal text-stone-700">{{ \App\Support\EntityLinks::inline($value, $campaign) }}</span>
                            @else
                                {{ $definition->type->format($value) }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>
