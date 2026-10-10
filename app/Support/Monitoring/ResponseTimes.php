<?php

namespace App\Support\Monitoring;

use App\Models\RequestMetric;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Temps de réponse du serveur : chaque requête s'ajoute aux totaux de son heure,
 * la console en tire la moyenne, la plus longue et la part de requêtes lentes.
 */
class ResponseTimes
{
    /** Au-delà d'une seconde, la personne attend : la requête compte comme lente. */
    public const SLOW_MS = 1000;

    public const VERY_SLOW_MS = 3000;

    /** Part de requêtes lentes, sur 24 heures, à partir de laquelle la console prévient. */
    public const ALERT_RATIO = 0.05;

    public static function record(int $ms, bool $error = false): void
    {
        try {
            DB::statement(
                'insert into request_metrics (hour, requests, total_ms, max_ms, slow, very_slow, errors) values (?, 1, ?, ?, ?, ?, ?)
                 on conflict (hour) do update set requests = request_metrics.requests + 1,
                    total_ms = request_metrics.total_ms + excluded.total_ms,
                    max_ms = greatest(request_metrics.max_ms, excluded.max_ms),
                    slow = request_metrics.slow + excluded.slow,
                    very_slow = request_metrics.very_slow + excluded.very_slow,
                    errors = request_metrics.errors + excluded.errors',
                [now()->startOfHour(), $ms, $ms, (int) ($ms >= self::SLOW_MS), (int) ($ms >= self::VERY_SLOW_MS), (int) $error],
            );
        } catch (Throwable) {
            // La mesure ne doit jamais faire échouer une page (base indisponible, migration pas encore passée).
        }
    }

    /**
     * Totaux depuis une date.
     *
     * @return array{requests: int, average: int, max: int, slow: int, very_slow: int, errors: int, slow_ratio: float}
     */
    public static function since(Carbon $from): array
    {
        $row = RequestMetric::where('hour', '>=', $from->copy()->startOfHour())
            ->selectRaw('coalesce(sum(requests), 0) as requests, coalesce(sum(total_ms), 0) as total_ms, coalesce(max(max_ms), 0) as max_ms, coalesce(sum(slow), 0) as slow, coalesce(sum(very_slow), 0) as very_slow, coalesce(sum(errors), 0) as errors')
            ->first();

        $requests = (int) $row->requests;

        return [
            'requests' => $requests,
            'average' => $requests ? (int) round($row->total_ms / $requests) : 0,
            'max' => (int) $row->max_ms,
            'slow' => (int) $row->slow,
            'very_slow' => (int) $row->very_slow,
            'errors' => (int) $row->errors,
            'slow_ratio' => $requests ? $row->slow / $requests : 0.0,
        ];
    }

    /**
     * Les dernières heures, de la plus ancienne à la plus récente, heures sans requête comprises.
     *
     * @return Collection<int, array{hour: Carbon, requests: int, average: int, max: int, slow: int}>
     */
    public static function hourly(int $hours = 24): Collection
    {
        $from = now()->startOfHour()->subHours($hours - 1);
        $rows = RequestMetric::where('hour', '>=', $from)->get()->keyBy(fn (RequestMetric $metric) => $metric->hour->timestamp);

        return collect(range(0, $hours - 1))->map(function (int $offset) use ($from, $rows) {
            $hour = $from->copy()->addHours($offset);
            $metric = $rows->get($hour->timestamp);

            return [
                'hour' => $hour,
                'requests' => (int) $metric?->requests,
                'average' => $metric && $metric->requests ? (int) round($metric->total_ms / $metric->requests) : 0,
                'max' => (int) $metric?->max_ms,
                'slow' => (int) $metric?->slow,
            ];
        });
    }

    /** Avertissement pour la console quand trop de requêtes ont été lentes sur 24 heures. */
    public static function warning(): ?string
    {
        try {
            $day = self::since(now()->subDay());
        } catch (Throwable) {
            return null;
        }

        if ($day['requests'] < 50 || $day['slow_ratio'] < self::ALERT_RATIO) {
            return null;
        }

        return __('Le serveur ralentit : :percent % des requêtes ont pris plus d’une seconde sur 24 heures (moyenne :average ms). Voir l’onglet Santé.', [
            'percent' => (int) round($day['slow_ratio'] * 100),
            'average' => $day['average'],
        ]);
    }
}
