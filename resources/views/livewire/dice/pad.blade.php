{{-- Dés de la feuille personnelle : comme le plateau d'une campagne, sans table ni MJ. Le résultat reste sur la feuille. --}}
<div>
    <div @class(['fixed bottom-2 left-2 sm:bottom-4 sm:left-4', 'z-50' => $open, 'z-30' => ! $open]) x-data="diceBuilder(@js('sagawyn-dice-sheet-'.$character->id), @js(['value' => ':name', 'tens' => __('dizaine de :name'), 'half' => __('moitié de :name'), 'fifth' => __('cinquième de :name')]))"
        x-on:dice-use.window="useValue($event.detail)">
        @if (! $open)
            <button type="button" wire:click="toggle" class="flex items-center gap-2 rounded-full bg-codex px-4 py-2.5 text-sm font-semibold text-on-accent shadow-lg hover:bg-ink">
                <x-die sides="6" value="6" size="size-5" state="dropped" aria-hidden="true" />
                {{ __('Dés') }}
            </button>
        @else
            <section class="fixed inset-x-2 bottom-2 z-50 flex max-h-[85vh] flex-col overflow-hidden rounded-xl border border-stone-200 bg-white shadow-2xl sm:inset-x-auto sm:bottom-4 sm:left-4 sm:w-[27rem]" aria-label="{{ __('Dés') }}">
                <header class="flex items-center gap-2 border-b border-stone-200 bg-stone-50 px-3 py-2">
                    <h2 class="text-sm font-semibold">{{ __('Dés') }}</h2>
                    <span class="text-xs text-stone-500">{{ __('Visible de vous seul') }}</span>
                    <button type="button" wire:click="toggle" class="ml-auto rounded px-2 py-0.5 text-stone-500 hover:bg-stone-200 hover:text-ink" aria-label="{{ __('Réduire les dés') }}">—</button>
                </header>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-3">
                    @foreach ($rolls as $index => $item)
                        @if ($index === 0)
                            <div wire:key="pad-roll-{{ $item['id'] }}" class="rounded-xl bg-stone-50 p-3" role="status" x-init="$el.scrollIntoView({ block: 'nearest' })">
                                @if ($item['label'])
                                    <p class="text-center text-sm font-semibold">{{ $item['label'] }}</p>
                                @endif
                                <x-dice-result :roll="$item" rolling />
                                <p class="mt-1 text-center text-xs text-stone-500">{{ $item['describe'] }}</p>
                            </div>
                            @if (count($rolls) > 1)
                                <div class="flex items-baseline justify-between">
                                    <p class="label mb-0">{{ __('Derniers lancers') }}</p>
                                    <button type="button" wire:click="clear" class="link text-xs">{{ __('Effacer') }}</button>
                                </div>
                            @endif
                        @else
                            <p wire:key="pad-roll-{{ $item['id'] }}" class="flex items-baseline justify-between gap-2 border-b border-stone-100 pb-1 text-sm">
                                <span class="min-w-0 truncate text-stone-600">{{ $item['at'] }} · {{ $item['label'] ?? $item['describe'] }}</span>
                                <span @class(['shrink-0 font-semibold', 'text-wyn' => $item['result']['success'] === true, 'text-flow' => $item['result']['success'] === false])>{{ $item['headline'] }}</span>
                            </p>
                        @endif
                    @endforeach

                    @if ($presets !== [])
                        <div>
                            <p class="label">{{ __('Lancers de cette feuille') }}</p>
                            <ul class="flex flex-wrap gap-1.5">
                                @foreach ($presets as $preset)
                                    <li wire:key="pad-preset-{{ $preset['id'] }}" class="inline-flex items-center rounded-full border text-xs"
                                        :class="loaded?.id === @js($preset['id']) ? 'border-codex bg-codex text-on-accent' : 'border-codex/40 bg-codex-soft'">
                                        <button type="button" x-on:click="loadPreset(@js($preset['formula']), @js($preset['label']), 'sheet', @js($preset['id']))" class="px-2.5 py-1 font-medium"
                                            title="{{ \App\Support\Dice\DiceFormula::fromArray($preset['formula'])->describe() }}">{{ $preset['label'] }}</button>
                                        <button type="button" wire:click="removePreset(@js($preset['id']))" x-on:click="if (loaded?.id === @js($preset['id'])) loaded = null" wire:confirm="{{ __('Supprimer ce lancer enregistré ?') }}" class="px-2 py-1 text-sm leading-none hover:text-red-700" aria-label="{{ __('Supprimer :name', ['name' => $preset['label']]) }}" title="{{ __('Supprimer :name', ['name' => $preset['label']]) }}">×</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('livewire.dice.partials.builder', ['sheetFields' => []])

                    <input type="text" x-model="label" maxlength="100" class="field py-1.5 text-sm" placeholder="{{ __('Pour quoi ? (facultatif) : Attaque, Fouille…') }}" aria-label="{{ __('Pour quoi') }}">
                    {{-- Un lancer qu'on refait souvent avec ce personnage : on l'enregistre avec la feuille. --}}
                    <div class="space-y-1.5 rounded-lg bg-stone-50 p-2">
                        <p class="label mb-0">{{ __('Enregistrer ce lancer') }}</p>
                        <input type="text" x-model="presetName" maxlength="60" class="field py-1 text-xs" placeholder="{{ __('Nom : Attaque, Sauvegarde…') }}" aria-label="{{ __('Nom du lancer enregistré') }}">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" x-show="loaded" x-cloak x-on:click="$wire.savePreset(formula, presetName || loaded.label, loaded.id).then((id) => { if (id) loaded.label = presetName || loaded.label })"
                                class="btn-secondary py-1 text-xs"><span x-text="@js(__('Mettre à jour')) + ' « ' + (loaded?.label ?? '') + ' »'"></span></button>
                            <button type="button" x-on:click="$wire.savePreset(formula, presetName, null).then((id) => { if (id) loaded = { id, scope: 'sheet', label: presetName } })"
                                class="btn-secondary py-1 text-xs" x-text="loaded ? @js(__('Enregistrer comme nouveau')) : @js(__('Enregistrer'))"></button>
                            <button type="button" x-show="loaded" x-cloak
                                x-on:click="if (confirm(@js(__('Supprimer ce lancer enregistré ?')))) $wire.removePreset(loaded.id).then(() => { loaded = null; presetName = '' })"
                                class="rounded-md border border-red-200 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50"><span x-text="@js(__('Supprimer')) + ' « ' + (loaded?.label ?? '') + ' »'"></span></button>
                        </div>
                        @error('presetLabel') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <footer class="space-y-2 border-t border-stone-200 bg-stone-50 p-3">
                    @error('threshold') <p class="error">{{ $message }}</p> @enderror
                    <button type="button" x-on:click="$wire.roll(formula, label)" wire:loading.attr="disabled" class="btn-primary w-full justify-center gap-2 py-2.5 text-base">
                        {{ __('Lancer') }} <span class="font-normal opacity-80" translate="no" x-text="notation"></span>
                    </button>
                </footer>
            </section>
        @endif
    </div>
</div>
