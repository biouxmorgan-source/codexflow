<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('L’état de santé de la plateforme : fréquentation, tables de jeu, stockage et rapidité du serveur. Des chiffres d’ensemble, sans rien de personnel.') }}</p>
    <x-admin-nav />

    @php($tile = 'rounded-xl border border-stone-200 bg-white p-3 shadow-sm')
    @php($size = fn (?int $bytes) => $bytes === null ? '—' : \App\Support\Plans\StorageUsage::format($bytes))

    <div class="mb-4">
        <label for="health-period" class="label">{{ __('Période') }}</label>
        <select id="health-period" wire:model.live="period" class="field w-auto">
            @foreach (\App\Livewire\Admin\Users::PERIODS as $days)
                <option value="{{ $days }}">{{ trans_choice(':count jour|:count jours', $days) }}</option>
            @endforeach
        </select>
    </div>

    <section class="mb-8" aria-labelledby="health-usage">
        <h2 id="health-usage" class="mb-3 text-lg font-semibold">{{ __('Fréquentation') }}</h2>
        @php($usage = $this->usage)
        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ([
                [__('Comptes'), $usage['accounts'], null],
                [__('Nouveaux comptes'), $usage['new_accounts'], null],
                [__('Personnes connectées'), $usage['people'], __('Chaque personne compte une fois.')],
                [__('Connexions'), $usage['logins'], __('Toutes les connexions, plusieurs par personne.')],
                [__('Connexions par personne'), \Illuminate\Support\Number::format($usage['per_person'], locale: app()->getLocale()), null],
                [__('Séances jouées'), $usage['sessions'], null],
                [__('Campagnes jouées'), $usage['played_campaigns'], __('Au moins une séance lancée sur la période.')],
            ] as [$label, $value, $hint])
                <div class="{{ $tile }}" @if ($hint) title="{{ $hint }}" @endif>
                    <dt class="text-xs text-stone-500">{{ $label }}</dt>
                    <dd class="text-2xl font-semibold tabular-nums">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @php($timeline = $this->timeline)
        @php($peak = max(1, $timeline->max('logins')))
        <div class="mt-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
            <p class="mb-3 flex flex-wrap gap-4 text-xs text-stone-600">
                <span class="flex items-center gap-1"><span class="inline-block size-3 rounded-sm bg-codex"></span>{{ __('Personnes connectées') }}</span>
                <span class="flex items-center gap-1"><span class="inline-block size-3 rounded-sm bg-codex-soft"></span>{{ __('Connexions') }}</span>
            </p>
            <table class="w-full text-sm">
                <caption class="sr-only">{{ __('Connexions sur la période') }}</caption>
                <tbody>
                    @foreach ($timeline as $bucket)
                        <tr>
                            <th scope="row" class="w-32 py-0.5 pr-3 text-left text-xs font-normal whitespace-nowrap text-stone-600">{{ $bucket['label'] }}</th>
                            <td class="py-0.5">
                                <span class="relative block h-3 overflow-hidden rounded-sm bg-stone-100" aria-hidden="true">
                                    <span class="absolute inset-y-0 left-0 bg-codex-soft" style="width: {{ round($bucket['logins'] / $peak * 100) }}%"></span>
                                    <span class="absolute inset-y-0 left-0 bg-codex" style="width: {{ round($bucket['people'] / $peak * 100) }}%"></span>
                                </span>
                            </td>
                            <td class="w-24 py-0.5 pl-3 text-right text-xs tabular-nums text-stone-600">{{ $bucket['people'] }} / {{ $bucket['logins'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8" aria-labelledby="health-tables">
        <h2 id="health-tables" class="mb-3 text-lg font-semibold">{{ __('Tables de jeu') }}</h2>
        @php($tables = $this->tables)
        @php($number = fn ($value) => \Illuminate\Support\Number::format($value, locale: app()->getLocale()))
        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            @foreach ([
                [__('Campagnes'), $tables['campaigns']],
                [__('MJ (propriétaires)'), $tables['gms']],
                [__('Campagnes par MJ'), $number($tables['campaigns_per_gm'])],
                [__('Joueurs'), $tables['players']],
                [__('Joueurs par campagne'), $number($tables['players_per_campaign'])],
                [__('Joueurs par MJ'), $number($tables['players_per_gm'])],
                [__('Plus grande table'), trans_choice(':count joueur|:count joueurs', $tables['max_players'])],
                [__('Co-MJ'), $tables['co_gms']],
                [__('Spectateurs'), $tables['spectators']],
                [__('Comptes seulement joueurs'), $tables['players_only']],
                [__('Comptes sans campagne'), $tables['without_campaign']],
            ] as [$label, $value])
                <div class="{{ $tile }}">
                    <dt class="text-xs text-stone-500">{{ $label }}</dt>
                    <dd class="text-2xl font-semibold tabular-nums">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="mb-8" aria-labelledby="health-storage">
        <h2 id="health-storage" class="mb-3 text-lg font-semibold">{{ __('Espace occupé') }}</h2>
        @php($storage = $this->storage)
        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Fichiers des comptes') }}</dt>
                <dd class="text-2xl font-semibold tabular-nums">{{ $size($storage['files']) }}</dd>
            </div>
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Base de données') }}</dt>
                <dd class="text-2xl font-semibold tabular-nums">{{ $size($storage['database']) }}</dd>
            </div>
            <div class="{{ $tile }} col-span-2">
                <dt class="text-xs text-stone-500">{{ __('Disque du serveur') }}</dt>
                @if ($storage['disk_ratio'] !== null)
                    <dd class="text-2xl font-semibold tabular-nums">{{ __(':free libres sur :total', ['free' => $size($storage['disk_free']), 'total' => $size($storage['disk_total'])]) }}</dd>
                    <dd class="mt-2 h-2 overflow-hidden rounded-full bg-stone-200" aria-hidden="true"><span @class(['block h-full', 'bg-codex' => $storage['disk_ratio'] < 0.85, 'bg-red-600' => $storage['disk_ratio'] >= 0.85]) style="width: {{ round($storage['disk_ratio'] * 100) }}%"></span></dd>
                @else
                    <dd class="text-2xl font-semibold">—</dd>
                @endif
            </div>
        </dl>
    </section>

    <section class="mb-8" aria-labelledby="health-server">
        <h2 id="health-server" class="mb-1 text-lg font-semibold">{{ __('Serveur') }}</h2>
        <p class="mb-3 text-sm text-stone-600">{{ __('Temps mis par le serveur pour préparer chaque page, mesuré à chaque requête. Au-delà d’une seconde, une requête compte comme lente ; la console prévient quand plus de 5 % le sont sur 24 heures.') }}</p>
        @php($server = $this->server)
        @php($ms = fn (int $value) => __(':value ms', ['value' => \Illuminate\Support\Number::format($value, locale: app()->getLocale())]))
        @php($percent = fn (float $ratio) => \Illuminate\Support\Number::percentage($ratio * 100, $ratio > 0 && $ratio < 0.1 ? 1 : 0, locale: app()->getLocale()))
        <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-xs text-stone-600">
                    <tr>
                        <th class="px-3 py-2"><span class="sr-only">{{ __('Période') }}</span></th>
                        <th class="px-3 py-2">{{ __('Requêtes') }}</th>
                        <th class="px-3 py-2">{{ __('Moyenne') }}</th>
                        <th class="px-3 py-2">{{ __('La plus longue') }}</th>
                        <th class="px-3 py-2">{{ __('Lentes (> 1 s)') }}</th>
                        <th class="px-3 py-2">{{ __('Très lentes (> 3 s)') }}</th>
                        <th class="px-3 py-2">{{ __('Erreurs serveur') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ([__('24 dernières heures') => $server['day'], __('7 derniers jours') => $server['week']] as $label => $stats)
                        <tr>
                            <th scope="row" class="px-3 py-2 font-medium">{{ $label }}</th>
                            <td class="px-3 py-2 tabular-nums">{{ $stats['requests'] }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $ms($stats['average']) }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $ms($stats['max']) }}</td>
                            <td @class(['px-3 py-2 tabular-nums', 'font-semibold text-red-700' => $stats['requests'] >= 50 && $stats['slow_ratio'] >= \App\Support\Monitoring\ResponseTimes::ALERT_RATIO])>{{ $stats['slow'] }} ({{ $percent($stats['slow_ratio']) }})</td>
                            <td class="px-3 py-2 tabular-nums">{{ $stats['very_slow'] }}</td>
                            <td @class(['px-3 py-2 tabular-nums', 'font-semibold text-red-700' => $stats['errors'] > 0])>{{ $stats['errors'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php($hours = $server['hours'])
        @php($slowest = max(\App\Support\Monitoring\ResponseTimes::SLOW_MS, $hours->max('average')))
        <figure class="mt-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
            <figcaption class="mb-3 text-sm font-medium">{{ __('Temps de réponse moyen, heure par heure') }}</figcaption>
            <div class="flex h-32 items-end gap-0.5 border-b border-stone-200" role="img" aria-label="{{ __('Temps de réponse moyen sur les 24 dernières heures') }}">
                @foreach ($hours as $hour)
                    <span @class(['flex-1 rounded-t-sm', 'bg-codex' => $hour['average'] < \App\Support\Monitoring\ResponseTimes::SLOW_MS, 'bg-red-600' => $hour['average'] >= \App\Support\Monitoring\ResponseTimes::SLOW_MS, 'bg-stone-100' => $hour['requests'] === 0])
                        style="height: {{ $hour['requests'] ? max(2, round($hour['average'] / $slowest * 100)) : 2 }}%"
                        title="{{ $hour['hour']->isoFormat('LT') }} · {{ $hour['requests'] ? trans_choice(':count requête|:count requêtes', $hour['requests']).' · '.$ms($hour['average']) : __('aucune requête') }}"></span>
                @endforeach
            </div>
            <p class="mt-1 flex justify-between text-xs text-stone-500"><span>{{ $hours->first()['hour']->isoFormat('LT') }}</span><span>{{ $hours->last()['hour']->isoFormat('LT') }}</span></p>
        </figure>

        <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Réponse de la base') }}</dt>
                <dd class="text-2xl font-semibold tabular-nums">{{ $server['database_ms'] === null ? '—' : $ms($server['database_ms']) }}</dd>
            </div>
            <div class="{{ $tile }}" title="{{ __('Nombre moyen de tâches en attente du processeur sur 1, 5 et 15 minutes. Au-delà du nombre de processeurs, le serveur est saturé.') }}">
                <dt class="text-xs text-stone-500">{{ __('Charge') }}@if ($server['cpus']) · {{ trans_choice(':count processeur|:count processeurs', $server['cpus']) }}@endif</dt>
                <dd @class(['text-2xl font-semibold tabular-nums', 'text-red-700' => $server['load'] && $server['cpus'] && $server['load'][1] > $server['cpus']])>{{ $server['load'] ? implode(' · ', $server['load']) : '—' }}</dd>
            </div>
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Mémoire utilisée') }}</dt>
                <dd @class(['text-2xl font-semibold tabular-nums', 'text-red-700' => $server['memory'] && $server['memory']['ratio'] >= 0.9])>{{ $server['memory'] ? $percent($server['memory']['ratio']) : '—' }}</dd>
                @if ($server['memory'])<dd class="text-xs text-stone-500">{{ __(':free libres sur :total', ['free' => $size($server['memory']['available']), 'total' => $size($server['memory']['total'])]) }}</dd>@endif
            </div>
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Tâches en attente') }}</dt>
                <dd class="text-2xl font-semibold tabular-nums">{{ $server['queue'] }}</dd>
            </div>
            <div class="{{ $tile }}">
                <dt class="text-xs text-stone-500">{{ __('Tâches en échec') }}</dt>
                <dd @class(['text-2xl font-semibold tabular-nums', 'text-red-700' => $server['failed_jobs'] > 0])>{{ $server['failed_jobs'] }}</dd>
            </div>
        </dl>
        <p class="mt-3 text-xs text-stone-500">{{ __('Les mesures commencent à la mise en ligne et sont gardées 90 jours. Pour suivre le processeur et la mémoire dans la durée, ou être alerté si le site ne répond plus, utilisez aussi la surveillance de l’hébergeur.') }}</p>
    </section>
</div>
