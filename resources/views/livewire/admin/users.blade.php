<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Comptes et indicateurs, sans donnée personnelle : ni nom, ni mot de passe, ni adresse IP.') }}</p>
    <x-admin-nav />

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    @php($totals = $this->totals)
    <dl class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            __('Comptes') => $totals['accounts'],
            __('Premium') => $totals['premium'],
            __('En essai') => $totals['trial'],
            __('Gratuit') => $totals['free'],
            __('Administrateurs') => $totals['admins'],
            __('Comptes actifs') => $totals['active'],
            __('Connexions') => $totals['logins'],
        ] as $label => $value)
            <div class="rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                <dt class="text-xs text-stone-500">{{ $label }}</dt>
                <dd class="text-2xl font-semibold tabular-nums">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-56 flex-1">
            <label for="admin-search" class="label">{{ __('Rechercher une adresse') }}</label>
            <input id="admin-search" type="search" wire:model.live.debounce.300ms="search" class="field" autocomplete="off">
        </div>
        <div>
            <label for="admin-period" class="label">{{ __('Connexions et comptes actifs sur') }}</label>
            <select id="admin-period" wire:model.live="period" class="field">
                @foreach (\App\Livewire\Admin\Users::PERIODS as $days)
                    <option value="{{ $days }}">{{ trans_choice(':count jour|:count jours', $days) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @php($header = function (string $column, string $label) {
        $active = $this->sort === $column;
        $arrow = $active ? ($this->direction === 'asc' ? ' ▲' : ' ▼') : '';
        return '<button type="button" wire:click="sortBy(\''.$column.'\')" class="font-semibold hover:text-codex">'.e($label).$arrow.'</button>';
    })
    <div class="relative overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full min-w-[60rem] text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs text-stone-600">
                <tr>
                    <th class="px-3 py-2">{!! $header('email', __('Adresse')) !!}</th>
                    <th class="px-3 py-2">{!! $header('plan', __('Formule')) !!}</th>
                    <th class="px-3 py-2">{!! $header('plan_started_at', __('Début')) !!}</th>
                    <th class="px-3 py-2">{!! $header('plan_ends_at', __('Fin')) !!}</th>
                    <th class="px-3 py-2">{!! $header('storage', __('Stockage')) !!}</th>
                    <th class="px-3 py-2">{!! $header('ai', __('IA')) !!}</th>
                    <th class="px-3 py-2">{!! $header('campaigns', __('Campagnes')) !!}</th>
                    <th class="px-3 py-2">{!! $header('logins', __('Connexions')) !!}</th>
                    <th class="px-3 py-2"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($this->rows as $row)
                    @php($user = $row['user'])
                    <tr wire:key="user-{{ $user->id }}" class="align-top">
                        <td class="px-3 py-2 break-all">{{ $user->email }}</td>
                        <td class="px-3 py-2">
                            <span @class(['rounded-full px-2 py-0.5 text-xs font-medium', 'bg-codex-soft text-codex' => $row['plan'] === 'admin', 'bg-amber-100 text-amber-900' => $row['plan'] === 'premium', 'bg-green-100 text-green-900' => $row['plan'] === 'trial', 'bg-stone-100 text-stone-700' => $row['plan'] === 'free'])>{{ $labels[$row['plan']] }}</span>
                            @if ($user->subscription_status)
                                <span class="block text-xs text-stone-500">{{ __('Stripe : :status', ['status' => $user->subscription_status]) }}</span>
                            @endif
                            @if ($row['plan'] === 'trial')
                                <span class="block text-xs text-stone-500">{{ __('jusqu’au :date', ['date' => \App\Support\Plans\Plans::trialEndsAt($user)->isoFormat('L')]) }}</span>
                            @endif
                            @if ($user->plan === 'premium' && $row['plan'] === 'free')
                                <span class="block text-xs text-red-700">{{ __('premium échu') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $user->plan_started_at?->isoFormat('L') ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $user->plan_ends_at?->isoFormat('L') ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ \App\Support\Plans\StorageUsage::format($row['storage']) }} / {{ $row['limit'] === null ? __('illimité') : \App\Support\Plans\StorageUsage::format($row['limit']) }}
                            @if ($row['limit'])
                                <span class="mt-1 block h-1.5 w-28 overflow-hidden rounded-full bg-stone-200" aria-hidden="true"><span @class(['block h-full', 'bg-codex' => $row['ratio'] < 0.9, 'bg-red-600' => $row['ratio'] >= 0.9]) style="width: {{ round($row['ratio'] * 100) }}%"></span></span>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $row['ai'] ? __('Oui') : __('Non') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ trans_choice(':count comme MJ|:count comme MJ', $row['campaigns']) }}<span class="block text-xs text-stone-500">{{ trans_choice(':count autre accès|:count autres accès', $row['memberships']) }}</span></td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['logins'] }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <button type="button" wire:click="edit({{ $editingId === $user->id ? 'null' : $user->id }})" class="link">{{ $editingId === $user->id ? __('Fermer') : __('Gérer') }}</button>
                        </td>
                    </tr>
                    @if ($editingId === $user->id)
                        <tr wire:key="edit-{{ $user->id }}">
                            <td colspan="9" class="bg-codex-soft/40 px-3 py-4">
                                <form wire:submit="save" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                                    <div>
                                        <label for="edit-plan" class="label">{{ __('Formule') }}</label>
                                        <select id="edit-plan" wire:model="plan" class="field">
                                            @foreach (\App\Support\Plans\Plans::ASSIGNABLE as $value)
                                                <option value="{{ $value }}">{{ $labels[$value] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit-start" class="label">{{ __('Début de l’abonnement') }}</label>
                                        <input id="edit-start" type="date" wire:model="planStartedAt" class="field">
                                        @error('planStartedAt') <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="edit-end" class="label">{{ __('Fin de l’abonnement') }}</label>
                                        <input id="edit-end" type="date" wire:model="planEndsAt" class="field">
                                        @error('planEndsAt') <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="edit-quota" class="label">{{ __('Stockage propre (Mo)') }}</label>
                                        <input id="edit-quota" type="number" min="0" wire:model="storageQuotaMb" class="field" placeholder="{{ __('celui de la formule') }}">
                                        @error('storageQuotaMb') <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    <label class="flex items-center gap-2 self-end pb-2 text-sm">
                                        <input type="checkbox" wire:model="isAdmin" @disabled($user->is(auth()->user()))>
                                        {{ __('Administrateur') }}
                                    </label>
                                    <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-5">
                                        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                                        <button type="button" wire:click="sendResetLink({{ $user->id }})" wire:confirm="{{ __('Envoyer à :email un lien pour choisir un nouveau mot de passe ?', ['email' => $user->email]) }}" class="btn-secondary">{{ __('Envoyer un lien de réinitialisation') }}</button>
                                        @if ($user->two_factor_confirmed_at)
                                            <button type="button" wire:click="disableTwoFactor({{ $user->id }})" wire:confirm="{{ __('Retirer la double authentification de :email ? Vérifiez d’abord que la demande vient bien de cette personne.', ['email' => $user->email]) }}" class="btn-secondary">{{ __('Retirer la double authentification') }}</button>
                                        @endif
                                        <span class="text-xs text-stone-500">{{ __('Le mot de passe n’est jamais visible : la personne en choisit un nouveau depuis le lien reçu.') }}</span>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="9" class="px-3 py-6 text-center text-stone-500">{{ __('Aucun compte ne correspond.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
