<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('my-sheets.index') }}" class="crumb" wire:navigate>{{ __('Mes feuilles de personnage') }}</a>
    </nav>

    @php($sheetRows = $character->fieldRows())
    <div class="mb-6 flex flex-wrap items-center gap-4">
        @if ($character->hasPortrait())
            <img src="{{ route('my-sheets.portrait', $character) }}?v={{ $character->portrait_size }}" alt="{{ __('Portrait de :name', ['name' => $character->name]) }}" class="size-20 rounded-xl object-cover">
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <h1 class="truncate text-2xl font-semibold">{{ $character->name }}</h1>
                <x-page-help topic="my-sheets" />
            </div>
            @if ($character->game || $character->table_name)
                <p class="text-sm text-stone-600">{{ collect([$character->game, $character->table_name])->filter()->implode(' · ') }}</p>
            @endif
            <p class="text-xs text-stone-500">{{ __('Visible de vous seul') }}</p>
        </div>
        @unless ($editing)
            <span class="flex gap-2">
                <button type="button" wire:click="edit" class="btn-secondary">{{ __('Modifier') }}</button>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer « :name », sa feuille PDF et ses notes ? C’est définitif.', ['name' => $character->name]) }}" class="btn-secondary text-red-700">{{ __('Supprimer') }}</button>
            </span>
        @endunless
    </div>

    <div class="grid gap-6 *:min-w-0 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($editing)
                <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="name" class="label">{{ __('Nom') }}</label>
                            <input id="name" type="text" wire:model="name" class="field" maxlength="255" required>
                            @error('name') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="gameName" class="label">{{ __('Jeu') }}</label>
                            <input id="gameName" type="text" wire:model="gameName" class="field" maxlength="255">
                            @error('gameName') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tableName" class="label">{{ __('Table') }}</label>
                            <input id="tableName" type="text" wire:model="tableName" class="field" maxlength="255" placeholder="{{ __('Chez Paul, le jeudi…') }}">
                            @error('tableName') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset class="space-y-2">
                        <legend class="label">{{ __('Champs') }}</legend>
                        @forelse ($rows as $index => $row)
                            <div wire:key="row-{{ $index }}-{{ $row['type'] }}" class="flex flex-wrap items-start gap-2 rounded-lg border border-stone-200 p-2">
                                <div class="min-w-32 flex-1">
                                    <label for="rows-{{ $index }}-label" class="sr-only">{{ __('Nom du champ') }}</label>
                                    <input id="rows-{{ $index }}-label" type="text" wire:model="rows.{{ $index }}.label" class="field" maxlength="100" placeholder="{{ $row['type'] === 'counter' ? __('PV, Santé mentale…') : __('Force, Métier, Équipement…') }}">
                                    @error("rows.$index.label") <p class="error">{{ $message }}</p> @enderror
                                </div>
                                @if ($row['type'] === 'counter')
                                    <div class="w-24">
                                        <label for="rows-{{ $index }}-value" class="sr-only">{{ __('Valeur') }}</label>
                                        <input id="rows-{{ $index }}-value" type="number" min="0" wire:model="rows.{{ $index }}.value" class="field text-center tabular-nums">
                                        @error("rows.$index.value") <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    <span class="pt-2 text-stone-500" aria-hidden="true">/</span>
                                    <div class="w-24">
                                        <label for="rows-{{ $index }}-max" class="sr-only">{{ __('Maximum') }}</label>
                                        <input id="rows-{{ $index }}-max" type="number" min="0" wire:model="rows.{{ $index }}.max" class="field text-center tabular-nums" placeholder="{{ __('max.') }}">
                                        @error("rows.$index.max") <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                    <div class="min-w-40 flex-[2]">
                                        <label for="rows-{{ $index }}-value" class="sr-only">{{ __('Valeur') }}</label>
                                        <textarea id="rows-{{ $index }}-value" wire:model="rows.{{ $index }}.value" rows="1" class="field" maxlength="5000"></textarea>
                                        @error("rows.$index.value") <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <span class="flex items-center gap-1 pt-1">
                                    <button type="button" wire:click="moveRow({{ $index }}, -1)" @disabled($index === 0) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Monter') }}">↑</button>
                                    <button type="button" wire:click="moveRow({{ $index }}, 1)" @disabled($index === count($rows) - 1) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Descendre') }}">↓</button>
                                    <button type="button" wire:click="removeRow({{ $index }})" class="rounded px-1.5 text-stone-500 hover:bg-stone-100 hover:text-red-700" aria-label="{{ __('Retirer ce champ') }}">×</button>
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">{{ __('Aucun champ. Ajoutez les caractéristiques que vous voulez garder sous la main.') }}</p>
                        @endforelse
                        @error('rows') <p class="error">{{ $message }}</p> @enderror
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="addRow('text')" class="btn-secondary min-h-0 py-1 text-sm">{{ __('+ Texte') }}</button>
                            <button type="button" wire:click="addRow('counter')" class="btn-secondary min-h-0 py-1 text-sm">{{ __('+ Compteur') }}</button>
                        </div>
                    </fieldset>

                    <div>
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <textarea id="notes" wire:model="notes" rows="8" class="field" maxlength="50000"></textarea>
                        @error('notes') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                        <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                    </div>
                </form>
            @else
                @php($counters = collect($sheetRows)->filter(fn ($row) => $row['type'] === 'counter'))
                @php($texts = collect($sheetRows)->filter(fn ($row) => $row['type'] !== 'counter'))
                @if ($counters->isNotEmpty())
                    <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($counters as $index => $row)
                            <li wire:key="counter-{{ $index }}" class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" x-data="{ amount: 1, step() { return Math.min(1000, Math.abs(parseInt(this.amount, 10)) || 1) } }">
                                <p class="text-sm text-stone-600">{{ $row['label'] }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <button type="button" x-on:click="$wire.adjust({{ $index }}, -step())" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="{{ __('Retirer le montant à :name', ['name' => $row['label']]) }}">−</button>
                                    <p class="flex-1 text-center text-2xl font-semibold tabular-nums" aria-live="polite">{{ $row['value'].(isset($row['max']) ? ' / '.$row['max'] : '') }}</p>
                                    <button type="button" x-on:click="$wire.adjust({{ $index }}, step())" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="{{ __('Ajouter le montant à :name', ['name' => $row['label']]) }}">+</button>
                                </div>
                                <label class="mt-2 flex items-center justify-center gap-2 text-sm text-stone-600">
                                    {{ __('Montant') }}
                                    <input type="number" min="1" max="1000" inputmode="numeric" x-model="amount" x-on:focus="$el.select()" class="field w-20 py-1 text-center tabular-nums" aria-label="{{ __('Montant pour :name', ['name' => $row['label']]) }}">
                                </label>
                                @if (isset($row['max']) && $row['max'] > 0)
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                                        <div class="h-full rounded-full bg-codex" style="width: {{ min(100, max(0, $row['value'] / $row['max'] * 100)) }}%"></div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($texts->isNotEmpty())
                    <dl class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white px-4 shadow-sm">
                        @foreach ($texts as $index => $row)
                            <div wire:key="text-{{ $index }}" class="grid gap-1 py-2 sm:grid-cols-3">
                                <dt class="text-sm text-stone-600">{{ $row['label'] }}</dt>
                                <dd class="whitespace-pre-line sm:col-span-2">{{ $row['value'] !== '' ? $row['value'] : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($sheetRows === [] && ! $character->notes)
                    <div class="rounded-xl border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
                        <p>{{ __('Feuille vide. « Modifier » ajoute ses caractéristiques, compteurs et notes.') }}</p>
                    </div>
                @endif

                @if ($character->notes)
                    <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" aria-labelledby="notes-title">
                        <h2 id="notes-title" class="mb-1 font-semibold">{{ __('Notes') }}</h2>
                        <p class="whitespace-pre-line">{{ $character->notes }}</p>
                    </section>
                @endif
            @endif

            @if ($character->hasSheet())
                <section class="rounded-xl border border-stone-200 bg-white shadow-sm" aria-labelledby="sheet-title">
                    <div class="flex items-center gap-3 px-4 py-3">
                        <h2 id="sheet-title" class="min-w-0 flex-1 font-semibold">
                            {{ __('Feuille PDF') }}
                            <span class="block truncate text-sm font-normal text-stone-500" title="{{ $character->sheet_name }}">{{ $character->sheet_name }}</span>
                        </h2>
                        <a href="{{ route('my-sheets.sheet', $character) }}" target="_blank" class="link shrink-0 text-sm">{{ __('Ouvrir en grand') }}</a>
                    </div>
                    <x-pdf-viewer :url="route('my-sheets.sheet', $character)" :title="__('Feuille de :name', ['name' => $character->name])" :download="route('my-sheets.sheet', $character)" class="h-[75vh] overflow-hidden rounded-b-xl border-t border-stone-200" wire:ignore wire:key="sheet-{{ $character->sheet_size }}" />
                </section>
            @endif
        </div>

        <aside class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <div>
                <label for="sheet" class="label">{{ $character->hasSheet() ? __('Remplacer la feuille PDF') : __('Feuille PDF') }} <span class="font-normal text-stone-500">{{ __('(30 Mo max.)') }}</span></label>
                <input id="sheet" type="file" wire:model="sheet" accept="application/pdf,.pdf" class="block w-full text-sm">
                <div wire:loading wire:target="sheet" class="mt-1 text-xs text-stone-500">{{ __('Envoi en cours…') }}</div>
                @error('sheet') <p class="error">{{ $message }}</p> @enderror
                @if ($character->hasSheet())
                    <button type="button" wire:click="removeSheet" wire:confirm="{{ __('Retirer la feuille PDF ?') }}" class="mt-1 text-sm text-red-700 hover:underline">{{ __('Retirer la feuille') }}</button>
                @endif
            </div>
            <div>
                <label for="portrait" class="label">{{ $character->hasPortrait() ? __('Changer le portrait') : __('Portrait') }} <span class="font-normal text-stone-500">{{ __('(image, 5 Mo max.)') }}</span></label>
                <input id="portrait" type="file" wire:model="portrait" accept="image/*" class="block w-full text-sm">
                <div wire:loading wire:target="portrait" class="mt-1 text-xs text-stone-500">{{ __('Envoi en cours…') }}</div>
                @error('portrait') <p class="error">{{ $message }}</p> @enderror
                @if ($character->hasPortrait())
                    <button type="button" wire:click="removePortrait" class="mt-1 text-sm text-red-700 hover:underline">{{ __('Retirer le portrait') }}</button>
                @endif
            </div>
            <p class="text-xs text-stone-500">{{ __('Les fichiers comptent dans l’espace de stockage de votre compte.') }}</p>
        </aside>
    </div>
</div>
