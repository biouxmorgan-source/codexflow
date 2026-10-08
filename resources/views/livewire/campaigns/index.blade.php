<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Mes campagnes') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Cliquez sur une campagne pour ouvrir ses fiches, ses scénarios et le mode Session.') }}</p>
        </div>
        @unless ($creating || $importing)
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="$set('importing', true)" class="btn-secondary">{{ __('Importer une campagne') }}</button>
                <button type="button" wire:click="$set('creating', true)" class="btn-primary">{{ __('Nouvelle campagne') }}</button>
            </div>
        @endunless
    </div>

    @if ($importing)
        <form wire:submit="importArchive" class="mb-8 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">{{ __('Importer une campagne') }}</h2>
            <p class="text-sm text-stone-600">{{ __('Choisissez une archive .zip téléchargée depuis « Exporter la campagne ». Elle crée une nouvelle campagne, avec son propre jeu et son propre monde, dont vous êtes le MJ.') }}</p>
            <div>
                <label for="archive" class="label">{{ __('Archive') }}</label>
                <input id="archive" type="file" wire:model="archive" accept=".zip,application/zip" class="block w-full text-sm">
                <p wire:loading wire:target="archive" class="mt-1 text-sm text-stone-500">{{ __('Envoi du fichier…') }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ __('Taille maximale acceptée par ce serveur : :size Mo.', ['size' => floor($this->maxArchiveSize / 1024)]) }}</p>
                @error('archive') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="archive,importArchive">{{ __('Importer') }}</button>
                <button type="button" wire:click="$set('importing', false)" class="btn-secondary">{{ __('Annuler') }}</button>
            </div>
            <p class="border-t border-stone-200 pt-4 text-sm text-stone-600">
                {{ __('Sans archive sous la main, chargez la campagne de démonstration : un jeu inventé, une intrigue de trois séances, des fiches, une carte, des secrets et une chronologie.') }}
                <span class="mt-2 block">@include('livewire.campaigns.demo-loader', ['class' => 'font-medium text-codex hover:underline', 'suffix' => 'Import'])</span>
            </p>
        </form>
    @endif

    @error('plan')
        <p class="mb-6 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">{{ $message }} <a href="{{ route('preferences') }}" class="underline" wire:navigate>{{ __('Voir ma formule') }}</a></p>
    @enderror
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
            <p class="mt-4 text-stone-600">{{ __('Ou chargez la campagne de démonstration : un jeu inventé et une intrigue complète, pour visiter l’application sans rien préparer.') }}</p>
            <div class="mt-3">@include('livewire.campaigns.demo-loader', ['class' => 'btn-secondary'])</div>
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
                    @if (auth()->user()->can('duplicate', $campaign) && auth()->user()->can('use-feature', ['duplication']))
                        <button type="button" wire:click="duplicate({{ $campaign->id }})" wire:confirm="{{ __('Dupliquer la campagne ? Le contenu préparé est copié ; les joueurs, les personnages, les séances et le journal ne le sont pas.') }}" class="relative z-10 mt-3 self-start text-sm link">{{ __('Dupliquer') }}</button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
