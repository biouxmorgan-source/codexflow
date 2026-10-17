{{-- Composer un lancer : combien de dés, de quel type, comment les lire. Variables Alpine de diceBuilder. --}}
@php($readings = [
    'sum' => ['Σ', __('Additionner'), __('Le total des dés, plus le bonus.')],
    'above' => ['≥', __('Atteindre un seuil'), __('Réussi si le total atteint le seuil.')],
    'below' => ['≤', __('Rester sous un seuil'), __('Réussi si le total ne dépasse pas le seuil.')],
    'count' => ['✓', __('Compter les réussites'), __('Chaque dé qui atteint le seuil compte une réussite.')],
    'keep' => ['★', __('Garder les meilleurs'), __('Seuls les meilleurs dés comptent (ou les plus faibles).')],
    'digits' => ['#', __('Lire en chiffres'), __('Chaque dé donne un chiffre : 3, 5 et 1 font 351.')],
])
<div class="space-y-3">
    <div class="flex items-center gap-2">
        <div class="flex items-center rounded-lg border border-stone-200" role="group" aria-label="{{ __('Nombre de dés') }}">
            <button type="button" x-on:click="bump(-1)" class="px-3 py-1.5 text-lg text-stone-600 hover:text-codex" aria-label="{{ __('Un dé de moins') }}">−</button>
            <span class="w-8 text-center text-lg font-semibold tabular-nums" x-text="count" aria-live="polite"></span>
            <button type="button" x-on:click="bump(1)" class="px-3 py-1.5 text-lg text-stone-600 hover:text-codex" aria-label="{{ __('Un dé de plus') }}">+</button>
        </div>
        <p class="text-lg font-semibold text-codex" translate="no"><span x-text="count"></span>d<span x-text="sides"></span></p>
    </div>

    <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="{{ __('Type de dé') }}">
        @foreach (\App\Support\Dice\DiceFormula::COMMON_SIDES as $sides)
            <button type="button" x-on:click="pick({{ $sides }})" class="rounded-lg p-0.5 ring-codex transition hover:scale-105"
                :class="Number(sides) === {{ $sides }} && custom === '' ? 'ring-2' : 'opacity-70'" aria-label="{{ __('Dé à :sides faces', ['sides' => $sides]) }}"
                :aria-pressed="(Number(sides) === {{ $sides }} && custom === '').toString()">
                <x-die :sides="$sides" :value="$sides === 100 ? '%' : $sides" size="size-9" />
            </button>
        @endforeach
        <input type="text" inputmode="numeric" x-model="custom" x-on:input="customSides()" maxlength="4" class="field w-16 px-2 py-1 text-center text-sm"
            placeholder="{{ __('autre') }}" aria-label="{{ __('Nombre de faces (autre dé)') }}">
    </div>

    <fieldset>
        <legend class="label">{{ __('Comment les lire') }}</legend>
        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
            @foreach ($readings as $key => [$icon, $name, $hint])
                <button type="button" x-on:click="setReading('{{ $key }}')" title="{{ $hint }}"
                    class="flex items-center gap-2 rounded-lg border px-2 py-1.5 text-left text-xs leading-tight"
                    :class="reading === '{{ $key }}' ? 'border-codex bg-codex-soft font-semibold text-ink' : 'border-stone-200 text-stone-600 hover:border-codex'"
                    :aria-pressed="(reading === '{{ $key }}').toString()">
                    <span class="w-4 shrink-0 text-center text-base font-bold text-codex" aria-hidden="true">{{ $icon }}</span>{{ $name }}
                </button>
            @endforeach
        </div>
        @foreach ($readings as $key => [$icon, $name, $hint])
            <p x-show="reading === '{{ $key }}'" x-cloak class="mt-1 text-xs text-stone-500">{{ $hint }}</p>
        @endforeach
    </fieldset>

    <div class="flex flex-wrap items-end gap-3 text-sm">
        <label x-show="needsThreshold" x-cloak class="flex flex-col gap-1">
            <span class="label mb-0">{{ __('Seuil') }}</span>
            <input type="text" inputmode="numeric" x-model="threshold" maxlength="5" class="field w-20 py-1 text-center" placeholder="{{ $thresholdPlaceholder ?? '' }}">
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
