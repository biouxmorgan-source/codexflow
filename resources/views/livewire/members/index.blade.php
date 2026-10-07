<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Membres') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        @can('manage', $campaign)
            {{ __("Créez un lien d'invitation par personne et envoyez-le-lui comme vous voulez (message, Discord, courriel…).") }}
            {{ __("En l'ouvrant, elle se connecte ou crée son compte, puis rejoint la campagne avec le rôle choisi. Chaque lien ne sert qu'une fois et expire après :days jours.", ['days' => \App\Models\CampaignInvitation::VALID_DAYS]) }}
        @else
            {{ __('Seul le propriétaire de la campagne invite, change les rôles et retire des membres.') }}
        @endif
    </p>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            <section>
                <h2 class="mb-2 font-semibold">{{ __('Membres de la campagne') }}</h2>
                <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
                    @foreach ($this->members as $member)
                        <li wire:key="member-{{ $member->id }}" class="flex flex-wrap items-center gap-3 px-4 py-3">
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium">{{ $member->name }}</span>
                                <span class="block truncate text-sm text-stone-600">{{ $member->email }}</span>
                            </span>
                            @if ($member->id !== $campaign->user_id && auth()->user()->can('manage', $campaign))
                                <label for="role-{{ $member->id }}" class="sr-only">{{ __('Rôle de :name', ['name' => $member->name]) }}</label>
                                <select id="role-{{ $member->id }}" wire:change="changeRole({{ $member->id }}, $event.target.value)" class="field w-auto shrink-0 py-1 text-sm">
                                    @foreach (\App\Enums\CampaignRole::cases() as $roleOption)
                                        <option value="{{ $roleOption->value }}" @selected($member->pivot->role === $roleOption)>{{ $roleOption->invitedLabel() }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="remove({{ $member->id }})" wire:confirm="{{ __('Retirer :name de la campagne ? Il n\'y aura plus accès.', ['name' => $member->name]) }}" class="shrink-0 text-sm text-red-700 hover:underline">{{ __('Retirer') }}</button>
                            @else
                                <span @class(['shrink-0 rounded-full px-2 py-0.5 text-xs font-medium', 'bg-flow/10 text-flow' => $member->pivot->role === \App\Enums\CampaignRole::GameMaster, 'bg-codex/10 text-codex' => $member->pivot->role === \App\Enums\CampaignRole::Player, 'bg-stone-100 text-stone-600' => $member->pivot->role === \App\Enums\CampaignRole::Spectator])>{{ $campaign->roleLabel($member, $member->pivot->role) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if ($this->members->count() === 1)
                    <p class="mt-2 text-sm text-stone-600">{{ __("Personne d'autre pour l'instant : créez un lien d'invitation à droite.") }}</p>
                @endif
            </section>

            <section>
                <h2 class="mb-2 font-semibold">{{ __('Invitations en attente') }}</h2>
                @if ($this->invitations->isEmpty())
                    <p class="rounded-xl border border-dashed border-stone-300 bg-white p-6 text-center text-sm text-stone-600">{{ __('Aucune invitation en attente.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($this->invitations as $invitation)
                            <li wire:key="invitation-{{ $invitation->id }}" @class(['rounded-xl border bg-white p-4 shadow-sm', 'border-flow' => $invitation->id === $createdId, 'border-stone-200' => $invitation->id !== $createdId])
                                x-data="{ copied: false }">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <span class="font-medium">{{ $invitation->label ?? __('Invitation') }}</span>
                                    <span class="text-xs text-stone-500">{{ $invitation->role->invitedLabel() }}</span>
                                    @if ($invitation->email)
                                        <span class="text-sm text-stone-600">{{ $invitation->email }}</span>
                                    @endif
                                    <span class="ml-auto text-xs text-stone-500">{{ __('expire le :date', ['date' => $invitation->expires_at->isoFormat('D MMM YYYY')]) }}</span>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <label for="invitation-link-{{ $invitation->id }}" class="sr-only">{{ __("Lien d'invitation") }}</label>
                                    <input id="invitation-link-{{ $invitation->id }}" type="text" readonly value="{{ $invitation->url() }}" x-ref="link" x-on:focus="$el.select()" class="field min-w-0 flex-1 font-mono text-xs">
                                    <button type="button" class="btn-secondary" x-on:click="navigator.clipboard.writeText($refs.link.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
                                        <span x-show="! copied">{{ __('Copier le lien') }}</span>
                                        <span x-show="copied" x-cloak>{{ __('Copié !') }}</span>
                                    </button>
                                    @can('manage', $campaign)
                                        <button type="button" wire:click="revoke({{ $invitation->id }})" wire:confirm="{{ __('Annuler cette invitation ? Le lien ne fonctionnera plus.') }}" class="text-sm text-red-700 hover:underline">{{ __('Annuler') }}</button>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        @can('manage', $campaign)
        <aside>
            <form wire:submit="invite" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold">{{ __('Inviter quelqu\'un') }}</h2>
                <fieldset>
                    <legend class="label">{{ __('Rôle') }}</legend>
                    <div class="space-y-2">
                        @foreach (\App\Enums\CampaignRole::cases() as $roleOption)
                            <label class="flex items-start gap-2 text-sm">
                                <input type="radio" wire:model="role" value="{{ $roleOption->value }}" class="mt-1">
                                <span><span class="font-medium">{{ $roleOption->invitedLabel() }}</span>
                                    <span class="block text-xs text-stone-500">{{ $roleOption->description() }}</span></span>
                            </label>
                        @endforeach
                    </div>
                    @error('role') <p class="error">{{ $message }}</p> @enderror
                </fieldset>
                <div>
                    <label for="label" class="label">{{ __('Pour qui ?') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <input id="label" type="text" wire:model="label" class="field" placeholder="{{ __('Prénom') }}">
                    @error('label') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('Adresse e-mail') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <input id="email" type="email" wire:model="email" class="field">
                    <p class="mt-1 text-xs text-stone-500">{{ __("Aucun courriel n'est envoyé : l'adresse pré-remplit seulement la création de compte.") }}</p>
                    @error('email') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">{{ __("Créer le lien d'invitation") }}</button>
            </form>
        </aside>
        @endcan
    </div>
</div>
