<div>
    <div class="flex items-center gap-2">
        <h1 class="text-2xl font-semibold">{{ __('Mes feuilles de personnage') }}</h1>
        <x-page-help topic="my-sheets" />
    </div>
    <p class="mb-6 text-sm text-stone-600">{{ __('Les personnages que vous jouez hors de SagaWyn, chez un ami, en club ou ailleurs. Ils restent dans votre compte et vous seul les voyez.') }}</p>

    <div class="grid gap-6 *:min-w-0 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if (count($this->games) > 1)
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach ($this->games as $gameName)
                        <button type="button" wire:click="$set('game', @js(mb_strtolower($game) === mb_strtolower($gameName) ? '' : $gameName))"
                            @class(['rounded-full px-2.5 py-0.5 text-sm', 'bg-codex text-on-accent' => mb_strtolower($game) === mb_strtolower($gameName), 'bg-stone-100 text-stone-700 hover:bg-codex-soft' => mb_strtolower($game) !== mb_strtolower($gameName)])>{{ $gameName }}</button>
                    @endforeach
                </div>
            @endif

            @if ($this->characters->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-stone-600">{{ $game !== '' ? __('Aucun personnage pour ce jeu.') : __('Aucune feuille pour l’instant. Créez votre premier personnage, puis ajoutez sa feuille PDF, ses caractéristiques et vos notes.') }}</p>
                </div>
            @else
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->characters as $character)
                        <li wire:key="personal-character-{{ $character->id }}">
                            <a href="{{ route('my-sheets.show', $character) }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-3 shadow-sm hover:border-codex">
                                @if ($character->hasPortrait())
                                    <img src="{{ route('my-sheets.portrait', $character) }}?v={{ $character->portrait_size }}" alt="" class="size-14 shrink-0 rounded-lg object-cover">
                                @else
                                    <span class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-codex-soft text-xl font-semibold text-codex" aria-hidden="true">{{ mb_strtoupper(mb_substr($character->name, 0, 1)) }}</span>
                                @endif
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{ $character->name }}</span>
                                    @if ($character->game || $character->table_name)
                                        <span class="block truncate text-sm text-stone-500">{{ collect([$character->game, $character->table_name])->filter()->implode(' · ') }}</span>
                                    @endif
                                    @if ($character->hasSheet())
                                        <span class="block text-xs text-stone-500">{{ __('Feuille PDF') }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form wire:submit="create" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('Nouveau personnage') }}</h2>
            <div>
                <label for="name" class="label">{{ __('Nom') }}</label>
                <input id="name" type="text" wire:model="name" class="field" maxlength="255" required>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="newGame" class="label">{{ __('Jeu') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                <input id="newGame" type="text" wire:model="newGame" class="field" maxlength="255" list="personal-games" placeholder="{{ __('L’Appel de Cthulhu, D&D 5e…') }}">
                <datalist id="personal-games">
                    @foreach ($this->games as $gameName)
                        <option value="{{ $gameName }}"></option>
                    @endforeach
                </datalist>
                @error('newGame') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">{{ __('Créer') }}</button>
        </form>
    </div>
</div>
