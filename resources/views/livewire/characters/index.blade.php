<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Personnages des joueurs') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        {!! __('Chaque personnage est une fiche de la campagne confiée à un joueur. Le joueur voit sa zone publique, sa feuille PDF, et peut modifier les champs que vous avez déclarés « modifiables par le joueur » dans :link (PV, munitions, argent…). Sa zone MJ lui reste toujours cachée.', ['link' => '<a href="'.e(route('fields.index', $campaign)).'" class="link" wire:navigate>'.e(__('Champs du jeu')).'</a>']) !!}
    </p>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            @if ($this->characters->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-lg font-medium">{{ __("Aucun personnage joueur pour l'instant.") }}</p>
                    <p class="mt-1 text-stone-600">{{ __('Créez-en un à droite et attribuez-le à un joueur invité.') }}</p>
                </div>
            @else
                <ul class="space-y-3">
                    @foreach ($this->characters as $character)
                        @php($entity = $character->entity)
                        <li wire:key="character-{{ $character->id }}" @class(['rounded-xl border bg-white p-4 shadow-sm', 'border-stone-200' => $character->is_active, 'border-dashed border-stone-300 opacity-80' => ! $character->is_active])>
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($entity->hasImage())
                                    <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover">
                                @else
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-stone-100 font-semibold text-stone-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($entity->name, 0, 1)) }}</span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('characters.show', [$campaign, $character]) }}" class="link text-lg" wire:navigate>{{ $entity->name }}</a>
                                    <p class="text-sm text-stone-600">
                                        {{ $character->player?->name ?? __('Sans joueur') }}
                                        · {{ $character->is_active ? __('actif') : __('au repos') }}
                                        @if ($character->locked) · <span class="font-medium text-flow">{{ __('fiche verrouillée') }}</span> @endif
                                        @if ($character->pending_count > 0) · <a href="{{ route('characters.show', [$campaign, $character]) }}#section-possession" class="font-medium text-flow hover:underline" wire:navigate>{{ trans_choice(':count objet à valider|:count objets à valider', $character->pending_count) }}</a> @endif
                                    </p>
                                </div>
                                <a href="{{ route('characters.show', [$campaign, $character, 'comme' => 1]) }}" class="btn-secondary" title="{{ __('Voir la campagne comme :name, en lecture seule', ['name' => $entity->name]) }}" wire:navigate>{{ __('Voir comme') }}</a>
                                <a href="{{ route('entities.edit', [$campaign, $entity, 'retour' => route('characters.index', $campaign, false)]) }}" class="btn-secondary" wire:navigate>{{ __('Modifier la fiche') }}</a>
                            </div>

                            <div class="mt-3 grid gap-3 border-t border-stone-100 pt-3 sm:grid-cols-2">
                                <div>
                                    <label for="player-{{ $character->id }}" class="label">{{ __('Joueur') }}</label>
                                    <select id="player-{{ $character->id }}" class="field" wire:change="assign({{ $character->id }}, $event.target.value)">
                                        <option value="">{{ __('Aucun joueur') }}</option>
                                        @foreach ($this->players as $player)
                                            <option value="{{ $player->id }}" @selected($character->user_id === $player->id)>{{ $player->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($this->players->isEmpty())
                                        <p class="mt-1 text-xs text-stone-500">{!! __("Invitez d'abord vos joueurs depuis la page :link.", ['link' => '<a href="'.e(route('members.index', $campaign)).'" class="link" wire:navigate>'.e(__('Membres')).'</a>']) !!}</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="label">{{ __('Feuille de personnage (PDF)') }}</span>
                                    @if ($character->hasSheet())
                                        <p class="flex flex-wrap items-center gap-2 text-sm">
                                            <a href="{{ route('characters.sheet', [$campaign, $character]) }}" target="_blank" class="link">{{ $character->sheet_name }}</a>
                                            <button type="button" wire:click="removeSheet({{ $character->id }})" wire:confirm="{{ __('Retirer la feuille PDF de :name ?', ['name' => $entity->name]) }}" class="text-red-700 hover:underline">{{ __('Retirer') }}</button>
                                        </p>
                                    @endif
                                    <label class="mt-1 inline-flex cursor-pointer items-center gap-2 text-sm text-codex hover:underline">
                                        <input type="file" accept="application/pdf" wire:model="sheets.{{ $character->id }}" class="sr-only">
                                        {{ $character->hasSheet() ? __('Remplacer le PDF…') : __('Joindre un PDF…') }}
                                    </label>
                                    <span wire:loading wire:target="sheets.{{ $character->id }}" class="text-sm text-stone-500">{{ __('Envoi…') }}</span>
                                    @error('sheets.'.$character->id) <p class="error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                <button type="button" wire:click="toggleLock({{ $character->id }})" class="link">{{ $character->locked ? __('Déverrouiller la fiche') : __('Verrouiller la fiche') }}</button>
                                <button type="button" wire:click="toggleActive({{ $character->id }})" class="link">{{ $character->is_active ? __('Mettre au repos') : __('Rendre actif') }}</button>
                                <button type="button" wire:click="remove({{ $character->id }})" wire:confirm="{{ __(':name ne sera plus un personnage joueur. Sa fiche reste dans la campagne.', ['name' => $entity->name]) }}" class="ml-auto text-red-700 hover:underline">{{ __('Retirer des personnages') }}</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside>
            <form wire:submit="create" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold">{{ __('Nouveau personnage') }}</h2>
                @if ($this->candidates->isNotEmpty())
                    <div>
                        <label for="entityChoice" class="label">{{ __('Fiche') }}</label>
                        <select id="entityChoice" wire:model.live="entityChoice" class="field">
                            <option value="new">{{ __('Créer une nouvelle fiche') }}</option>
                            @foreach ($this->candidates->groupBy(fn ($candidate) => $candidate->isWorldEntity() ? __('Fiches du monde (copiées dans la campagne)') : __('Fiches de la campagne')) as $group => $candidates)
                                <optgroup label="{{ $group }}">
                                    @foreach ($candidates as $candidate)
                                        <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($entityChoice === 'new')
                    <div>
                        <label for="name" class="label">{{ __('Nom du personnage') }}</label>
                        <input id="name" type="text" wire:model="name" class="field">
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div>
                    <label for="playerId" class="label">{{ __('Joueur') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <select id="playerId" wire:model="playerId" class="field">
                        <option value="">{{ __('Plus tard') }}</option>
                        @foreach ($this->players as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                    @error('playerId') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">{{ __('Ajouter le personnage') }}</button>
                <p class="text-xs text-stone-500">{{ __('Portrait, description et caractéristiques se remplissent ensuite avec « Modifier la fiche ».') }}</p>
            </form>
        </aside>
    </div>
</div>
