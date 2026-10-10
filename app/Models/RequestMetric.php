<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Une heure de requêtes : leur nombre, leur durée totale et la plus longue, les lentes et les erreurs. */
class RequestMetric extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['hour' => 'datetime'];
    }

    /** Gardées 90 jours (php artisan model:prune). */
    public function prunable(): Builder
    {
        return static::where('hour', '<', now()->subDays(90));
    }
}
