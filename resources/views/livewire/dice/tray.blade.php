{{-- Sans Reverb, le plateau se resynchronise tout seul (demandes du MJ, lancers des autres). --}}
<div wire:poll.15s>
    @if ($usable)
        <div @class(['fixed bottom-2 left-2 sm:bottom-4 sm:left-4', 'z-50' => $open, 'z-30' => ! $open]) x-data="diceBuilder(@js('sagawyn-dice-'.$campaign->id), @js(['value' => ':name', 'tens' => __('dizaine de :name'), 'half' => __('moitié de :name'), 'fifth' => __('cinquième de :name')]))">
            @if (! $open)
                <button type="button" wire:click="toggle" class="flex items-center gap-2 rounded-full bg-codex px-4 py-2.5 text-sm font-semibold text-on-accent shadow-lg hover:bg-ink">
                    <x-die sides="6" value="6" size="size-5" state="dropped" aria-hidden="true" />
                    {{ __('Dés') }}
                    @if ($this->pending->isNotEmpty())
                        <span class="rounded-full bg-flow px-2 py-0.5 text-xs">{{ $this->pending->count() }} <span class="sr-only">{{ __('lancers demandés') }}</span></span>
                    @endif
                </button>
            @else
                <section class="fixed inset-x-2 bottom-2 z-50 flex max-h-[85vh] flex-col overflow-hidden rounded-xl border border-stone-200 bg-white shadow-2xl sm:inset-x-auto sm:bottom-4 sm:left-4 sm:w-[27rem]" aria-label="{{ __('Dés') }}">
                    <header class="flex items-center gap-1 border-b border-stone-200 bg-stone-50 px-3 py-2">
                        <h2 class="mr-2 text-sm font-semibold">{{ __('Dés') }}</h2>
                        @foreach (array_filter(['roll' => __('Lancer'), 'ask' => $this->isGameMaster ? __('Demander') : null, 'history' => __('Derniers lancers')]) as $key => $name)
                            <button type="button" wire:click="select('{{ $key }}')" @class(['rounded-md px-2 py-1 text-xs', 'bg-codex text-on-accent' => $tab === $key, 'text-stone-700 hover:bg-codex-soft' => $tab !== $key])
                                @if ($tab === $key) aria-current="true" @endif>{{ $name }}</button>
                        @endforeach
                        <button type="button" wire:click="toggle" class="ml-auto rounded px-2 py-0.5 text-stone-500 hover:bg-stone-200 hover:text-ink" aria-label="{{ __('Réduire les dés') }}">—</button>
                    </header>

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-3">
                        @if ($tab === 'history')
                            @forelse ($history as $item)
                                <article wire:key="history-{{ $item['id'] }}" class="rounded-lg border border-stone-100 p-2">
                                    <p class="flex items-baseline justify-between gap-2 text-xs text-stone-500">
                                        <span class="min-w-0 truncate"><span class="font-semibold text-ink">{{ $item['who'] }}</span>@if ($item['label']) · {{ $item['label'] }}@endif</span>
                                        <span class="shrink-0">@if ($item['discreet'])<span class="rounded bg-stone-100 px-1">{{ __('Discret') }}</span> @endif{{ $item['at']?->format('H:i') }}</span>
                                    </p>
                                    <x-dice-result :roll="$item" class="mt-1" />
                                    <p class="mt-1 text-center text-xs text-stone-500">{{ $item['describe'] }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-stone-500">{{ __('Aucun lancer pour l’instant.') }}</p>
                            @endforelse
                        @else
                            @if ($tab === 'roll' && $last)
                                <div wire:key="last-roll-{{ $last['id'] }}" class="rounded-xl bg-stone-50 p-3" role="status" x-init="$el.scrollIntoView({ block: 'nearest' })">
                                    <x-dice-result :roll="$last" rolling />
                                    <p class="mt-1 text-center text-xs text-stone-500">{{ $last['describe'] }}@if ($last['discreet']) · {{ __('Discret') }}@endif</p>
                                </div>
                            @endif

                            @if ($tab === 'ask')
                                @foreach ($requests as $item)
                                    @php($request = $item['request'])
                                    <article wire:key="request-{{ $request->id }}" @class(['rounded-lg border p-2', 'border-codex/40' => $request->isOpen(), 'border-stone-100 opacity-80' => ! $request->isOpen()])>
                                        <div class="flex items-baseline justify-between gap-2">
                                            <p class="min-w-0 truncate text-sm font-semibold">{{ $request->label }}</p>
                                            @if ($request->isOpen())
                                                <button type="button" wire:click="closeRequest({{ $request->id }})" class="link shrink-0 text-xs">{{ __('Clore') }}</button>
                                            @else
                                                <span class="shrink-0 text-xs text-stone-500">{{ __('Terminée') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-stone-500">{{ $item['describe'] }}@if ($request->visibility === 'gm') · {{ __('Discret') }}@endif</p>
                                        <ul class="mt-1.5 divide-y divide-stone-100">
                                            @foreach ($item['targets'] as $target)
                                                <li class="flex items-center justify-between gap-2 py-1 text-sm">
                                                    <span class="min-w-0 truncate">{{ $target['name'] }}</span>
                                                    @if ($target['roll'])
                                                        <span @class(['shrink-0 font-semibold', 'text-wyn' => $target['roll']['result']['success'] === true, 'text-flow' => $target['roll']['result']['success'] === false])
                                                            title="{{ collect($target['roll']['result']['dice'])->map(fn ($group) => collect($group['rolls'])->pluck('v')->implode('+'))->implode(', ') }}">{{ $target['roll']['headline'] }}</span>
                                                    @else
                                                        <span class="shrink-0 text-xs text-stone-400">{{ $request->isOpen() ? __('en attente…') : __('n’a pas lancé') }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </article>
                                @endforeach
                            @endif

                            @foreach (['mine' => [__('Mes lancers'), $mine, true], 'table' => [__('Lancers de la campagne'), $presets, $this->isGameMaster]] as $scope => [$title, $list, $removable])
                                @if ($list !== [])
                                    <div>
                                        <p class="label">{{ $title }}</p>
                                        <ul class="flex flex-wrap gap-1.5">
                                            @foreach ($list as $preset)
                                                <li wire:key="preset-{{ $scope }}-{{ $preset['id'] }}" class="inline-flex items-center rounded-full border text-xs"
                                                    :class="loaded?.id === @js($preset['id']) ? 'border-codex bg-codex text-on-accent' : 'border-codex/40 bg-codex-soft'">
                                                    <button type="button" x-on:click="loadPreset(@js($preset['formula']), @js($preset['label']), '{{ $scope }}', @js($preset['id']))" class="px-2.5 py-1 font-medium"
                                                        title="{{ \App\Support\Dice\DiceFormula::fromArray($preset['formula'])->describe() }}">{{ $preset['label'] }}</button>
                                                    @if ($removable)
                                                        <button type="button" wire:click="removePreset(@js($preset['id']), '{{ $scope }}')" x-on:click="if (loaded?.id === @js($preset['id'])) loaded = null" wire:confirm="{{ __('Retirer ce lancer enregistré ?') }}" class="pr-2 opacity-60 hover:text-red-700 hover:opacity-100" aria-label="{{ __('Retirer :name', ['name' => $preset['label']]) }}">×</button>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                            <div>
                                <p class="label">{{ __('Exemples') }}</p>
                                <ul class="-mx-3 flex gap-1.5 overflow-x-auto px-3 pb-1">
                                    @foreach ($examples as $example)
                                        <li class="shrink-0"><button type="button" x-on:click="load(@js($example['formula'])); loaded = null" class="rounded-full border border-stone-200 px-2.5 py-1 text-xs whitespace-nowrap hover:border-codex"
                                            title="{{ \App\Support\Dice\DiceFormula::fromArray($example['formula'])->describe() }}">{{ $example['label'] }}</button></li>
                                    @endforeach
                                </ul>
                            </div>

                            @include('livewire.dice.partials.builder', ['thresholdPlaceholder' => $tab === 'ask' ? __('chacun') : ''])

                            @if ($tab === 'roll')
                                <div class="space-y-2 border-t border-stone-100 pt-3">
                                    <input type="text" x-model="label" maxlength="100" class="field py-1.5 text-sm" placeholder="{{ __('Pour quoi ? (facultatif) : Attaque, Fouille…') }}" aria-label="{{ __('Pour quoi') }}">
                                    @if ($this->characters->count() > 1)
                                        <select wire:model="characterId" class="field py-1.5 text-sm" aria-label="{{ __('Lancer pour') }}">
                                            @foreach ($this->characters as $character)
                                                <option value="{{ $character->id }}">{{ $character->entity->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    {{-- Un lancer qu'on refait souvent : on l'enregistre, on le recharge, on le met à jour quand le personnage évolue. --}}
                                    <div class="space-y-1.5 rounded-lg bg-stone-50 p-2">
                                        <p class="label mb-0">{{ __('Enregistrer ce lancer') }}</p>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="presetName" maxlength="60" class="field min-w-0 py-1 text-xs" placeholder="{{ __('Nom : Attaque, Sauvegarde…') }}" aria-label="{{ __('Nom du lancer enregistré') }}">
                                            @if ($this->isGameMaster)
                                                <select x-model="presetScope" class="field w-auto py-1 text-xs" aria-label="{{ __('Pour qui') }}">
                                                    <option value="mine">{{ __('Pour moi') }}</option>
                                                    <option value="table">{{ __('Pour la table') }}</option>
                                                </select>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" x-show="loaded" x-cloak x-on:click="$wire.savePreset(formula, presetName || loaded.label, loaded.scope, loaded.id).then((id) => { if (id) loaded.label = presetName || loaded.label })"
                                                class="btn-secondary py-1 text-xs"><span x-text="@js(__('Mettre à jour')) + ' « ' + (loaded?.label ?? '') + ' »'"></span></button>
                                            <button type="button" x-on:click="$wire.savePreset(formula, presetName, presetScope, null).then((id) => { if (id) loaded = { id, scope: presetScope, label: presetName } })"
                                                class="btn-secondary py-1 text-xs" x-text="loaded ? @js(__('Enregistrer comme nouveau')) : @js(__('Enregistrer'))"></button>
                                        </div>
                                        @error('presetLabel') <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @else
                                <div class="space-y-2 border-t border-stone-100 pt-3">
                                    <input type="text" x-model="label" maxlength="100" class="field py-1.5 text-sm" placeholder="{{ __('Quel jet ? Perception, Volonté…') }}" aria-label="{{ __('Quel jet') }}">
                                    <p class="text-xs text-stone-500" x-show="needsThreshold && threshold === ''" x-cloak>{{ __('Sans seuil, chaque joueur saisit le sien. Chacun peut aussi ajouter son bonus.') }}</p>
                                    <label class="flex items-center gap-2 text-xs" x-show="['above', 'below'].includes(reading)" x-cloak>
                                        <input type="checkbox" x-model="hide_threshold"> {{ __('Seuil caché : les joueurs voient leurs dés, pas la réussite') }}
                                    </label>
                                    <fieldset>
                                        <legend class="label">{{ __('À qui') }}</legend>
                                        <div class="flex flex-wrap gap-1.5 text-xs">
                                            <button type="button" x-on:click="targets = []" class="rounded-full border px-2.5 py-1" :class="targets.length === 0 ? 'border-codex bg-codex-soft font-semibold' : 'border-stone-200 text-stone-600'">{{ __('Toute la table') }}</button>
                                            @foreach ($played as $character)
                                                <button type="button" wire:key="target-{{ $character->id }}" x-on:click="toggleTarget({{ $character->id }})" class="rounded-full border px-2.5 py-1"
                                                    :class="targets.includes({{ $character->id }}) ? 'border-codex bg-codex-soft font-semibold' : 'border-stone-200 text-stone-600'">{{ $character->entity->name }}</button>
                                            @endforeach
                                        </div>
                                        @if ($played->isEmpty())
                                            <p class="mt-1 text-xs text-stone-500">{{ __('Aucun personnage n’est encore joué dans cette campagne.') }}</p>
                                        @endif
                                    </fieldset>
                                </div>
                            @endif
                        @endif
                    </div>

                    @if ($tab !== 'history')
                        {{-- Toujours visible : qui voit le résultat, et le bouton. --}}
                        <footer class="space-y-2 border-t border-stone-200 bg-stone-50 p-3">
                            @if ($tab === 'roll')
                                <div class="flex gap-1.5 text-xs" role="group" aria-label="{{ __('Qui voit le résultat') }}">
                                    <button type="button" x-on:click="visibility = 'table'" class="flex-1 rounded-lg border px-2 py-1.5" :class="visibility === 'table' ? 'border-codex bg-white font-semibold' : 'border-stone-200 text-stone-600'">{{ __('Toute la table') }}</button>
                                    <button type="button" x-on:click="visibility = 'gm'" class="flex-1 rounded-lg border px-2 py-1.5" :class="visibility === 'gm' ? 'border-codex bg-white font-semibold' : 'border-stone-200 text-stone-600'">
                                        {{ $this->isGameMaster ? __('Caché (vous seul)') : __('Discret (le MJ et vous)') }}
                                    </button>
                                </div>
                                @error('threshold') <p class="error">{{ $message }}</p> @enderror
                                <button type="button" x-on:click="$wire.roll(formula, visibility, label)" wire:loading.attr="disabled" class="btn-primary w-full justify-center gap-2 py-2.5 text-base">
                                    {{ __('Lancer') }} <span class="font-normal opacity-80" translate="no" x-text="notation"></span>
                                </button>
                            @else
                                <div class="flex gap-1.5 text-xs" role="group" aria-label="{{ __('Qui voit les résultats') }}">
                                    <button type="button" x-on:click="visibility = 'table'" class="flex-1 rounded-lg border px-2 py-1.5" :class="visibility === 'table' ? 'border-codex bg-white font-semibold' : 'border-stone-200 text-stone-600'">{{ __('Résultats à la vue de tous') }}</button>
                                    <button type="button" x-on:click="visibility = 'gm'" class="flex-1 rounded-lg border px-2 py-1.5" :class="visibility === 'gm' ? 'border-codex bg-white font-semibold' : 'border-stone-200 text-stone-600'">{{ __('Discret : vous et le joueur') }}</button>
                                </div>
                                @error('threshold') <p class="error">{{ $message }}</p> @enderror
                                @error('targets') <p class="error">{{ $message }}</p> @enderror
                                <button type="button" x-on:click="$wire.ask(formula, label, targets.length ? targets : null, visibility)" @disabled($played->isEmpty()) class="btn-primary w-full justify-center py-2.5 disabled:opacity-40">{{ __('Demander le lancer') }}</button>
                            @endif
                        </footer>
                    @endif
                </section>
            @endif
        </div>

        @if ($answered || $prompt)
            {{-- Le MJ demande un lancer : la fenêtre s'ouvre où que soit le joueur. --}}
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="dice-prompt-title">
                <div class="max-h-[85vh] w-full max-w-md overflow-y-auto rounded-xl border border-stone-200 bg-white p-6 shadow-xl">
                    @if ($answered)
                        <div wire:key="answered-{{ $answered['id'] }}">
                            <p class="text-sm font-medium text-flow">{{ $answered['who'] }}</p>
                            <h2 id="dice-prompt-title" class="text-xl font-semibold">{{ $answered['label'] }}</h2>
                            <x-dice-result :roll="$answered" rolling large class="mt-4" />
                            <p class="mt-2 text-center text-xs text-stone-500">{{ $answered['describe'] }}@if ($answered['discreet']) · {{ __('Seuls le MJ et vous voyez ce résultat.') }}@endif</p>
                            <button type="button" wire:click="dismissAnswer" class="btn-primary mt-5 w-full justify-center" x-init="$nextTick(() => $el.focus())">{{ $prompt ? __('Lancer suivant') : __('Fermer') }}</button>
                        </div>
                    @else
                        @php($request = $prompt['request'])
                        @php($formula = \App\Support\Dice\SheetValues::apply($request->formula(), $campaign, $prompt['character']))
                        <form wire:submit="answer" wire:key="prompt-{{ $request->id }}-{{ $prompt['character']->id }}">
                            <p class="text-sm font-medium text-flow">{{ __('Le MJ demande un lancer') }} · {{ $prompt['character']->entity->name }}</p>
                            <h2 id="dice-prompt-title" class="mt-1 text-2xl font-semibold">{{ $request->label }}</h2>
                            <p class="mt-1 text-sm text-stone-600">{{ $formula->describe(! $formula->hideThreshold) }}</p>
                            <div class="mt-4 flex justify-center gap-2" aria-hidden="true">
                                @foreach (collect($formula->pool())->flatMap(fn ($kind) => array_fill(0, $kind['count'], $kind['sides']))->take(8) as $sides)
                                    <x-die :sides="$sides" value="?" size="size-12" />
                                @endforeach
                            </div>
                            <div class="mt-4 flex flex-wrap justify-center gap-3">
                                @if ($formula->needsThreshold() && $formula->threshold === null)
                                    <label class="flex flex-col gap-1 text-sm">
                                        <span class="label mb-0">{{ __('Votre seuil') }}</span>
                                        <input type="text" inputmode="numeric" wire:model="answerThreshold" maxlength="5" class="field w-24 text-center" x-init="$nextTick(() => $el.focus())">
                                    </label>
                                @endif
                                @if (in_array($formula->reading, ['sum', 'above', 'below', 'keep'], true))
                                    <label class="flex flex-col gap-1 text-sm">
                                        <span class="label mb-0">{{ $formula->reading === 'below' ? __('Votre bonus au seuil') : __('Votre bonus') }}</span>
                                        <input type="text" inputmode="numeric" wire:model="answerBonus" maxlength="5" class="field w-24 text-center" placeholder="0">
                                    </label>
                                @endif
                            </div>
                            @error('answerThreshold') <p class="error mt-2 text-center">{{ $message }}</p> @enderror
                            @error('answerBonus') <p class="error mt-2 text-center">{{ $message }}</p> @enderror
                            @error('answer') <p class="error mt-2 text-center">{{ $message }}</p> @enderror
                            <button type="submit" wire:loading.attr="disabled" class="btn-primary mt-5 w-full justify-center py-3 text-lg">{{ __('Lancer les dés') }}</button>
                            <p class="mt-2 text-center text-xs text-stone-500">{{ $request->visibility === 'gm' ? __('Discret : seuls le MJ et vous verrez le résultat.') : __('Le résultat sera visible de toute la table.') }}</p>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
