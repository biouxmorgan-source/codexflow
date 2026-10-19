<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('my-sheets.index') }}" class="crumb" wire:navigate>{{ __('Mes feuilles de personnage') }}</a>
    </nav>

    @php($sheetTabs = $editing ? $tabs : $character->tabList())
    @php($currentTab = in_array($tab, array_column($sheetTabs, 'id'), true) ? $tab : $sheetTabs[0]['id'])
    @php($sheetBlocks = collect($character->blockList())->where('tab', $currentTab)->values()->all())
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
            <span class="flex flex-wrap gap-2">
                @if ($sheetBlocks !== [])
                    <button type="button" wire:click="toggleArranging" aria-pressed="{{ $arranging ? 'true' : 'false' }}" @class(['btn-secondary', 'border-codex text-codex' => $arranging])>{{ $arranging ? __('Terminé') : __('Organiser les blocs') }}</button>
                @endif
                <button type="button" wire:click="edit" class="btn-secondary">{{ __('Modifier') }}</button>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer « :name », sa feuille PDF et ses notes ? C’est définitif.', ['name' => $character->name]) }}" class="btn-secondary text-red-700">{{ __('Supprimer') }}</button>
            </span>
        @endunless
    </div>

    @if (count($sheetTabs) > 1)
        <nav class="mb-4 flex gap-1 overflow-x-auto border-b border-stone-200 pb-2" aria-label="{{ __('Onglets de la feuille') }}">
            @foreach ($sheetTabs as $t => $sheetTab)
                <button type="button" wire:key="tab-{{ $sheetTab['id'] }}" wire:click="showTab('{{ $sheetTab['id'] }}')"
                    @class(['shrink-0 rounded-md px-3 py-1.5 text-sm', 'bg-codex text-on-accent' => $currentTab === $sheetTab['id'], 'text-stone-700 hover:bg-codex-soft' => $currentTab !== $sheetTab['id']])
                    @if ($currentTab === $sheetTab['id']) aria-current="true" @endif>{{ \App\Models\PersonalCharacter::tabLabel($sheetTab, $t) }}</button>
            @endforeach
        </nav>
    @endif

    <div class="space-y-6">
        <div class="space-y-6">
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

                    <fieldset class="space-y-2 rounded-xl border border-stone-200 p-3">
                        <legend class="px-1 text-sm font-semibold">{{ __('Onglets') }}</legend>
                        @foreach ($tabs as $t => $sheetTab)
                            <div wire:key="edit-tab-{{ $sheetTab['id'] }}" class="flex flex-wrap items-center gap-2">
                                <label for="tabs-{{ $t }}-name" class="sr-only">{{ __('Nom de l’onglet') }}</label>
                                <input id="tabs-{{ $t }}-name" type="text" wire:model.blur="tabs.{{ $t }}.name" class="field min-w-40 flex-1" maxlength="40" placeholder="{{ __('Onglet :number : Personnage, Sorts, Inventaire…', ['number' => $t + 1]) }}">
                                <span class="flex items-center gap-1">
                                    <button type="button" wire:click="moveTab({{ $t }}, -1)" @disabled($t === 0) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Monter l’onglet') }}">↑</button>
                                    <button type="button" wire:click="moveTab({{ $t }}, 1)" @disabled($t === count($tabs) - 1) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Descendre l’onglet') }}">↓</button>
                                    @if (count($tabs) > 1)
                                        <button type="button" wire:click="removeTab({{ $t }})" wire:confirm="{{ __('Retirer cet onglet ? Ses blocs passent dans le premier onglet.') }}" class="rounded px-1.5 text-stone-500 hover:bg-stone-100 hover:text-red-700" aria-label="{{ __('Retirer l’onglet') }}">×</button>
                                    @endif
                                </span>
                                @error("tabs.$t.name") <p class="error w-full">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        @error('tabs') <p class="error">{{ $message }}</p> @enderror
                        @if (count($tabs) < \App\Models\PersonalCharacter::MAX_TABS)
                            <button type="button" wire:click="addTab" class="btn-secondary min-h-0 py-1 text-sm">{{ __('+ Nouvel onglet') }}</button>
                        @endif
                    </fieldset>

                    <div class="space-y-4">
                        @if (count($tabs) > 1)
                            <p class="text-sm text-stone-600">{{ __('Blocs de l’onglet « :name ». Les onglets au-dessus montrent les autres.', ['name' => \App\Models\PersonalCharacter::tabLabel(collect($tabs)->firstWhere('id', $currentTab), collect($tabs)->search(fn ($item) => $item['id'] === $currentTab))]) }}</p>
                        @endif
                        @foreach ($blocks as $b => $block)
                            @continue($block['tab'] !== $currentTab)
                            <fieldset wire:key="edit-block-{{ $block['id'] }}" class="space-y-2 rounded-xl border border-stone-200 bg-stone-50 p-3">
                                <legend class="sr-only">{{ $block['title'] !== '' ? $block['title'] : __('Bloc :number', ['number' => $b + 1]) }}</legend>
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <label for="blocks-{{ $b }}-title" class="sr-only">{{ __('Titre du bloc') }}</label>
                                        <input id="blocks-{{ $b }}-title" type="text" wire:model="blocks.{{ $b }}.title" class="field font-semibold" maxlength="100" placeholder="{{ __('Titre du bloc : Caractéristiques, Compétences, Équipement…') }}">
                                        @error("blocks.$b.title") <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    @if (count($tabs) > 1)
                                        <label class="flex items-center gap-1.5 pt-1 text-sm text-stone-600">
                                            <span class="sr-only">{{ __('Onglet du bloc') }}</span>
                                            <select wire:model.live="blocks.{{ $b }}.tab" class="field min-h-0 w-auto py-1 text-sm">
                                                @foreach ($tabs as $t => $sheetTab)
                                                    <option value="{{ $sheetTab['id'] }}">{{ \App\Models\PersonalCharacter::tabLabel($sheetTab, $t) }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                    <button type="button" wire:click="removeBlock({{ $b }})" @if ($block['rows'] !== [] || $block['text'] !== '') wire:confirm="{{ __('Retirer ce bloc et ses champs ?') }}" @endif class="rounded px-1.5 pt-2 text-sm text-stone-500 hover:text-red-700">{{ __('Retirer le bloc') }}</button>
                                </div>
                                @if ($block['kind'] === 'note')
                                    <div>
                                        <label for="blocks-{{ $b }}-text" class="sr-only">{{ __('Notes') }}</label>
                                        <textarea id="blocks-{{ $b }}-text" wire:model="blocks.{{ $b }}.text" rows="6" class="field" maxlength="50000"></textarea>
                                        @error("blocks.$b.text") <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                @forelse ($block['rows'] as $r => $row)
                                    <div wire:key="row-{{ $block['id'] }}-{{ $r }}-{{ $row['type'] }}" class="space-y-2 rounded-lg border border-stone-200 bg-white p-2">
                                        <div class="flex flex-wrap items-start gap-2">
                                            <div class="min-w-32 flex-1">
                                                <label for="blocks-{{ $b }}-rows-{{ $r }}-label" class="sr-only">{{ __('Nom du champ') }}</label>
                                                <input id="blocks-{{ $b }}-rows-{{ $r }}-label" type="text" wire:model="blocks.{{ $b }}.rows.{{ $r }}.label" class="field" maxlength="100" placeholder="{{ $row['type'] === 'counter' ? __('PV, Santé mentale…') : __('Force, Métier, Équipement…') }}">
                                                @error("blocks.$b.rows.$r.label") <p class="error">{{ $message }}</p> @enderror
                                            </div>
                                            @if ($row['type'] === 'counter')
                                                <div class="w-24">
                                                    <label for="blocks-{{ $b }}-rows-{{ $r }}-value" class="sr-only">{{ __('Valeur') }}</label>
                                                    <input id="blocks-{{ $b }}-rows-{{ $r }}-value" type="number" min="0" wire:model="blocks.{{ $b }}.rows.{{ $r }}.value" class="field text-center tabular-nums">
                                                    @error("blocks.$b.rows.$r.value") <p class="error">{{ $message }}</p> @enderror
                                                </div>
                                                <span class="pt-2 text-stone-500" aria-hidden="true">/</span>
                                                <div class="w-24">
                                                    <label for="blocks-{{ $b }}-rows-{{ $r }}-max" class="sr-only">{{ __('Maximum') }}</label>
                                                    <input id="blocks-{{ $b }}-rows-{{ $r }}-max" type="number" min="0" wire:model="blocks.{{ $b }}.rows.{{ $r }}.max" class="field text-center tabular-nums" placeholder="{{ __('max.') }}">
                                                    @error("blocks.$b.rows.$r.max") <p class="error">{{ $message }}</p> @enderror
                                                </div>
                                            @else
                                                <div class="min-w-40 flex-[2]">
                                                    <label for="blocks-{{ $b }}-rows-{{ $r }}-value" class="sr-only">{{ __('Valeur') }}</label>
                                                    <textarea id="blocks-{{ $b }}-rows-{{ $r }}-value" wire:model="blocks.{{ $b }}.rows.{{ $r }}.value" rows="1" class="field" maxlength="5000"></textarea>
                                                    @error("blocks.$b.rows.$r.value") <p class="error">{{ $message }}</p> @enderror
                                                </div>
                                            @endif
                                            <span class="flex items-center gap-1 pt-1">
                                                <button type="button" wire:click="moveRow({{ $b }}, {{ $r }}, -1)" @disabled($r === 0) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Monter') }}">↑</button>
                                                <button type="button" wire:click="moveRow({{ $b }}, {{ $r }}, 1)" @disabled($r === count($block['rows']) - 1) class="rounded px-1.5 text-stone-500 hover:bg-stone-100 disabled:opacity-30" aria-label="{{ __('Descendre') }}">↓</button>
                                                <button type="button" wire:click="removeRow({{ $b }}, {{ $r }})" class="rounded px-1.5 text-stone-500 hover:bg-stone-100 hover:text-red-700" aria-label="{{ __('Retirer ce champ') }}">×</button>
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                            <div class="min-w-48 flex-1">
                                                <label for="blocks-{{ $b }}-rows-{{ $r }}-note" class="sr-only">{{ __('Note') }}</label>
                                                <input id="blocks-{{ $b }}-rows-{{ $r }}-note" type="text" wire:model="blocks.{{ $b }}.rows.{{ $r }}.note" class="field py-1 text-sm" maxlength="2000" placeholder="{{ __('Note (facultatif)') }}">
                                                @error("blocks.$b.rows.$r.note") <p class="error">{{ $message }}</p> @enderror
                                            </div>
                                            <label class="flex items-center gap-1.5 text-sm text-stone-700"><input type="checkbox" wire:model="blocks.{{ $b }}.rows.{{ $r }}.checkbox"> {{ __('Case à cocher') }}</label>
                                            @php($targets = collect($blocks)->where('kind', 'fields')->where('tab', $currentTab)->except($b))
                                            @if ($targets->isNotEmpty())
                                                <label class="flex items-center gap-1.5 text-sm text-stone-600">
                                                    <span class="sr-only">{{ __('Ranger dans un autre bloc') }}</span>
                                                    <select wire:change="moveRowToBlock({{ $b }}, {{ $r }}, $event.target.value)" class="field min-h-0 w-auto py-1 text-sm">
                                                        <option value="{{ $b }}">{{ __('Ranger dans…') }}</option>
                                                        @foreach ($targets as $t => $other)
                                                            <option value="{{ $t }}">{{ $other['title'] !== '' ? $other['title'] : __('Bloc :number', ['number' => $t + 1]) }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-stone-500">{{ __('Bloc vide. Ajoutez-y un texte ou un compteur.') }}</p>
                                @endforelse
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="addRow({{ $b }}, 'text')" class="btn-secondary min-h-0 py-1 text-sm">{{ __('+ Texte') }}</button>
                                    <button type="button" wire:click="addRow({{ $b }}, 'counter')" class="btn-secondary min-h-0 py-1 text-sm">{{ __('+ Compteur') }}</button>
                                </div>
                                @endif
                            </fieldset>
                        @endforeach
                        @error('blocks') <p class="error">{{ $message }}</p> @enderror
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="addBlock" class="btn-secondary">{{ __('+ Nouveau bloc') }}</button>
                            <button type="button" wire:click="addBlock('note')" class="btn-secondary">{{ __('+ Nouvelle note') }}</button>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                        <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                    </div>
                </form>
            @else
                @if ($arranging)
                    <p class="rounded-xl border border-codex/30 bg-codex-soft/50 p-3 text-sm text-stone-700" role="status">{{ __('Glissez un bloc par sa poignée ⠿ (ou utilisez les flèches) pour le déplacer, y compris d’une colonne à l’autre.') }}</p>
                @endif
                @if ($sheetBlocks !== [])
                    @php($shown = $arranging ? 3 : $character->usedColumns($currentTab))
                    <div x-data="screenArranger" @class(['grid gap-4', 'items-start' => ! $arranging, 'sm:grid-cols-2' => $shown >= 2, 'lg:grid-cols-3' => $shown === 3])>
                        @foreach (\App\Models\PersonalCharacter::COLUMNS as $position => $column)
                            <div data-screen-column="{{ $column }}" wire:key="column-{{ $column }}" @class(['space-y-4', 'hidden' => $position >= $shown, 'min-h-24 rounded-xl border-2 border-dashed border-stone-200 p-1' => $arranging])>
                                @foreach (collect($sheetBlocks)->where('column', $column) as $block)
                                    @php($title = $block['title'] !== '' ? $block['title'] : __('Sans titre'))
                                    <section wire:key="block-{{ $block['id'] }}" data-block="{{ $block['id'] }}" aria-label="{{ $title }}" @class(['rounded-xl border border-stone-200 bg-white p-4 shadow-sm', 'border-2 border-dashed border-codex/40' => $arranging])>
                                        @if ($arranging)
                                            <div class="mb-2 flex items-center gap-1.5 text-sm">
                                                <button type="button" x-on:pointerdown="start($event)" class="cursor-grab touch-none rounded-md px-1.5 text-lg leading-none text-stone-500 hover:bg-codex-soft hover:text-codex active:cursor-grabbing" aria-label="{{ __('Déplacer « :name »', ['name' => $title]) }}" title="{{ __('Glisser pour déplacer') }}">⠿</button>
                                                <span class="mr-auto font-medium text-stone-700">{{ $title }}</span>
                                                <button type="button" x-on:click="move($el, -1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Monter « :name »', ['name' => $title]) }}">↑</button>
                                                <button type="button" x-on:click="move($el, 1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Descendre « :name »', ['name' => $title]) }}">↓</button>
                                            </div>
                                        @elseif ($block['title'] !== '')
                                            <h2 class="mb-2 font-semibold">{{ $block['title'] }}</h2>
                                        @endif
                                        @if ($block['kind'] === 'note')
                                            <p class="whitespace-pre-line">{{ $block['text'] !== '' ? $block['text'] : '—' }}</p>
                                        @elseif ($block['rows'] === [])
                                            <p class="text-sm text-stone-500">{{ __('Bloc vide.') }}</p>
                                        @else
                                            <ul class="divide-y divide-stone-100">
                                                @foreach ($block['rows'] as $r => $row)
                                                    <li wire:key="line-{{ $block['id'] }}-{{ $r }}" class="py-2 first:pt-0 last:pb-0" @if ($row['type'] === 'counter') x-data="{ amount: 1, step() { return Math.min(1000, Math.abs(parseInt(this.amount, 10)) || 1) } }" @endif>
                                                        <div class="flex items-start gap-2">
                                                            @if ($row['checkbox'])
                                                                <input type="checkbox" @checked($row['checked']) wire:click="toggleCheck('{{ $block['id'] }}', {{ $r }})" class="mt-1 size-4 shrink-0" aria-label="{{ __('Cocher « :name »', ['name' => $row['label']]) }}">
                                                            @endif
                                                            <div class="min-w-0 flex-1">
                                                                @if ($row['type'] === 'counter')
                                                                    <p class="text-sm text-stone-600">{{ $row['label'] }}</p>
                                                                    <div class="mt-1 flex items-center gap-2">
                                                                        <button type="button" x-on:click="$wire.adjust('{{ $block['id'] }}', {{ $r }}, -step())" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="{{ __('Retirer le montant à :name', ['name' => $row['label']]) }}">−</button>
                                                                        <p class="flex-1 text-center text-2xl font-semibold tabular-nums" aria-live="polite">{{ $row['value'].($row['max'] !== null ? ' / '.$row['max'] : '') }}</p>
                                                                        <button type="button" x-on:click="$wire.adjust('{{ $block['id'] }}', {{ $r }}, step())" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="{{ __('Ajouter le montant à :name', ['name' => $row['label']]) }}">+</button>
                                                                    </div>
                                                                    <label class="mt-2 flex items-center justify-center gap-2 text-sm text-stone-600">
                                                                        {{ __('Montant') }}
                                                                        <input type="number" min="1" max="1000" inputmode="numeric" x-model="amount" x-on:focus="$el.select()" class="field w-20 py-1 text-center tabular-nums" aria-label="{{ __('Montant pour :name', ['name' => $row['label']]) }}">
                                                                    </label>
                                                                    @if ($row['max'] > 0)
                                                                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                                                                            <div class="h-full rounded-full bg-codex" style="width: {{ min(100, max(0, $row['value'] / $row['max'] * 100)) }}%"></div>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="grid gap-x-3 gap-y-0.5 sm:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                                                                        <span class="text-sm text-stone-600">{{ $row['label'] }}</span>
                                                                        <span class="whitespace-pre-line">{{ $row['value'] !== '' ? $row['value'] : '—' }}</span>
                                                                    </div>
                                                                @endif
                                                                @if ($row['note'] !== '')
                                                                    <p class="mt-1 text-xs whitespace-pre-line text-stone-500">{{ $row['note'] }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </section>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($sheetBlocks === [])
                    <div class="rounded-xl border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
                        <p>{{ count($sheetTabs) > 1 ? __('Onglet vide. « Modifier » y ajoute des blocs.') : __('Feuille vide. « Modifier » ajoute ses blocs, caractéristiques, compteurs et notes.') }}</p>
                    </div>
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

        <aside class="grid gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm sm:grid-cols-2" aria-label="{{ __('Fichiers') }}">
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
            <p class="text-xs text-stone-500 sm:col-span-2">{{ __('Les fichiers comptent dans l’espace de stockage de votre compte.') }}</p>
        </aside>
    </div>
</div>
