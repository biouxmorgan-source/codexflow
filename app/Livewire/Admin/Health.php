<?php

namespace App\Livewire\Admin;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\PlaySession;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\Monitoring\ResponseTimes;
use App\Support\Plans\Plans;
use App\Support\Plans\StorageUsage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

/**
 * Santé de la plateforme : fréquentation, tables de jeu, stockage et temps de réponse du serveur.
 * Des chiffres d'ensemble seulement, sans rien de personnel.
 */
class Health extends Component
{
    #[Url(except: 30)]
    public int $period = 30;

    /** Rôle du compte : all, no_admin, admin, premium, trial ou free. */
    #[Url(except: 'all')]
    public string $role = 'all';

    public const ROLES = ['all', 'no_admin', Plans::ADMIN, Plans::PREMIUM, Plans::TRIAL, Plans::FREE, Plans::DEMO];

    public function mount(): void
    {
        $this->authorize('admin');
    }

    private function days(): int
    {
        return in_array($this->period, Users::PERIODS, true) ? $this->period : 30;
    }

    /** Comptes retenus par le filtre de rôle ; null = tous, sans filtrer. @return list<int>|null */
    private function accountIds(): ?array
    {
        $role = in_array($this->role, self::ROLES, true) ? $this->role : 'all';

        if ($role === 'all') {
            return null;
        }

        return User::all()
            ->filter(fn (User $user) => $role === 'no_admin' ? Plans::effective($user) !== Plans::ADMIN : Plans::effective($user) === $role)
            ->pluck('id')->all();
    }

    /** Campagnes comptées : celles des comptes retenus, hors campagne de démonstration. */
    private function campaigns()
    {
        return Campaign::where('is_demo', false)->when($this->accountIds() !== null, fn ($query) => $query->whereIn('user_id', $this->accountIds()));
    }

    private function from(): Carbon
    {
        return now()->subDays($this->days());
    }

    /** Fréquentation sur la période : personnes distinctes et connexions. */
    #[Computed]
    public function usage(): array
    {
        $ids = $this->accountIds();
        $logins = UserLogin::where('logged_in_at', '>=', $this->from())->when($ids !== null, fn ($query) => $query->whereIn('user_id', $ids));
        $sessions = PlaySession::where('started_at', '>=', $this->from())->whereIn('campaign_id', $this->campaigns()->select('id'));
        $people = (clone $logins)->distinct('user_id')->count('user_id');
        $total = (clone $logins)->count();

        return [
            'accounts' => User::when($ids !== null, fn ($query) => $query->whereIn('id', $ids))->count(),
            'new_accounts' => User::where('created_at', '>=', $this->from())->when($ids !== null, fn ($query) => $query->whereIn('id', $ids))->count(),
            'people' => $people,
            'logins' => $total,
            'per_person' => $people ? round($total / $people, 1) : 0,
            'sessions' => (clone $sessions)->count(),
            'played_campaigns' => (clone $sessions)->distinct('campaign_id')->count('campaign_id'),
        ];
    }

