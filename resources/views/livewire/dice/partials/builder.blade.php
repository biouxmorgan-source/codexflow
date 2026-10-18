{{-- Composer un lancer : combien de dés, de quel type, comment les lire. Variables Alpine de diceBuilder. --}}
@php($readings = [
    'raw' => ['⚀', __('Brut'), __('Chaque dé tel qu’il tombe, sans total ni règle.'), "reading === 'raw'"],
    'sum' => ['Σ', __('Additionner'), __('Le total des dés, plus le bonus.'), "reading === 'sum'"],
    'total' => ['≥', __('Seuil sur le total'), __('Réussi si le total des dés atteint le seuil, ou s’il ne le dépasse pas.'), "['above', 'below'].includes(reading)"],
    'count' => ['✓', __('Seuil par dé'), __('Chaque dé au-dessus (ou au-dessous) du seuil compte une réussite.'), "reading === 'count'"],
    'keep' => ['★', __('Garder les meilleurs'), __('Seuls les meilleurs dés comptent (ou les plus faibles).'), "reading === 'keep'"],
    'digits' => ['#', __('Lire en chiffres'), __('Chaque dé donne un chiffre : 3, 5 et 1 font 351.'), "reading === 'digits'"],
])
@php($fieldGroups = collect($sheetFields ?? [])->groupBy('group'))
@php($takeNames = ['value' => __('la valeur'), 'tens' => __('la dizaine'), 'half' => __('la moitié'), 'fifth' => __('le cinquième')])
<div class="space-y-3">
    <div>
        <p class="flex items-baseline justify-between gap-2">
            <span class="label mb-0">{{ __('Dés lancés') }}</span>
            <span class="text-base font-semibold text-codex" translate="no" x-text="notation"></span>
        </p>
        <ul class="mt-1 space-y-1">
            <template x-for="(kind, index) in pool" :key="kind.sides">
                <li class="flex items-center gap-2 rounded-lg border border-stone-200 px-2 py-1">
                    <span class="w-14 text-sm font-semibold" translate="no" x-text="'d' + kind.sides"></span>
                    <div class="flex items-center" role="group" :aria-label="@js(__('Nombre de dés')) + ' d' + kind.sides">
                        <button type="button" x-on:click="bump(index, -1)" class="px-2.5 text-lg text-stone-600 hover:text-codex" aria-label="{{ __('Un dé de moins') }}">−</button>
                        <span class="w-7 text-center text-lg font-semibold tabular-nums" x-text="kind.count" aria-live="polite"></span>
                        <button type="button" x-on:click="bump(index, 1)" class="px-2.5 text-lg text-stone-600 hover:text-codex" aria-label="{{ __('Un dé de plus') }}">+</button>
                    </div>
                    <button type="button" x-show="pool.length > 1" x-on:click="removeKind(index)" class="ml-auto px-1 text-stone-400 hover:text-red-700" aria-label="{{ __('Retirer ce type de dé') }}">×</button>
                </li>
            </template>
        </ul>
    </div>

    <div>
        <p class="label">{{ __('Ajouter un dé') }} <span class="font-normal text-stone-500">{{ __('(touchez plusieurs types pour les mélanger)') }}</span></p>
        <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="{{ __('Type de dé') }}">
            @foreach (\App\Support\Dice\DiceFormula::COMMON_SIDES as $sides)
                <button type="button" x-on:click="add({{ $sides }})" class="rounded-lg p-0.5 ring-codex transition hover:scale-105"
                    :class="pool.some((kind) => kind.sides === {{ $sides }}) ? 'ring-2' : 'opacity-70'" aria-label="{{ __('Ajouter un dé à :sides faces', ['sides' => $sides]) }}">
                    <x-die :sides="$sides" :value="$sides === 100 ? '%' : $sides" size="size-9" />
                </button>
            @endforeach
            <form x-on:submit.prevent="addCustom()" class="flex items-center gap-1">
                <input type="text" inputmode="numeric" x-model="custom" maxlength="4" class="field w-16 px-2 py-1 text-center text-sm"
                    placeholder="{{ __('autre') }}" aria-label="{{ __('Nombre de faces (autre dé)') }}">
                <button type="submit" x-show="custom !== ''" class="btn-secondary px-2 py-1 text-xs">{{ __('Ajouter') }}</button>
            </form>
        </div>
    </div>

    <fieldset>
        <legend class="label">{{ __('Comment les lire') }}</legend>
        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
            @foreach ($readings as $key => [$icon, $name, $hint, $active])
                <button type="button" x-on:click="setReading('{{ $key }}')" title="{{ $hint }}"
                    class="flex items-center gap-2 rounded-lg border px-2 py-1.5 text-left text-xs leading-tight"
                    :class="{{ $active }} ? 'border-codex bg-codex-soft font-semibold text-ink' : 'border-stone-200 text-stone-600 hover:border-codex'"
                    :aria-pressed="({{ $active }}).toString()">
                    <span class="w-4 shrink-0 text-center text-base font-bold text-codex" aria-hidden="true">{{ $icon }}</span>{{ $name }}
                </button>
            @endforeach
        </div>
        @foreach ($readings as $key => [$icon, $name, $hint, $active])
            <p x-show="{{ $active }}" x-cloak class="mt-1 text-xs text-stone-500">{{ $hint }}</p>
        @endforeach
    </fieldset>

    <div class="flex flex-wrap items-end gap-3 text-sm">
        <label x-show="needsThreshold" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0">{{ __('Seuil') }}</span>
            <span class="flex gap-1">
                <input type="text" inputmode="numeric" x-model="threshold" x-show="! threshold_from" maxlength="5" class="field w-20 py-1 text-center" placeholder="{{ $thresholdPlaceholder ?? '' }}">
                @if ($fieldGroups->isNotEmpty())
                    <select class="field max-w-40 py-1" aria-label="{{ __('Seuil lu sur la fiche') }}"
                        x-on:change="setThresholdFrom($event.target.value, threshold_from?.take ?? 'value', $event.target.selectedOptions[0].text)">
                        <option value="" :selected="! threshold_from">{{ __('Nombre saisi') }}</option>
                        @foreach ($fieldGroups as $group => $fields)
                            <optgroup label="{{ $group }}">
                                @foreach ($fields as $field)
                                    <option value="{{ $field['id'] }}" :selected="threshold_from?.field === {{ $field['id'] }}">{{ $field['name'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <select x-show="threshold_from" x-cloak class="field py-1" aria-label="{{ __('Ce qu’on prend de la valeur') }}"
                        x-on:change="threshold_from.take = $event.target.value">
                        @foreach ($takeNames as $take => $name)
                            <option value="{{ $take }}" :selected="threshold_from?.take === '{{ $take }}'">{{ $name }}</option>
                        @endforeach
                    </select>
                @endif
            </span>
        </label>
        <label x-show="['above', 'below'].includes(reading)" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0">{{ __('Le total réussit') }}</span>
            <select class="field py-1" x-on:change="reading = $event.target.value">
                <option value="above" :selected="reading === 'above'">{{ __('au seuil ou plus') }}</option>
                <option value="below" :selected="reading === 'below'">{{ __('au seuil ou moins') }}</option>
            </select>
        </label>
        <label x-show="reading === 'count'" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0">{{ __('Un dé réussit') }}</span>
            <select class="field py-1" x-on:change="count_high = $event.target.value === 'true'">
                <option value="true" :selected="count_high">{{ __('au seuil ou plus') }}</option>
                <option value="false" :selected="! count_high">{{ __('au seuil ou moins') }}</option>
            </select>
        </label>
        <label x-show="reading === 'keep'" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0">{{ __('Garder') }}</span>
            <span class="flex gap-1">
                <input type="number" min="1" :max="count" x-model="keep" class="field w-16 py-1 text-center">
                <select class="field py-1" x-on:change="keep_high = $event.target.value === 'true'">
                    <option value="true" :selected="keep_high">{{ __('meilleurs') }}</option>
                    <option value="false" :selected="! keep_high">{{ __('plus faibles') }}</option>
                </select>
            </span>
        </label>
        <label x-show="hasModifier" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0" x-text="reading === 'below' ? @js(__('Bonus au seuil')) : @js(__('Bonus'))"></span>
            <input type="number" min="-999" max="999" x-model="modifier" class="field w-20 py-1 text-center">
        </label>
    </div>

    @if ($fieldGroups->isNotEmpty())
        <div x-show="hasModifier" x-cloak class="rounded-lg border border-stone-200 p-2 text-sm">
            <p class="label mb-1">{{ __('Ajouter au bonus une valeur de la fiche') }}</p>
            <ul class="mb-1.5 flex flex-wrap gap-1.5" x-show="bonus_from.length">
                <template x-for="(item, index) in bonus_from" :key="index">
                    <li class="inline-flex items-center gap-1 rounded-full border border-codex bg-codex-soft px-2.5 py-0.5 text-xs font-semibold">
                        <span x-text="'+ ' + takeLabel(item)"></span>
                        <button type="button" x-on:click="removeSource(index)" class="text-stone-500 hover:text-red-700" aria-label="{{ __('Retirer cette valeur') }}">×</button>
                    </li>
                </template>
            </ul>
            <div class="flex flex-wrap items-center gap-1" x-show="bonus_from.length < 4">
                <select x-model="sourceTake" class="field py-1" aria-label="{{ __('Ce qu’on prend de la valeur') }}">
                    @foreach ($takeNames as $take => $name)
                        <option value="{{ $take }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select x-model="sourceField" x-ref="sourceField" class="field max-w-44 py-1" aria-label="{{ __('Valeur de la fiche') }}">
                    <option value="">{{ __('Choisir…') }}</option>
                    @foreach ($fieldGroups as $group => $fields)
                        <optgroup label="{{ $group }}">
                            @foreach ($fields as $field)
                                <option value="{{ $field['id'] }}">{{ $field['name'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <button type="button" x-on:click="addSource($refs.sourceField.selectedOptions[0].text)" :disabled="sourceField === ''" class="btn-secondary px-2.5 py-1 text-xs">{{ __('Ajouter') }}</button>
            </div>
        </div>
        <p x-show="threshold_from || (hasModifier && bonus_from.length)" x-cloak class="text-xs text-stone-500">{{ __('Les valeurs se lisent sur la fiche du personnage qui lance, au moment du lancer.') }}</p>
    @endif

    <fieldset x-show="canExplode" x-cloak>
        <legend class="label">{{ __('Dés qui relancent') }}</legend>
        <div class="flex flex-wrap gap-1.5 text-xs">
            @foreach (['none' => __('Non'), 'up' => __('Le max relance (+)'), 'down' => __('Le 1 relance (−)'), 'both' => __('Les deux')] as $key => $name)
                <button type="button" x-on:click="explode = '{{ $key }}'" @if (in_array($key, ['down', 'both'], true)) x-show="canExplodeDown" @endif
                    class="rounded-full border px-2.5 py-1" :class="explode === '{{ $key }}' ? 'border-codex bg-codex-soft font-semibold' : 'border-stone-200 text-stone-600'"
                    :aria-pressed="(explode === '{{ $key }}').toString()">{{ $name }}</button>
            @endforeach
        </div>
    </fieldset>
</div>
