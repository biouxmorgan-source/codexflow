<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserLogin;
use App\Support\GiftCodes;
use App\Support\Plans\Plans;
use App\Support\Plans\StorageUsage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Console d'administration : les comptes et leurs indicateurs, sans rien de personnel
 * (ni nom, ni mot de passe, ni adresse IP). L'administrateur règle la formule de chacun
 * et peut envoyer un lien de réinitialisation du mot de passe.
 */
class Users extends Component
{
    public const PERIODS = [7, 30, 90, 365];

    public const SORTS = ['email', 'plan', 'plan_started_at', 'plan_ends_at', 'storage', 'ai', 'campaigns', 'logins', 'created_at'];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'created_at')]
    public string $sort = 'created_at';

    #[Url(except: 'desc')]
    public string $direction = 'desc';

    #[Url(except: 30)]
    public int $period = 30;

    public ?int $editingId = null;

    public string $plan = Plans::FREE;

    public string $planStartedAt = '';

    public string $planEndsAt = '';

    public string $storageQuotaMb = '';

    public bool $isAdmin = false;

    public bool $isFounder = false;

    /** Récompense choisie sur le Kickstarter (repère libre, ex. « The Whole Table »). */
    public string $backerReward = '';

    public string $giftCount = '';

    public string $giftMonths = '12';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    public function sortBy(string $column): void
    {
        abort_unless(in_array($column, self::SORTS, true), 422);

        $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $column;
    }

    /** @return Collection<int, array<string, mixed>> */
    #[Computed]
    public function rows(): Collection
    {
        $period = in_array($this->period, self::PERIODS, true) ? $this->period : 30;

        $rows = User::query()
            ->when($this->search !== '', fn ($query) => $query->where('email', 'ilike', '%'.addcslashes($this->search, '%_\\').'%'))
            ->withCount([
                'ownedCampaigns',
                'campaigns',
                // Jours avec au moins une connexion : plusieurs connexions le même jour comptent une fois.
                'logins' => fn ($query) => $query->where('logged_in_at', '>=', now()->subDays($period))->select(DB::raw('count(distinct date(logged_in_at))')),
            ])
            ->withMax('logins', 'logged_in_at')
            ->get()
            ->map(function (User $user) {
                $used = StorageUsage::bytes($user);
                $limit = Plans::storageLimit($user);

                return [
                    'user' => $user,
                    'plan' => Plans::effective($user),
                    'storage' => $used,
                    'limit' => $limit,
                    'ratio' => $limit ? min(1, $used / max(1, $limit)) : 0,
                    'ai' => $user->hasAiKey(),
                    'campaigns' => $user->owned_campaigns_count,
                    'memberships' => $user->campaigns_count - $user->owned_campaigns_count,
                    'logins' => $user->logins_count,
                    'last_login' => $user->logins_max_logged_in_at ? Carbon::parse($user->logins_max_logged_in_at) : null,
                ];
            });

        $key = fn (array $row) => match ($this->sort) {
            'email' => mb_strtolower($row['user']->email),
            'plan' => $row['plan'],
            'plan_started_at' => $row['user']->plan_started_at?->timestamp ?? 0,
            'plan_ends_at' => $row['user']->plan_ends_at?->timestamp ?? PHP_INT_MAX,
            'storage' => $row['storage'],
            'ai' => (int) $row['ai'],
            'campaigns' => $row['campaigns'],
            'logins' => $row['logins'],
            default => $row['user']->created_at?->timestamp ?? 0,
        };

        return ($this->direction === 'asc' ? $rows->sortBy($key) : $rows->sortByDesc($key))->values();
    }

    /** Chiffres d'ensemble, en tête de la console. */
    #[Computed]
    public function totals(): array
    {
        $users = User::all();
        $plans = $users->countBy(fn (User $user) => Plans::effective($user));

        return [
            'accounts' => $users->count(),
            'premium' => $plans[Plans::PREMIUM] ?? 0,
            'trial' => $plans[Plans::TRIAL] ?? 0,
            'free' => $plans[Plans::FREE] ?? 0,
            'demo' => $plans[Plans::DEMO] ?? 0,
            'admins' => $plans[Plans::ADMIN] ?? 0,
            'founders' => $users->filter(fn (User $user) => $user->isFounder())->count(),
            'active' => UserLogin::where('logged_in_at', '>=', now()->subDays($this->period))->distinct('user_id')->count('user_id'),
            'logins' => (int) UserLogin::where('logged_in_at', '>=', now()->subDays($this->period))->selectRaw('count(distinct (user_id, date(logged_in_at))) as days')->value('days'),
        ];
    }

    public function edit(?int $id): void
    {
        $this->authorize('admin');
        $this->resetValidation();
        $this->editingId = $id;

        if ($id === null) {
            return;
        }

        $user = User::findOrFail($id);
        $this->plan = in_array($user->plan, Plans::ASSIGNABLE, true) ? $user->plan : Plans::FREE;
        $this->planStartedAt = (string) $user->plan_started_at?->toDateString();
        $this->planEndsAt = (string) $user->plan_ends_at?->toDateString();
        $this->storageQuotaMb = (string) ($user->storage_quota_mb ?? '');
        $this->isAdmin = $user->is_admin;
        $this->isFounder = $user->isFounder();
        $this->backerReward = (string) $user->backer_reward;
        $this->giftCount = '';
    }

    /** Sort un compte de la démo : formule gratuite, essai qui commence maintenant. */
    public function unlock(int $id): void
    {
        $this->authorize('admin');
        $user = User::findOrFail($id);
        Plans::leaveDemo($user);

        session()->now('status', __('Compte débloqué : :email.', ['email' => $user->email]));
        unset($this->rows, $this->totals);
    }

    public function save(): void
    {
        $this->authorize('admin');
        $user = User::findOrFail($this->editingId);

        $this->validate([
            'plan' => ['required', Rule::in(Plans::ASSIGNABLE)],
            'planStartedAt' => ['nullable', 'date'],
            'planEndsAt' => ['nullable', 'date', 'after_or_equal:planStartedAt'],
            'storageQuotaMb' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'backerReward' => ['nullable', 'string', 'max:120'],
        ], attributes: [
            'plan' => __('formule'),
            'planStartedAt' => __('début'),
            'planEndsAt' => __('fin'),
            'storageQuotaMb' => __('stockage'),
        ]);

        // Débloqué : le compte quitte la démo et son essai commence.
        if ($this->plan !== Plans::DEMO) {
            Plans::leaveDemo($user, $this->plan);
        }

        // On ne se retire pas soi-même l'administration : il resterait peut-être personne.
        $isAdmin = $user->is(auth()->user()) ? true : $this->isAdmin;

        $user->forceFill([
            'plan' => $this->plan,
            'plan_started_at' => $this->planStartedAt ?: null,
            'plan_ends_at' => $this->planEndsAt ?: null,
            'storage_quota_mb' => $this->storageQuotaMb === '' ? null : (int) $this->storageQuotaMb,
            'is_admin' => $isAdmin,
            // La date d'origine reste : retirer puis remettre le marqueur la remet à aujourd'hui.
            'founder_since' => $this->isFounder ? ($user->founder_since ?? today()) : null,
            'backer_reward' => trim($this->backerReward) ?: null,
        ])->save();

        $this->editingId = null;
        session()->now('status', __('Compte mis à jour : :email.', ['email' => $user->email]));
        unset($this->rows, $this->totals);
    }

    /** Donne au compte des codes cadeaux à partager (ex. 4 codes d'un an pour « The Whole Table »). */
    public function issueGifts(): void
    {
        $this->authorize('admin');
        $user = User::findOrFail($this->editingId);

        $this->validate([
            'giftCount' => ['required', 'integer', 'min:1', 'max:50'],
            'giftMonths' => ['required', 'integer', 'min:1', 'max:120'],
        ], attributes: ['giftCount' => __('nombre de codes'), 'giftMonths' => __('mois')]);

        GiftCodes::issue($user, (int) $this->giftCount, (int) $this->giftMonths, $user->backer_reward);

        $this->giftCount = '';
        session()->now('status', __('Codes cadeaux ajoutés au compte :email.', ['email' => $user->email]));
    }

    /** Envoie à la personne le lien « Réinitialiser le mot de passe » ; l'administrateur ne voit jamais le mot de passe. */
    public function sendResetLink(int $id): void
    {
        $this->authorize('admin');
        $user = User::findOrFail($id);

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        session()->now('status', $status === Password::RESET_LINK_SENT
            ? __('Lien de réinitialisation envoyé à :email.', ['email' => $user->email])
            : __('Le lien n’a pas pu être envoyé : un lien a peut-être déjà été demandé il y a peu. Réessayez dans une minute.'));
    }

    /**
     * Téléphone perdu et codes de secours épuisés : l'administrateur retire la double
     * authentification ; la personne pourra la réactiver depuis ses préférences.
     */
    public function disableTwoFactor(int $id): void
    {
        $this->authorize('admin');
        $user = User::findOrFail($id);

        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();

        session()->now('status', __('Double authentification retirée pour :email.', ['email' => $user->email]));
        unset($this->rows);
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'labels' => Plans::labels(),
        ])->title(__('Administration · Comptes'));
    }
}
