<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Mes campagnes') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Cliquez sur une campagne pour ouvrir ses fiches, ses scénarios et le mode Session.') }}</p>
        </div>
        @unless ($creating)
            <button type="button" wire:click="$set('creating', true)" class="btn-primary">{{ __('Nouvelle campagne') }}</button>
        @endunless
    </div>

    @if (session('status'))
        <p class="mb-6 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">{{ session('status') }}</p>
    @endif

    @if ($creating)
        <form wire:submit="create" class="mb-8 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">{{ __('Nouvelle campagne') }}</h2>

            <div>
                <label for="name" class="label">{{ __('Nom de la campagne') }}</label>
                <input id="name" type="text" wire:model="name" class="field" autofocus>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="label">{{ __('Description') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                <textarea id="description" wire:model="description" rows="3" class="field"></textarea>
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="gameChoice" class="label">{{ __('Jeu') }}</label>
                    <select id="gameChoice" wire:model.live="gameChoice" class="field">
                        @foreach ($this->gameSystems as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                        <option value="new">{{ __('Nouveau jeu…') }}</option>
                    </select>
                    @error('gameChoice') <p class="error">{{ $message }}</p> @enderror
                    @if ($gameChoice === 'new')
                        <input type="text" wire:model="newGameName" placeholder="{{ __('Nom du jeu') }}" aria-label="{{ __('Nom du nouveau jeu') }}" class="field mt-2">
                        @error('newGameName') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </div>

                <div>
                    <label for="worldChoice" class="label">{{ __('Monde') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <select id="worldChoice" wire:model.live="worldChoice" class="field">
                        <option value="">{{ __('Aucun monde partagé') }}</option>
                        @foreach ($this->worlds as $world)
                            <option value="{{ $world->id }}">{{ $world->name }}</option>
                        @endforeach
                        <option value="new">{{ __('Nouveau monde…') }}</option>
                    </select>
                    @error('worldChoice') <p class="error">{{ $message }}</p> @enderror
                    @if ($worldChoice === 'new')
                        <input type="text" wire:model="newWorldName" placeholder="{{ __('Nom du monde') }}" aria-label="{{ __('Nom du nouveau monde') }}" class="field mt-2">
                        @error('newWorldName') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Créer la campagne') }}</button>
                <button type="button" wire:click="$set('creating', false)" class="btn-secondary">{{ __('Annuler') }}</button>
            </div>
        </form>
    @endif

    @if ($this->campaigns->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __("Aucune campagne pour l'instant.") }}</p>
            <p class="mt-1 text-stone-600">{{ __('Créez votre première campagne pour commencer à préparer vos parties.') }}</p>
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
                            @elseif ($role === \App\Enums\CampaignRole::Spectator)
                                <a href="{{ route('table.screen', $campaign) }}" class="text-codex hover:text-ink after:absolute after:inset-0">{{ $campaign->name }}</a>
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
                        <div><dt class="inline font-medium text-ink">{{ __('Jeu :') }}</dt> <dd class="inline">{{ $campaign->gameSystem->name }}</dd></div>
                        <div><dt class="inline font-medium text-ink">{{ __('Monde :') }}</dt> <dd class="inline">{{ $campaign->world?->name ?? __('aucun') }}</dd></div>
                        <div><dt class="inline font-medium text-ink">{{ __('Rôle :') }}</dt> <dd class="inline">{{ $campaign->roleLabel(auth()->user(), $role) }}</dd></div>
                    </dl>
                    @if ($role === \App\Enums\CampaignRole::Player)
                        <p class="mt-3 text-sm text-stone-600">
                            @if ($myCharacter)
                                {!! __('Votre personnage : :name', ['name' => '<span class="font-medium text-codex">'.e($myCharacter->entity->name).'</span>']) !!}
                            @else
                                {{ __('Votre MJ ne vous a pas encore confié de personnage.') }}
                            @endif
                        </p>
                    @endif
                    @if ($role === \App\Enums\CampaignRole::Spectator)
                        <p class="mt-3 text-sm text-stone-600">{{ __("Vous suivez l'écran de table de cette campagne.") }}</p>
                    @endif
                    @php($unread = \App\Models\Message::unreadCount(auth()->user(), $campaign))
                    @if ($unread > 0)
                        <a href="{{ route('messages.index', $campaign) }}" class="relative z-10 mt-3 text-sm font-medium text-flow hover:underline" wire:navigate>{{ trans_choice(':count message non lu|:count messages non lus', $unread) }}</a>
                    @endif
                    @can('duplicate', $campaign)
                        <button type="button" wire:click="duplicate({{ $campaign->id }})" wire:confirm="{{ __('Dupliquer la campagne ? Le contenu préparé est copié ; les joueurs, les personnages, les séances et le journal ne le sont pas.') }}" class="relative z-10 mt-3 self-start text-sm link">{{ __('Dupliquer') }}</button>
                    @endcan
                </li>
            @endforeach
        </ul>
    @endif
</div>
