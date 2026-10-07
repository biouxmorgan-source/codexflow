<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Mes campagnes</h1>
            <p class="mt-1 text-sm text-stone-600">Cliquez sur une campagne pour ouvrir ses fiches, ses scénarios et le mode Session.</p>
        </div>
        @unless ($creating)
            <button type="button" wire:click="$set('creating', true)" class="btn-primary">Nouvelle campagne</button>
        @endunless
    </div>

    @if (session('status'))
        <p class="mb-6 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">{{ session('status') }}</p>
    @endif

    @if ($creating)
        <form wire:submit="create" class="mb-8 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Nouvelle campagne</h2>

            <div>
                <label for="name" class="label">Nom de la campagne</label>
                <input id="name" type="text" wire:model="name" class="field" autofocus>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="label">Description <span class="font-normal text-stone-500">(facultatif)</span></label>
                <textarea id="description" wire:model="description" rows="3" class="field"></textarea>
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="gameChoice" class="label">Jeu</label>
                    <select id="gameChoice" wire:model.live="gameChoice" class="field">
                        @foreach ($this->gameSystems as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                        <option value="new">Nouveau jeu…</option>
                    </select>
                    @error('gameChoice') <p class="error">{{ $message }}</p> @enderror
                    @if ($gameChoice === 'new')
                        <input type="text" wire:model="newGameName" placeholder="Nom du jeu" aria-label="Nom du nouveau jeu" class="field mt-2">
                        @error('newGameName') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </div>

                <div>
                    <label for="worldChoice" class="label">Monde <span class="font-normal text-stone-500">(facultatif)</span></label>
                    <select id="worldChoice" wire:model.live="worldChoice" class="field">
                        <option value="">Aucun monde partagé</option>
                        @foreach ($this->worlds as $world)
                            <option value="{{ $world->id }}">{{ $world->name }}</option>
                        @endforeach
                        <option value="new">Nouveau monde…</option>
                    </select>
                    @error('worldChoice') <p class="error">{{ $message }}</p> @enderror
                    @if ($worldChoice === 'new')
                        <input type="text" wire:model="newWorldName" placeholder="Nom du monde" aria-label="Nom du nouveau monde" class="field mt-2">
                        @error('newWorldName') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Créer la campagne</button>
                <button type="button" wire:click="$set('creating', false)" class="btn-secondary">Annuler</button>
            </div>
        </form>
    @endif

    @if ($this->campaigns->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">Aucune campagne pour l'instant.</p>
            <p class="mt-1 text-stone-600">Créez votre première campagne pour commencer à préparer vos parties.</p>
        </div>
    @else
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->campaigns as $campaign)
                @php($role = $campaign->members->first()->pivot->role)
                <li wire:key="campaign-{{ $campaign->id }}" class="relative flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-codex/50 hover:shadow-md">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <h2 class="text-lg font-semibold">
                            @php($myCharacter = $campaign->playerCharacters->first())
                            @if ($role === \App\Enums\CampaignRole::GameMaster)
                                <a href="{{ route('campaigns.show', $campaign) }}" class="text-codex hover:text-ink after:absolute after:inset-0" wire:navigate>{{ $campaign->name }}</a>
                            @elseif ($myCharacter)
                                <a href="{{ route('characters.show', [$campaign, $myCharacter]) }}" class="text-codex hover:text-ink after:absolute after:inset-0" wire:navigate>{{ $campaign->name }}</a>
                            @else
                                {{ $campaign->name }}
                            @endif
                        </h2>
                        <span @class([
                            'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                            'bg-codex-soft text-codex' => $campaign->status === \App\Enums\CampaignStatus::Active,
                            'bg-stone-100 text-stone-600' => $campaign->status === \App\Enums\CampaignStatus::Archived,
                        ])>{{ $campaign->status->label() }}</span>
                    </div>
                    <dl class="space-y-1 text-sm text-stone-600">
                        <div><dt class="inline font-medium text-ink">Jeu :</dt> <dd class="inline">{{ $campaign->gameSystem->name }}</dd></div>
                        <div><dt class="inline font-medium text-ink">Monde :</dt> <dd class="inline">{{ $campaign->world?->name ?? 'aucun' }}</dd></div>
                        <div><dt class="inline font-medium text-ink">Rôle :</dt> <dd class="inline">{{ $role->label() }}</dd></div>
                    </dl>
                    @if ($role === \App\Enums\CampaignRole::Player)
                        <p class="mt-3 text-sm text-stone-600">
                            @if ($myCharacter)
                                Votre personnage : <span class="font-medium text-codex">{{ $myCharacter->entity->name }}</span>
                            @else
                                Votre MJ ne vous a pas encore confié de personnage.
                            @endif
                        </p>
                    @endif
                    @php($unread = \App\Models\Message::unreadCount(auth()->user(), $campaign))
                    @if ($unread > 0)
                        <a href="{{ route('messages.index', $campaign) }}" class="relative z-10 mt-3 text-sm font-medium text-flow hover:underline" wire:navigate>{{ $unread }} message{{ $unread > 1 ? 's' : '' }} non lu{{ $unread > 1 ? 's' : '' }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
