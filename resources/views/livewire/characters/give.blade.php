<section class="rounded-xl border border-green-200 bg-white p-6 shadow-sm">
    <h2 class="mb-1 font-semibold">
        @switch($fixedKind)
            @case('entity') {{ __('Révéler aux joueurs') }} @break
            @case('document') {{ __('Donner aux joueurs') }} @break
            @case('rule') {{ __('Ouvrir aux joueurs') }} @break
            @default {{ __('Révéler ou donner') }}
        @endswitch
    </h2>
    <p class="mb-3 text-sm text-stone-600">
        @switch($fixedKind)
            @case('entity') {{ __('Le personnage choisi verra la zone publique de cette fiche, jamais la zone MJ.') }} @break
            @case('document') {{ __('Le document apparaîtra dans la rubrique Documents du personnage.') }} @break
            @case('rule') {{ __('Seuls les personnages choisis pourront lire cette règle (jamais les notes MJ). Les autres ne la voient pas.') }} @break
            @default {{ __('Une information apparaît dans ses Connaissances, un objet dans ses Possessions.') }}
        @endswitch
    </p>

    @if ($this->characters->isEmpty())
        <p class="text-sm text-stone-600">{!! __('Aucun personnage joueur actif. Créez-en un depuis la page :link.', ['link' => '<a href="'.e(route('characters.index', $campaign)).'" class="link" wire:navigate>'.e(__('Personnages')).'</a>']) !!}</p>
    @else
        <form wire:submit="give" class="space-y-3">
            @if ($fixedKind === '')
                <div class="flex gap-4 text-sm" role="radiogroup" aria-label="{{ __('Nature') }}">
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="kind" value="information"> {{ __('Information') }}</label>
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="kind" value="possession"> {{ __('Objet') }}</label>
                </div>
                <div>
                    <label for="give-title" class="label">{{ $kind === 'possession' ? __('Objet') : __('Titre') }}</label>
                    <input id="give-title" type="text" wire:model="title" class="field" placeholder="{{ $kind === 'possession' ? __('Lampe tempête, revolver .38…') : __('Ce que le personnage apprend') }}">
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>
                @if ($kind === 'possession')
                    <div>
                        <label for="give-quantity" class="label">{{ __('Quantité') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                        <input id="give-quantity" type="number" min="1" wire:model="quantity" class="field w-32">
                        @error('quantity') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div>
                    <label for="give-body" class="label">{{ $kind === 'possession' ? __('Description') : __('Texte') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <textarea id="give-body" wire:model="body" rows="3" class="field"></textarea>
                    @error('body') <p class="error">{{ $message }}</p> @enderror
                </div>
            @endif

            @unless ($characterId)
                <fieldset>
                    <legend class="label">{{ __('Personnages') }}</legend>
                    <div class="space-y-1">
                        @foreach ($this->characters as $character)
                            <label wire:key="give-{{ $character->id }}" class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="selected" value="{{ $character->id }}" @disabled($character->already)>
                                <span @class(['text-stone-400' => $character->already])>{{ $character->entity->name }}</span>
                                <span class="text-xs text-stone-500">{{ $character->already ? __('déjà connu') : ($character->player?->name ?? __('sans joueur')) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selected') <p class="error">{{ $message }}</p> @enderror
                </fieldset>
            @endunless

            <button type="submit" class="btn-primary w-full">{{ in_array($fixedKind === '' ? $kind : $fixedKind, ['entity', 'information', 'rule'], true) ? __('Révéler') : __('Donner') }}</button>
            @if ($flash)
                <p class="text-sm text-green-800" role="status">{{ $flash }}</p>
            @endif
        </form>
    @endif
</section>
