<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Personnages des joueurs') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        {!! __('Chaque personnage est une fiche de la campagne confiée à un joueur. Le joueur voit sa zone publique, sa feuille PDF, et peut modifier les champs que vous avez déclarés « modifiables par le joueur » dans :link (PV, munitions, argent…). Sa zone MJ lui reste toujours cachée.', ['link' => '<a href="'.e(route('fields.index', $campaign)).'" class="link" wire:navigate>'.e(__('Champs du jeu')).'</a>']) !!}
    </p>

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    <label class="mb-6 flex items-start gap-2 text-sm text-stone-700">
        <input type="checkbox" wire:click="toggleExchangeApproval" @checked($campaign->exchanges_need_approval) class="mt-0.5">
        <span>{{ __('Valider les échanges entre joueurs') }} <span class="block text-stone-500">{{ __('Décoché, les joueurs se donnent objets et connaissances sans attendre votre accord ; vous en êtes informé.') }}</span></span>
    </label>

    @foreach ($this->returning as $character)
        <div wire:key="returning-{{ $character->id }}" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900" role="status">
            <span>{{ __(':player est de retour dans la campagne. :character, son ancien personnage, est sans joueur.', ['player' => $character->previousPlayer->name, 'character' => $character->entity->name]) }}</span>
            <button type="button" wire:click="giveBack({{ $character->id }})" class="btn-secondary">{{ __('Lui rendre :name', ['name' => $character->entity->name]) }}</button>
        </div>
    @endforeach

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
                                        @if ($character->player && ! $this->players->contains('id', $character->user_id))
                                            {{-- Joueur retiré de la campagne : le personnage l'attend s'il revient. --}}
                                            {{ __(':name (ne fait plus partie de la campagne)', ['name' => $character->player->name]) }}
                                        @else
                                            {{ $character->player?->name ?? __('Sans joueur') }}
                                            @if (! $character->player && $character->previousPlayer) <span class="text-stone-500">({{ __('anciennement :name', ['name' => $character->previousPlayer->name]) }})</span> @endif
                                        @endif
                                        · {{ $character->is_active ? __('actif') : __('au repos') }}
                                        @if ($character->locked) · <span class="font-medium text-flow">{{ __('fiche verrouillée') }}</span> @endif
                                        @if ($character->pending_count > 0) · <a href="{{ route('characters.show', [$campaign, $character]) }}#section-possession" class="font-medium text-flow hover:underline" wire:navigate>{{ trans_choice(':count objet à valider|:count objets à valider', $character->pending_count) }}</a> @endif
                                        @if ($character->exchange_requests_count > 0) · <a href="{{ route('characters.show', [$campaign, $character]) }}" class="font-medium text-flow hover:underline" wire:navigate>{{ trans_choice(':count échange à valider|:count échanges à valider', $character->exchange_requests_count) }}</a> @endif
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
                                        @if ($character->player && ! $this->players->contains('id', $character->user_id))
                                            <option value="" selected disabled>{{ __(':name (ne fait plus partie de la campagne)', ['name' => $character->player->name]) }}</option>
                                        @endif
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
                                @if ($character->user_id && $this->characters->where('user_id', $character->user_id)->count() > 1)
                                    <button type="button" wire:click="openTransfer({{ $character->id }})" class="link">{{ __('Reprendre d’un ancien personnage…') }}</button>
                                @endif
                                <button type="button" wire:click="remove({{ $character->id }})" wire:confirm="{{ __(':name ne sera plus un personnage joueur. Sa fiche reste dans la campagne.', ['name' => $entity->name]) }}" class="ml-auto text-red-700 hover:underline">{{ __('Retirer des personnages') }}</button>
                                @if ($entity->campaign_id === $campaign->id)
                                    <button type="button" wire:click="destroy({{ $character->id }})" wire:confirm="{{ __('Supprimer :name et sa fiche ? Ses connaissances, possessions et notes seront supprimées aussi.', ['name' => $entity->name]) }}" class="text-red-700 hover:underline">{{ __('Supprimer avec sa fiche') }}</button>
                                @endif
                            </div>
                            @if ($transferTo === $character->id)
                                <form wire:submit="transfer" class="mt-4 rounded-lg border border-codex/30 bg-codex-soft/40 p-4" aria-labelledby="transfer-title-{{ $character->id }}">
                                    <h3 id="transfer-title-{{ $character->id }}" class="font-semibold">{{ __('Ce qui passe à :name', ['name' => $entity->name]) }}</h3>
                                    <p class="mt-1 text-sm text-stone-600">{{ __('Connaissances, informations, documents et règles sont recopiés ; les objets changent de main. Décochez ce que le nouveau personnage ne doit pas reprendre.') }}</p>
                                    @if ($this->transferSources->count() > 1)
                                        <label for="transfer-from" class="label mt-3">{{ __('Ancien personnage') }}</label>
                                        <select id="transfer-from" wire:model.live="transferFrom" class="field max-w-xs">
                                            @foreach ($this->transferSources as $source)
                                                <option value="{{ $source->id }}">{{ $source->entity->name }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($this->transferSources->isNotEmpty())
                                        <p class="mt-2 text-sm">{{ __('Depuis :name', ['name' => $this->transferSources->first()->entity->name]) }}</p>
                                    @endif
                                    @if ($this->transferGrants->isEmpty())
                                        <p class="mt-3 text-sm text-stone-500">{{ __('Rien à transmettre.') }}</p>
                                    @else
                                        @foreach ($this->transferGrants->groupBy('kind') as $kind => $grants)
                                            <fieldset class="mt-3">
                                                <legend class="text-sm font-semibold text-stone-600">{{ \App\Models\CharacterGrant::kinds()[$kind] ?? $kind }}</legend>
                                                <div class="mt-1 grid gap-1 sm:grid-cols-2">
                                                    @foreach ($grants as $grant)
                                                        <label wire:key="transfer-{{ $grant->id }}" class="flex items-center gap-2 text-sm">
                                                            <input type="checkbox" value="{{ $grant->id }}" wire:model="transferIds">
                                                            <span class="min-w-0 truncate">{{ $grant->label() }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                        @endforeach
                                    @endif
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if ($this->transferGrants->isNotEmpty())
                                            <button type="submit" class="btn-primary">{{ __('Transmettre') }}</button>
                                        @endif
                                        <button type="button" wire:click="closeTransfer" class="btn-secondary">{{ __('Ne rien reprendre') }}</button>
                                    </div>
                                </form>
                            @endif
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
                        @error('entityChoice') <p class="error">{{ $message }}</p> @enderror
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

            @if ($this->unused->isNotEmpty())
                <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">{{ __('Fiches de personnage inutilisées') }}</h2>
                    <p class="mt-1 text-xs text-stone-500">{{ __('Les fiches de personnages retirés restent dans la campagne. Supprimez les doublons pour qu’ils n’apparaissent plus dans les listes (cartes, recherche…).') }}</p>
                    <ul class="mt-3 divide-y divide-stone-100 text-sm">
                        @foreach ($this->unused as $sheet)
                            <li wire:key="unused-{{ $sheet->id }}" class="flex items-center gap-3 py-2">
                                <a href="{{ route('entities.show', [$campaign, $sheet]) }}" class="min-w-0 flex-1 truncate link" wire:navigate>{{ $sheet->name }}</a>
                                <span class="shrink-0 text-xs text-stone-500">{{ $sheet->created_at?->translatedFormat('j M') }}</span>
                                <button type="button" wire:click="deleteUnused({{ $sheet->id }})" wire:confirm="{{ __('Supprimer la fiche « :name » ?', ['name' => $sheet->name]) }}" class="shrink-0 text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</div>
