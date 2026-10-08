<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserLogin;
use App\Support\Plans\Plans;
use App\Support\Plans\StorageUsage;
use Illuminate\Support\Collection;
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
                'logins' => fn ($query) => $query->where('logged_in_at', '>=', now()->subDays($period)),
            ])
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
            'admins' => $plans[Plans::ADMIN] ?? 0,
            'active' => UserLogin::where('logged_in_at', '>=', now()->subDays($this->period))->distinct('user_id')->count('user_id'),
            'logins' => UserLogin::where('logged_in_at', '>=', now()->subDays($this->period))->count(),
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
        ], attributes: [
            'plan' => __('formule'),
            'planStartedAt' => __('début'),
            'planEndsAt' => __('fin'),
            'storageQuotaMb' => __('stockage'),
        ]);

        // On ne se retire pas soi-même l'administration : il resterait peut-être personne.
        $isAdmin = $user->is(auth()->user()) ? true : $this->isAdmin;

        $user->forceFill([
            'plan' => $this->plan,
            'plan_started_at' => $this->planStartedAt ?: null,
            'plan_ends_at' => $this->planEndsAt ?: null,
            'storage_quota_mb' => $this->storageQuotaMb === '' ? null : (int) $this->storageQuotaMb,
            'is_admin' => $isAdmin,
        ])->save();

        $this->editingId = null;
        session()->now('status', __('Compte mis à jour : :email.', ['email' => $user->email]));
        unset($this->rows, $this->totals);
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

    public function render()
    {
        return view('livewire.admin.users', [
            'labels' => Plans::labels(),
        ])->title(__('Administration · Comptes'));
    }
}