    /**
     * Connexions par jour (7 et 30 jours), par semaine (90 jours) ou par mois (un an).
     *
     * @return Collection<int, array{label: string, people: int, logins: int}>
     */
    #[Computed]
    public function timeline(): Collection
    {
        $unit = match (true) {
            $this->days() <= 30 => 'day',
            $this->days() <= 90 => 'week',
            default => 'month',
        };

        $ids = $this->accountIds();
        $rows = UserLogin::where('logged_in_at', '>=', $this->from())
            ->when($ids !== null, fn ($query) => $query->whereIn('user_id', $ids))
            ->selectRaw("date_trunc('{$unit}', logged_in_at) as bucket, count(distinct user_id) as people, count(*) as logins")
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->bucket)->toDateString());

        $start = $unit === 'week' ? $this->from()->startOfWeek(Carbon::MONDAY) : $this->from()->startOf($unit);
        $buckets = collect();
        for ($date = $start->copy(); $date <= now(); $date->add(1, $unit)) {
            $row = $rows->get($date->toDateString());
            $buckets->push([
                'label' => match ($unit) {
                    'day' => $date->isoFormat('dd D MMM'),
                    'week' => __('sem. du :date', ['date' => $date->isoFormat('D MMM')]),
                    default => $date->isoFormat('MMMM YYYY'),
                },
                'people' => (int) $row?->people,
                'logins' => (int) $row?->logins,
            ]);
        }

        return $buckets;
    }

    /** Tables de jeu : combien de MJ, de joueurs par campagne et par MJ. Le filtre porte sur le MJ propriétaire. */
    #[Computed]
    public function tables(): array
    {
        $ids = $this->accountIds();
        $campaigns = $this->campaigns();
        $seats = fn (CampaignRole $role) => DB::table('campaign_memberships')
            ->join('campaigns', 'campaigns.id', '=', 'campaign_memberships.campaign_id')
            ->where('campaigns.is_demo', false)
            ->when($ids !== null, fn ($query) => $query->whereIn('campaigns.user_id', $ids))
            ->where('campaign_memberships.role', $role->value);

        $players = $seats(CampaignRole::Player);
        $perCampaign = (clone $players)->selectRaw('campaigns.id as campaign_id, count(*) as players')->groupBy('campaigns.id');
        // Joueurs distincts autour de chaque MJ propriétaire, toutes ses campagnes confondues.
        $perGm = (clone $players)->selectRaw('campaigns.user_id, count(distinct campaign_memberships.user_id) as players')->groupBy('campaigns.user_id');

        $count = (clone $campaigns)->count();
        $gms = (clone $campaigns)->distinct('user_id')->count('user_id');
        $playerSeats = (clone $players)->count();
        $accounts = fn () => User::when($ids !== null, fn ($query) => $query->whereIn('id', $ids));
        $realCampaigns = fn ($query) => $query->where('is_demo', false);

        return [
            'campaigns' => $count,
            'gms' => $gms,
            'campaigns_per_gm' => $gms ? round($count / $gms, 1) : 0,
            'players' => (clone $players)->distinct('campaign_memberships.user_id')->count('campaign_memberships.user_id'),
            'players_per_campaign' => $count ? round($playerSeats / $count, 1) : 0,
            'players_per_gm' => $gms ? round((int) DB::query()->fromSub($perGm, 'g')->sum('players') / $gms, 1) : 0,
            'max_players' => (int) DB::query()->fromSub($perCampaign, 'c')->max('players'),
            'co_gms' => $seats(CampaignRole::GameMaster)->whereColumn('campaign_memberships.user_id', '!=', 'campaigns.user_id')->count(),
            'spectators' => $seats(CampaignRole::Spectator)->count(),
            'players_only' => $accounts()->whereDoesntHave('ownedCampaigns', $realCampaigns)->whereHas('campaigns', $realCampaigns)->count(),
            'without_campaign' => $accounts()->whereDoesntHave('campaigns', $realCampaigns)->whereDoesntHave('ownedCampaigns', $realCampaigns)->count(),
        ];
    }

    /** Espace occupé : fichiers des comptes, base de données, disque du serveur. */
    #[Computed]
    public function storage(): array
    {
        $ids = $this->accountIds();
        $files = User::when($ids !== null, fn ($query) => $query->whereIn('id', $ids))->get()->sum(fn (User $user) => StorageUsage::bytes($user));

        try {
            $database = (int) DB::selectOne('select pg_database_size(current_database()) as size')->size;
        } catch (Throwable) {
            $database = null;
        }

        $free = @disk_free_space(storage_path());
        $total = @disk_total_space(storage_path());

        return [
            'files' => $files,
            'database' => $database,
            'disk_free' => $free === false ? null : (int) $free,
            'disk_total' => $total === false ? null : (int) $total,
            'disk_ratio' => $free !== false && $total ? 1 - $free / $total : null,
        ];
    }

    /** Le serveur maintenant et ses temps de réponse récents. */
    #[Computed]
    public function server(): array
    {
        $start = microtime(true);
        try {
            DB::select('select 1');
            $database = (int) round((microtime(true) - $start) * 1000);
        } catch (Throwable) {
            $database = null;
        }

        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;

        return [
            'day' => ResponseTimes::since(now()->subDay()),
            'week' => ResponseTimes::since(now()->subDays(7)),
            'hours' => ResponseTimes::hourly(24),
            'database_ms' => $database,
            'load' => $load === false ? null : array_map(fn ($value) => round($value, 2), $load),
            'cpus' => self::cpus(),
            'memory' => self::memory(),
            'queue' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    private static function cpus(): ?int
    {
        $info = @file_get_contents('/proc/cpuinfo');

        return $info ? max(1, substr_count($info, "\nprocessor") + (str_starts_with($info, 'processor') ? 1 : 0)) : null;
    }

    /** @return array{total: int, available: int, ratio: float}|null mémoire vive, lue sous Linux */
    private static function memory(): ?array
    {
        $info = @file_get_contents('/proc/meminfo');
        if (! $info || ! preg_match('/MemTotal:\s+(\d+)/', $info, $total) || ! preg_match('/MemAvailable:\s+(\d+)/', $info, $available)) {
            return null;
        }

        return ['total' => $total[1] * 1024, 'available' => $available[1] * 1024, 'ratio' => 1 - $available[1] / max(1, $total[1])];
    }

    public function render()
    {
        return view('livewire.admin.health')->title(__('Administration · Santé'));
    }
}
